<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Database;
use App\Services\Logger;

class TrainerService
{
    /**
     * Assign a member to a trainer within a gym.
     */
    public static function assignMember(
        int $gymId,
        int $trainerUserId,
        int $memberId,
        int $assignedByUserId,
        ?string $notes = null
    ): int {
        // 1. Verify trainer belongs to gym with trainer or staff/owner role
        $trainer = Database::fetchOne("
            SELECT gu.*, u.name, u.email 
            FROM `gym_users` gu
            INNER JOIN `users` u ON u.id = gu.user_id
            WHERE gu.gym_id = :gym_id AND gu.user_id = :trainer_id AND gu.is_active = 1
        ", [':gym_id' => $gymId, ':trainer_id' => $trainerUserId]);

        if (!$trainer) {
            throw new \InvalidArgumentException("Trainer does not exist or is not active in this gym.");
        }

        if (!in_array($trainer['role'], ['trainer', 'staff', 'owner'], true)) {
            throw new \InvalidArgumentException("Selected user does not have trainer privileges.");
        }

        // 2. Verify member belongs to gym and is not archived
        $member = Database::fetchOne("
            SELECT * FROM `members` 
            WHERE `id` = :member_id AND `gym_id` = :gym_id AND `is_archived` = 0
        ", [':member_id' => $memberId, ':gym_id' => $gymId]);

        if (!$member) {
            throw new \InvalidArgumentException("Member does not exist or is archived in this gym.");
        }

        // 3. Prevent duplicate active assignment
        $existing = Database::fetchOne("
            SELECT id FROM `trainer_member_assignments`
            WHERE `gym_id` = :gym_id AND `trainer_user_id` = :trainer_id AND `member_id` = :member_id AND `status` = 'active'
        ", [':gym_id' => $gymId, ':trainer_id' => $trainerUserId, ':member_id' => $memberId]);

        if ($existing) {
            throw new \InvalidArgumentException("This member is already actively assigned to this trainer.");
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `trainer_member_assignments` 
                    (`gym_id`, `trainer_user_id`, `member_id`, `status`, `assigned_by_user_id`, `notes`, `assigned_at`, `created_at`)
                VALUES 
                    (:gym_id, :trainer_id, :member_id, 'active', :assigned_by, :notes, NOW(), NOW())
            ");
            $stmt->execute([
                ':gym_id' => $gymId,
                ':trainer_id' => $trainerUserId,
                ':member_id' => $memberId,
                ':assigned_by' => $assignedByUserId,
                ':notes' => $notes,
            ]);

            $assignmentId = (int) $pdo->lastInsertId();

            // Audit
            $stmtAudit = $pdo->prepare("
                INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
                VALUES (:gym_id, :user_id, 'trainer_member_assigned', 'trainer_assignment', :entity_id, :details, NOW())
            ");
            $stmtAudit->execute([
                ':gym_id' => $gymId,
                ':user_id' => $assignedByUserId,
                ':entity_id' => $assignmentId,
                ':details' => json_encode([
                    'trainer_id' => $trainerUserId,
                    'trainer_name' => $trainer['name'],
                    'member_id' => $memberId,
                    'member_name' => $member['first_name'] . ' ' . $member['last_name'],
                ]),
            ]);

            $pdo->commit();

            Logger::info("Member assigned to trainer", [
                'gym_id' => $gymId,
                'trainer_id' => $trainerUserId,
                'member_id' => $memberId,
                'assigned_by' => $assignedByUserId,
            ]);

            return $assignmentId;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Terminate or end an active trainer assignment.
     */
    public static function unassignMember(int $gymId, int $assignmentId, int $actorUserId): bool
    {
        $assignment = Database::fetchOne("
            SELECT * FROM `trainer_member_assignments` 
            WHERE `id` = :id AND `gym_id` = :gym_id AND `status` = 'active'
        ", [':id' => $assignmentId, ':gym_id' => $gymId]);

        if (!$assignment) {
            throw new \InvalidArgumentException("Active assignment not found.");
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                UPDATE `trainer_member_assignments` 
                SET `status` = 'inactive', `ended_at` = NOW(), `updated_at` = NOW() 
                WHERE `id` = :id AND `gym_id` = :gym_id
            ");
            $stmt->execute([':id' => $assignmentId, ':gym_id' => $gymId]);

            // Audit
            $stmtAudit = $pdo->prepare("
                INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
                VALUES (:gym_id, :user_id, 'trainer_member_unassigned', 'trainer_assignment', :entity_id, :details, NOW())
            ");
            $stmtAudit->execute([
                ':gym_id' => $gymId,
                ':user_id' => $actorUserId,
                ':entity_id' => $assignmentId,
                ':details' => json_encode([
                    'trainer_id' => $assignment['trainer_user_id'],
                    'member_id' => $assignment['member_id'],
                ]),
            ]);

            $pdo->commit();

            Logger::info("Trainer assignment ended", [
                'gym_id' => $gymId,
                'assignment_id' => $assignmentId,
                'actor_user_id' => $actorUserId,
            ]);

            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Check if a member is currently assigned to a trainer.
     */
    public static function isMemberAssigned(int $gymId, int $trainerUserId, int $memberId): bool
    {
        $res = Database::fetchOne("
            SELECT id FROM `trainer_member_assignments`
            WHERE `gym_id` = :gym_id AND `trainer_user_id` = :trainer_id AND `member_id` = :member_id AND `status` = 'active'
            LIMIT 1
        ", [':gym_id' => $gymId, ':trainer_id' => $trainerUserId, ':member_id' => $memberId]);

        return !empty($res);
    }

    /**
     * Get active members assigned to a trainer.
     */
    public static function getAssignedMembers(int $gymId, int $trainerUserId): array
    {
        return Database::fetchAll("
            SELECT 
                m.*,
                tma.id as assignment_id,
                tma.assigned_at,
                tma.notes as assignment_notes,
                mem.plan_name_snapshot,
                mem.expiry_date as plan_expiry_date,
                mem.status as membership_status
            FROM `trainer_member_assignments` tma
            INNER JOIN `members` m ON m.id = tma.member_id
            LEFT JOIN `memberships` mem ON mem.member_id = m.id AND mem.status = 'active'
            WHERE tma.gym_id = :gym_id AND tma.trainer_user_id = :trainer_id AND tma.status = 'active'
            ORDER BY m.first_name ASC, m.last_name ASC
        ", [':gym_id' => $gymId, ':trainer_id' => $trainerUserId]);
    }

    /**
     * Add or set trainer availability slot.
     */
    public static function setAvailability(int $gymId, int $trainerUserId, array $data): int
    {
        $type = $data['type'] ?? 'recurring';
        if (!in_array($type, ['recurring', 'exception', 'unavailable'], true)) {
            throw new \InvalidArgumentException("Invalid availability type.");
        }

        $dayOfWeek = isset($data['day_of_week']) && $data['day_of_week'] !== '' ? (int) $data['day_of_week'] : null;
        $specificDate = !empty($data['specific_date']) ? trim($data['specific_date']) : null;
        $startTime = trim($data['start_time'] ?? '');
        $endTime = trim($data['end_time'] ?? '');
        $isAvailable = isset($data['is_available']) ? (int) $data['is_available'] : 1;
        $slotDuration = isset($data['slot_duration_minutes']) ? (int) $data['slot_duration_minutes'] : 60;
        $notes = !empty($data['notes']) ? trim($data['notes']) : null;

        if ($type === 'recurring' && ($dayOfWeek === null || $dayOfWeek < 0 || $dayOfWeek > 6)) {
            throw new \InvalidArgumentException("Day of week (0-6) is required for recurring availability.");
        }

        if (($type === 'exception' || $type === 'unavailable') && empty($specificDate)) {
            throw new \InvalidArgumentException("Specific date is required for availability exceptions.");
        }

        if (strtotime($startTime) >= strtotime($endTime)) {
            throw new \InvalidArgumentException("Start time must be before end time.");
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO `trainer_availabilities` 
                (`gym_id`, `trainer_user_id`, `type`, `day_of_week`, `specific_date`, `start_time`, `end_time`, `is_available`, `slot_duration_minutes`, `notes`, `created_at`)
            VALUES 
                (:gym_id, :trainer_id, :type, :day, :date, :start, :end, :avail, :duration, :notes, NOW())
        ");
        $stmt->execute([
            ':gym_id' => $gymId,
            ':trainer_id' => $trainerUserId,
            ':type' => $type,
            ':day' => $dayOfWeek,
            ':date' => $specificDate,
            ':start' => $startTime,
            ':end' => $endTime,
            ':avail' => $isAvailable,
            ':duration' => $slotDuration,
            ':notes' => $notes,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Delete an availability slot.
     */
    public static function deleteAvailability(int $gymId, int $trainerUserId, int $availabilityId): bool
    {
        $stmt = Database::getConnection()->prepare("
            DELETE FROM `trainer_availabilities` 
            WHERE `id` = :id AND `trainer_user_id` = :trainer_id AND `gym_id` = :gym_id
        ");
        $stmt->execute([
            ':id' => $availabilityId,
            ':trainer_id' => $trainerUserId,
            ':gym_id' => $gymId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Get trainer availability schedule.
     */
    public static function getTrainerAvailability(int $gymId, int $trainerUserId): array
    {
        return Database::fetchAll("
            SELECT * FROM `trainer_availabilities`
            WHERE `gym_id` = :gym_id AND `trainer_user_id` = :trainer_id
            ORDER BY `type` ASC, `day_of_week` ASC, `specific_date` ASC, `start_time` ASC
        ", [':gym_id' => $gymId, ':trainer_id' => $trainerUserId]);
    }

    /**
     * Schedule a training session with conflict detection.
     */
    public static function scheduleSession(
        int $gymId,
        int $trainerUserId,
        int $memberId,
        string $sessionDate,
        string $startTime,
        string $endTime,
        int $createdById,
        ?string $notes = null
    ): int {
        // Validate relationship: member must be actively assigned to this trainer (unless created by Owner)
        $isAssigned = self::isMemberAssigned($gymId, $trainerUserId, $memberId);
        $creatorRole = Database::fetchOne("
            SELECT role FROM `gym_users` WHERE `gym_id` = :gym_id AND `user_id` = :user_id
        ", [':gym_id' => $gymId, ':user_id' => $createdById])['role'] ?? 'staff';

        if (!$isAssigned && $creatorRole !== 'owner') {
            throw new \InvalidArgumentException("This member is not assigned to the specified trainer.");
        }

        // Validate time ordering
        if (strtotime($startTime) >= strtotime($endTime)) {
            throw new \InvalidArgumentException("Session start time must be before end time.");
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sessionDate)) {
            throw new \InvalidArgumentException("Invalid session date format (YYYY-MM-DD required).");
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            // Conflict check: ensure trainer has no overlapping scheduled sessions on that date
            $conflict = Database::fetchOne("
                SELECT id, start_time, end_time 
                FROM `training_sessions`
                WHERE `gym_id` = :gym_id 
                  AND `trainer_user_id` = :trainer_id 
                  AND `session_date` = :s_date 
                  AND `status` = 'scheduled'
                  AND NOT (`end_time` <= :start_time OR `start_time` >= :end_time)
                LIMIT 1
                FOR UPDATE
            ", [
                ':gym_id' => $gymId,
                ':trainer_id' => $trainerUserId,
                ':s_date' => $sessionDate,
                ':start_time' => $startTime,
                ':end_time' => $endTime,
            ]);

            if ($conflict) {
                throw new \InvalidArgumentException(
                    "Scheduling conflict: Trainer already has a session booked from {$conflict['start_time']} to {$conflict['end_time']}."
                );
            }

            $stmt = $pdo->prepare("
                INSERT INTO `training_sessions` 
                    (`gym_id`, `trainer_user_id`, `member_id`, `session_date`, `start_time`, `end_time`, `status`, `session_notes`, `created_by_user_id`, `created_at`)
                VALUES 
                    (:gym_id, :trainer_id, :member_id, :s_date, :start, :end, 'scheduled', :notes, :created_by, NOW())
            ");
            $stmt->execute([
                ':gym_id' => $gymId,
                ':trainer_id' => $trainerUserId,
                ':member_id' => $memberId,
                ':s_date' => $sessionDate,
                ':start' => $startTime,
                ':end' => $endTime,
                ':notes' => $notes,
                ':created_by' => $createdById,
            ]);

            $sessionId = (int) $pdo->lastInsertId();

            // Audit
            $stmtAudit = $pdo->prepare("
                INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
                VALUES (:gym_id, :user_id, 'training_session_scheduled', 'training_session', :entity_id, :details, NOW())
            ");
            $stmtAudit->execute([
                ':gym_id' => $gymId,
                ':user_id' => $createdById,
                ':entity_id' => $sessionId,
                ':details' => json_encode([
                    'trainer_id' => $trainerUserId,
                    'member_id' => $memberId,
                    'date' => $sessionDate,
                    'start' => $startTime,
                    'end' => $endTime,
                ]),
            ]);

            $pdo->commit();

            Logger::info("Training session scheduled", [
                'gym_id' => $gymId,
                'session_id' => $sessionId,
                'trainer_id' => $trainerUserId,
                'member_id' => $memberId,
                'date' => $sessionDate,
            ]);

            return $sessionId;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Update session status (completed, cancelled, no_show).
     */
    public static function updateSessionStatus(
        int $gymId,
        int $sessionId,
        int $actorUserId,
        string $status,
        ?string $notes = null
    ): bool {
        if (!in_array($status, ['scheduled', 'completed', 'cancelled', 'no_show'], true)) {
            throw new \InvalidArgumentException("Invalid session status.");
        }

        // Verify session belongs to gym and trainer or owner
        $session = Database::fetchOne("
            SELECT * FROM `training_sessions` WHERE `id` = :id AND `gym_id` = :gym_id
        ", [':id' => $sessionId, ':gym_id' => $gymId]);

        if (!$session) {
            throw new \InvalidArgumentException("Training session not found.");
        }

        $userRole = Database::fetchOne("
            SELECT role FROM `gym_users` WHERE `gym_id` = :gym_id AND `user_id` = :user_id
        ", [':gym_id' => $gymId, ':user_id' => $actorUserId])['role'] ?? 'staff';

        if ((int)$session['trainer_user_id'] !== $actorUserId && $userRole !== 'owner') {
            throw new \InvalidArgumentException("Unauthorized: You can only update sessions assigned to you.");
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                UPDATE `training_sessions` 
                SET `status` = :status, 
                    `session_notes` = COALESCE(:notes, `session_notes`), 
                    `updated_at` = NOW() 
                WHERE `id` = :id AND `gym_id` = :gym_id
            ");
            $stmt->execute([
                ':status' => $status,
                ':notes' => $notes,
                ':id' => $sessionId,
                ':gym_id' => $gymId,
            ]);

            // Audit
            $stmtAudit = $pdo->prepare("
                INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
                VALUES (:gym_id, :user_id, 'training_session_status_updated', 'training_session', :entity_id, :details, NOW())
            ");
            $stmtAudit->execute([
                ':gym_id' => $gymId,
                ':user_id' => $actorUserId,
                ':entity_id' => $sessionId,
                ':details' => json_encode([
                    'old_status' => $session['status'],
                    'new_status' => $status,
                ]),
            ]);

            $pdo->commit();

            Logger::info("Training session status updated to {$status}", [
                'gym_id' => $gymId,
                'session_id' => $sessionId,
                'status' => $status,
            ]);

            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Record training progress for an assigned member.
     */
    public static function recordProgress(
        int $gymId,
        int $trainerUserId,
        int $memberId,
        array $data
    ): int {
        // Enforce member assignment
        if (!self::isMemberAssigned($gymId, $trainerUserId, $memberId)) {
            // Check if actor is owner
            $actorRole = Database::fetchOne("
                SELECT role FROM `gym_users` WHERE `gym_id` = :gym_id AND `user_id` = :user_id
            ", [':gym_id' => $gymId, ':user_id' => $trainerUserId])['role'] ?? 'staff';

            if ($actorRole !== 'owner') {
                throw new \InvalidArgumentException("Cannot record progress: Member is not assigned to this trainer.");
            }
        }

        $workoutSummary = trim($data['workout_summary'] ?? '');
        if (empty($workoutSummary)) {
            throw new \InvalidArgumentException("Workout summary is required.");
        }

        $recordDate = !empty($data['record_date']) ? trim($data['record_date']) : date('Y-m-d');
        $trainerFeedback = !empty($data['trainer_feedback']) ? trim($data['trainer_feedback']) : null;
        $sessionId = !empty($data['session_id']) ? (int) $data['session_id'] : null;

        $weightKg = null;
        if (isset($data['weight_kg']) && $data['weight_kg'] !== '') {
            $w = (float) $data['weight_kg'];
            if ($w < 20 || $w > 350) {
                throw new \InvalidArgumentException("Weight must be a valid number between 20kg and 350kg.");
            }
            $weightKg = $w;
        }

        $bodyFat = null;
        if (isset($data['body_fat_percentage']) && $data['body_fat_percentage'] !== '') {
            $bf = (float) $data['body_fat_percentage'];
            if ($bf < 2 || $bf > 70) {
                throw new \InvalidArgumentException("Body fat percentage must be between 2% and 70%.");
            }
            $bodyFat = $bf;
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `training_progress_records` 
                    (`gym_id`, `trainer_user_id`, `member_id`, `session_id`, `record_date`, `workout_summary`, `trainer_feedback`, `weight_kg`, `body_fat_percentage`, `created_at`)
                VALUES 
                    (:gym_id, :trainer_id, :member_id, :sess_id, :r_date, :workout, :feedback, :weight, :bf, NOW())
            ");
            $stmt->execute([
                ':gym_id' => $gymId,
                ':trainer_id' => $trainerUserId,
                ':member_id' => $memberId,
                ':sess_id' => $sessionId,
                ':r_date' => $recordDate,
                ':workout' => $workoutSummary,
                ':feedback' => $trainerFeedback,
                ':weight' => $weightKg,
                ':bf' => $bodyFat,
            ]);

            $progressId = (int) $pdo->lastInsertId();

            // Audit
            $stmtAudit = $pdo->prepare("
                INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
                VALUES (:gym_id, :user_id, 'training_progress_recorded', 'training_progress', :entity_id, :details, NOW())
            ");
            $stmtAudit->execute([
                ':gym_id' => $gymId,
                ':user_id' => $trainerUserId,
                ':entity_id' => $progressId,
                ':details' => json_encode([
                    'member_id' => $memberId,
                    'record_date' => $recordDate,
                    'weight_kg' => $weightKg,
                    'body_fat' => $bodyFat,
                ]),
            ]);

            $pdo->commit();

            Logger::info("Training progress recorded", [
                'gym_id' => $gymId,
                'trainer_id' => $trainerUserId,
                'member_id' => $memberId,
                'progress_id' => $progressId,
            ]);

            return $progressId;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Get historical progress records for a member.
     */
    public static function getMemberProgressHistory(
        int $gymId,
        int $memberId,
        ?int $trainerUserId = null
    ): array {
        // If trainer specified, enforce assignment isolation
        if ($trainerUserId !== null) {
            $isAssigned = self::isMemberAssigned($gymId, $trainerUserId, $memberId);
            if (!$isAssigned) {
                return [];
            }
        }

        return Database::fetchAll("
            SELECT tpr.*, u.name as trainer_name, ts.start_time, ts.end_time
            FROM `training_progress_records` tpr
            INNER JOIN `users` u ON u.id = tpr.trainer_user_id
            LEFT JOIN `training_sessions` ts ON ts.id = tpr.session_id
            WHERE tpr.gym_id = :gym_id AND tpr.member_id = :member_id
            ORDER BY tpr.record_date DESC, tpr.id DESC
        ", [':gym_id' => $gymId, ':member_id' => $memberId]);
    }
}
