<?php
/**
 * Transformation & Progress Service Engine
 * Manages photo checkpoints, body measurements, before/after comparisons, and privacy levels.
 */

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/AuditLogger.php';

class ProgressService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getMemberEntries($memberId, $requestingUser = null) {
        if (!is_array($requestingUser)) {
            $userRole = 'superadmin';
            $userId = is_numeric($requestingUser) ? (int)$requestingUser : 1;
            $requestingUser = ['id' => $userId, 'role' => $userRole];
        }
        return $this->getMemberProgress($memberId, $requestingUser);
    }

    /**
     * Record a new progress checkpoint with photos and measurements
     */
    public function recordCheckpoint($data, $files = [], $recordedByUserId = null) {
        $memberId = intval($data['member_id']);
        $checkpointType = $data['checkpoint_type'] ?? 'custom';
        $recordedDate = !empty($data['recorded_date']) ? $data['recorded_date'] : date('Y-m-d');
        $weight = !empty($data['weight_kg']) ? floatval($data['weight_kg']) : null;
        $chest = !empty($data['chest_in']) ? floatval($data['chest_in']) : null;
        $waist = !empty($data['waist_in']) ? floatval($data['waist_in']) : null;
        $arms = !empty($data['arms_in']) ? floatval($data['arms_in']) : null;
        $thighs = !empty($data['thighs_in']) ? floatval($data['thighs_in']) : null;
        $bodyFat = !empty($data['body_fat_pct']) ? floatval($data['body_fat_pct']) : null;
        $notes = !empty($data['notes']) ? trim($data['notes']) : null;
        $privacyLevel = in_array($data['privacy_level'] ?? '', ['only_me', 'me_trainer', 'me_staff', 'community']) ? $data['privacy_level'] : 'me_staff';

        $photoFront = null;
        $photoBack = null;
        $photoSide = null;

        $uploadDir = __DIR__ . '/../../uploads/progress/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Handle File Uploads
        if (!empty($files['photo_front']['name'])) {
            $photoFront = $this->uploadPhoto($files['photo_front'], $memberId, 'front');
        }
        if (!empty($files['photo_back']['name'])) {
            $photoBack = $this->uploadPhoto($files['photo_back'], $memberId, 'back');
        }
        if (!empty($files['photo_side']['name'])) {
            $photoSide = $this->uploadPhoto($files['photo_side'], $memberId, 'side');
        }

        $stmt = $this->db->prepare("
            INSERT INTO member_progress 
            (member_id, checkpoint_type, recorded_date, weight_kg, chest_in, waist_in, arms_in, thighs_in, body_fat_pct, photo_front, photo_back, photo_side, privacy_level, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $memberId,
            $checkpointType,
            $recordedDate,
            $weight,
            $chest,
            $waist,
            $arms,
            $thighs,
            $bodyFat,
            $photoFront,
            $photoBack,
            $photoSide,
            $privacyLevel,
            $notes
        ]);
        $progressId = $this->db->lastInsertId();

        AuditLogger::log('progress_checkpoint_added', 'member_progress', $progressId, [
            'member_id' => $memberId,
            'checkpoint' => $checkpointType,
            'weight' => $weight,
            'privacy' => $privacyLevel
        ], $recordedByUserId);

        return $progressId;
    }

    /**
     * Upload and sanitize photo
     */
    private function uploadPhoto($file, $memberId, $angle) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions)) {
            return null;
        }

        $filename = "m{$memberId}_{$angle}_" . time() . '_' . rand(100, 999) . '.' . $ext;
        $destination = __DIR__ . '/../../uploads/progress/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return 'uploads/progress/' . $filename;
        }

        return null;
    }

    /**
     * Get member's progress timeline with privacy enforcement
     */
    public function getMemberProgress($memberId, $requestingUser) {
        $stmt = $this->db->prepare("
            SELECT * FROM member_progress 
            WHERE member_id = ? 
            ORDER BY recorded_date ASC, id ASC
        ");
        $stmt->execute([$memberId]);
        $checkpoints = $stmt->fetchAll();

        // Apply granular privacy filtering based on requesting user role
        $filtered = [];
        foreach ($checkpoints as $cp) {
            if ($this->canViewCheckpoint($cp, $memberId, $requestingUser)) {
                $filtered[] = $cp;
            }
        }

        return $filtered;
    }

    /**
     * Verify whether a user is authorized to see a specific checkpoint photo & measurements
     */
    public function canViewCheckpoint($checkpoint, $memberId, $user) {
        if ($user['role'] === 'superadmin' || $user['role'] === 'owner') {
            return true;
        }

        // Check if user is the member themselves
        $stmt = $this->db->prepare("SELECT user_id FROM members WHERE id = ?");
        $stmt->execute([$memberId]);
        $memberUserId = $stmt->fetchColumn();

        if ($user['id'] == $memberUserId) {
            return true;
        }

        $privacy = $checkpoint['privacy_level'];

        if ($privacy === 'community') {
            return true;
        }

        if ($privacy === 'me_staff' && in_array($user['role'], ['manager', 'staff', 'trainer'])) {
            return true;
        }

        if ($privacy === 'me_trainer' && $user['role'] === 'trainer') {
            return true;
        }

        return false;
    }

    /**
     * Get baseline vs latest comparison data
     */
    public function getComparisonData($memberId, $requestingUser, $baseId = null, $latestId = null) {
        $all = $this->getMemberProgress($memberId, $requestingUser);
        if (count($all) < 1) {
            return null;
        }

        $base = null;
        $latest = null;

        if ($baseId) {
            foreach ($all as $item) {
                if ($item['id'] == $baseId) $base = $item;
            }
        }
        if (!$base) {
            $base = $all[0]; // First recorded checkpoint
        }

        if ($latestId) {
            foreach ($all as $item) {
                if ($item['id'] == $latestId) $latest = $item;
            }
        }
        if (!$latest) {
            $latest = end($all); // Most recent checkpoint
        }

        // Calculate deltas
        $deltas = [
            'weight_diff' => ($latest['weight_kg'] && $base['weight_kg']) ? round($latest['weight_kg'] - $base['weight_kg'], 2) : null,
            'chest_diff' => ($latest['chest_in'] && $base['chest_in']) ? round($latest['chest_in'] - $base['chest_in'], 2) : null,
            'waist_diff' => ($latest['waist_in'] && $base['waist_in']) ? round($latest['waist_in'] - $base['waist_in'], 2) : null,
            'arms_diff' => ($latest['arms_in'] && $base['arms_in']) ? round($latest['arms_in'] - $base['arms_in'], 2) : null,
            'thighs_diff' => ($latest['thighs_in'] && $base['thighs_in']) ? round($latest['thighs_in'] - $base['thighs_in'], 2) : null,
            'fat_diff' => ($latest['body_fat_pct'] && $base['body_fat_pct']) ? round($latest['body_fat_pct'] - $base['body_fat_pct'], 2) : null,
            'days_between' => round((strtotime($latest['recorded_date']) - strtotime($base['recorded_date'])) / 86400)
        ];

        return [
            'baseline' => $base,
            'latest' => $latest,
            'deltas' => $deltas,
            'all_checkpoints' => $all
        ];
    }
}
