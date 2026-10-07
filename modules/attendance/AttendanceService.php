<?php
/**
 * Attendance Service - Multi-Method Access & Expiry Blocking Engine
 */

class AttendanceService {
    public function recordAttendance($tokenOrCode, $method = 'qr_scanner', $branchId = null, $recordedByUserId = null) {
        return self::validateAndCheckIn($tokenOrCode, $method, $branchId, null, $recordedByUserId);
    }

    public function recordCheckin($data) {
        $tokenOrCode = $data['qr_token'] ?? $data['member_code'] ?? $data['token'] ?? '';
        $method = $data['method'] ?? 'member_app';
        $branchId = $data['branch_id'] ?? null;
        $res = self::validateAndCheckIn($tokenOrCode, $method, $branchId, null, Auth::id());
        if ($res['success']) {
            return ['success' => true, 'message' => $res['message']];
        } else {
            return ['success' => false, 'error' => $res['message']];
        }
    }

    public function getMemberAttendanceStats($memberId) {
        $totalWorkouts = (int)Database::fetchValue("SELECT COUNT(*) FROM attendance WHERE member_id = :mid", [':mid' => $memberId]);
        $thisMonth = (int)Database::fetchValue("SELECT COUNT(*) FROM attendance WHERE member_id = :mid AND MONTH(check_in_time) = MONTH(CURRENT_DATE()) AND YEAR(check_in_time) = YEAR(CURRENT_DATE())", [':mid' => $memberId]);
        $streak = self::getMemberStreak($memberId);
        $lastVisit = Database::fetchValue("SELECT MAX(check_in_time) FROM attendance WHERE member_id = :mid", [':mid' => $memberId]);
        return [
            'total_workouts' => $totalWorkouts,
            'this_month' => $thisMonth,
            'streak' => $streak,
            'last_visit' => $lastVisit
        ];
    }

    public function getMemberAttendanceHistory($memberId, $limit = 30) {
        return Database::fetchAll(
            "SELECT a.*, b.name as branch_name 
             FROM attendance a 
             LEFT JOIN branches b ON a.branch_id = b.id 
             WHERE a.member_id = :mid 
             ORDER BY a.check_in_time DESC LIMIT " . (int)$limit,
            [':mid' => $memberId]
        );
    }

    public function getRecentCheckins($branchId = null, $limit = 10) {
        $query = "SELECT a.*, m.member_code, u.name as full_name, u.name, b.name as branch_name
                  FROM attendance a
                  INNER JOIN members m ON a.member_id = m.id
                  INNER JOIN users u ON m.user_id = u.id
                  INNER JOIN branches b ON a.branch_id = b.id";
        $params = [];
        if ($branchId) {
            $query .= " WHERE a.branch_id = :branch_id";
            $params[':branch_id'] = (int)$branchId;
        }
        $query .= " ORDER BY a.check_in_time DESC LIMIT " . (int)$limit;
        return Database::fetchAll($query, $params);
    }

    /**
     * Core Access Validation Engine (Zero-Bypass Server Validation)
     * Validates QR token, Member ID, PIN, or Phone against membership status and branch rules.
     */
    public static function validateAndCheckIn($tokenOrCode, $method = 'qr_scanner', $branchId = null, $pin = null, $recordedByUserId = null) {
        $member = MemberService::findByTokenOrCode($tokenOrCode);

        if (!$member) {
            AuditLogger::log('attendance.rejected_not_found', 'attendance', null, ['query' => $tokenOrCode, 'method' => $method], $recordedByUserId, $branchId);
            return [
                'success' => false,
                'code'    => 'NOT_FOUND',
                'message' => 'Member profile not found. Please verify Member ID or QR code.'
            ];
        }

        $memberId = (int)$member['id'];
        $memberBranchId = (int)$member['branch_id'];

        // 1. Verify PIN if method is PIN
        if (($method === 'pin' || $method === 'pin_pad') && $pin !== null) {
            if ($member['pin_code'] !== $pin) {
                AuditLogger::log('attendance.rejected_invalid_pin', 'member', $memberId, ['method' => $method], $recordedByUserId, $branchId);
                return [
                    'success' => false,
                    'code'    => 'INVALID_PIN',
                    'message' => 'Incorrect 4-digit security PIN.'
                ];
            }
        }

        // 2. Branch isolation check
        if ($branchId !== null && $memberBranchId !== (int)$branchId && empty($member['cross_branch_access'])) {
            AuditLogger::log('attendance.rejected_wrong_branch', 'member', $memberId, ['member_branch' => $memberBranchId, 'target_branch' => $branchId], $recordedByUserId, $branchId);
            return [
                'success' => false,
                'code'    => 'WRONG_BRANCH',
                'message' => 'Access restricted: Your membership is registered at ' . htmlspecialchars($member['branch_name']) . ' and does not include multi-branch access.'
            ];
        }

        // 3. Frozen membership check
        if ($member['status'] === 'frozen') {
            AuditLogger::log('attendance.rejected_frozen', 'member', $memberId, [], $recordedByUserId, $branchId);
            return [
                'success' => false,
                'code'    => 'MEMBERSHIP_FROZEN',
                'message' => 'Membership is currently FROZEN. Please contact the front desk to unfreeze.'
            ];
        }

        // 4. Expiry validation (Strict Server-Side Blocking)
        $today = date('Y-m-d');
        if (empty($member['end_date']) || $member['end_date'] < $today) {
            $expiredDate = !empty($member['end_date']) ? date('d M Y', strtotime($member['end_date'])) : 'No active plan';
            AuditLogger::log('attendance.blocked_expired', 'member', $memberId, ['expiry' => $member['end_date'] ?? null], $recordedByUserId, $branchId);
            return [
                'success' => false,
                'code'    => 'MEMBERSHIP_EXPIRED',
                'message' => "ACCESS DENIED: Membership expired on {$expiredDate}. Please renew your membership to continue accessing the gym.",
                'member'  => [
                    'id'        => $memberId,
                    'name'      => $member['name'],
                    'code'      => $member['member_code'],
                    'end_date'  => $member['end_date'] ?? null
                ]
            ];
        }

        // 5. Duplicate check-in cooldown prevention (within ATTENDANCE_COOLDOWN_MINUTES)
        $cooldown = defined('ATTENDANCE_COOLDOWN_MINUTES') ? ATTENDANCE_COOLDOWN_MINUTES : 30;
        $recentCheckin = Database::fetchOne(
            "SELECT id, check_in_time, TIMESTAMPDIFF(MINUTE, check_in_time, NOW()) as mins_ago 
             FROM attendance 
             WHERE member_id = :mid AND DATE(check_in_time) = :today AND TIMESTAMPDIFF(MINUTE, check_in_time, NOW()) < :cooldown
             ORDER BY check_in_time DESC LIMIT 1",
            [':mid' => $memberId, ':today' => $today, ':cooldown' => $cooldown]
        );

        if ($recentCheckin) {
            return [
                'success' => true,
                'code'    => 'ALREADY_CHECKED_IN',
                'message' => 'Already checked in today at ' . date('h:i A', strtotime($recentCheckin['check_in_time'])) . ' (' . $recentCheckin['mins_ago'] . ' mins ago). Welcome back!',
                'member'  => [
                    'id'             => $memberId,
                    'name'           => $member['name'],
                    'code'           => $member['member_code'],
                    'days_remaining' => (int)$member['days_remaining']
                ]
            ];
        }

        // 6. Record Valid Attendance
        $validMethods = ['qr', 'qr_scanner', 'kiosk', 'pin', 'pin_pad', 'member_app', 'staff_manual', 'phone', 'rfid_card', 'face_recognition'];
        $cleanMethod = in_array($method, $validMethods) ? $method : 'qr';

        $targetBranch = ($branchId !== null) ? (int)$branchId : $memberBranchId;
        $timeNow = date('Y-m-d H:i:s');

        $query = "INSERT INTO attendance (member_id, branch_id, check_in_time, method, created_at)
                  VALUES (:member_id, :branch_id, :check_in_time, :method, NOW())";

        $attendanceId = Database::insert($query, [
            ':member_id'     => $memberId,
            ':branch_id'     => $targetBranch,
            ':check_in_time' => $timeNow,
            ':method'        => $cleanMethod
        ]);

        AuditLogger::log('attendance.checkin_success', 'attendance', $attendanceId, ['member' => $member['name'], 'method' => $method], $recordedByUserId, $targetBranch);

        return [
            'success' => true,
            'code'    => 'CHECKIN_SUCCESS',
            'message' => "Welcome, {$member['name']}! Check-in recorded at " . date('h:i A', strtotime($timeNow)) . ".",
            'member'  => [
                'id'             => $memberId,
                'name'           => $member['name'],
                'code'           => $member['member_code'],
                'days_remaining' => (int)$member['days_remaining'],
                'time'           => date('h:i A', strtotime($timeNow))
            ]
        ];
    }

    /**
     * Get attendance register for a specific date
     */
    public static function getDailyAttendance($branchId = null, $date = null) {
        $targetDate = $date ?: date('Y-m-d');
        
        $query = "SELECT a.*, m.member_code, u.name, u.name as full_name, u.email, u.phone,
                         p.name as plan_name, b.name as branch_name
                  FROM attendance a
                  INNER JOIN members m ON a.member_id = m.id
                  INNER JOIN users u ON m.user_id = u.id
                  INNER JOIN branches b ON a.branch_id = b.id
                  LEFT JOIN member_subscriptions ms ON ms.id = (
                      SELECT id FROM member_subscriptions WHERE member_id = m.id ORDER BY id DESC LIMIT 1
                  )
                  LEFT JOIN membership_plans p ON ms.plan_id = p.id
                  WHERE DATE(a.check_in_time) = :date";

        $params = [':date' => $targetDate];

        if ($branchId !== null) {
            $query .= " AND a.branch_id = :branch_id";
            $params[':branch_id'] = (int)$branchId;
        }

        $query .= " ORDER BY a.check_in_time DESC";

        try {
            return Database::fetchAll($query, $params);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Calculate Member Attendance Streak (Consecutive workout days)
     */
    public static function getMemberStreak($memberId) {
        $dates = Database::fetchAll(
            "SELECT DISTINCT DATE(check_in_time) as attendance_date FROM attendance WHERE member_id = :mid ORDER BY check_in_time DESC LIMIT 30",
            [':mid' => (int)$memberId]
        );

        if (empty($dates)) {
            return 0;
        }

        $streak = 0;
        $currentDate = new DateTime();
        
        $firstDate = new DateTime($dates[0]['attendance_date']);
        $diff = (int)$currentDate->diff($firstDate)->format('%a');

        if ($diff > 1) {
            return 0; // Streak broken
        }

        $streak = 1;
        for ($i = 0; $i < count($dates) - 1; $i++) {
            $d1 = new DateTime($dates[$i]['attendance_date']);
            $d2 = new DateTime($dates[$i + 1]['attendance_date']);
            $dayGap = (int)$d1->diff($d2)->format('%a');

            if ($dayGap === 1) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }
}
