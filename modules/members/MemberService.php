<?php
/**
 * Member Service - Comprehensive Member Management & Expiry Engine
 */

class MemberService {
    public function getAll($branchId = null, $status = null, $search = null) {
        return self::getAllMembers($branchId, $status, $search);
    }

    public function getMemberById($id) {
        return self::findById($id);
    }

    public function find($id) {
        return self::findById($id);
    }

    /**
     * Generate unique Member ID (e.g. GYM-1001, GYM-1002)
     */
    public static function generateMemberCode() {
        $lastId = Database::fetchValue("SELECT MAX(id) FROM members");
        $nextNumber = $lastId ? ((int)$lastId + 1001) : 1001;
        return 'GYM-' . $nextNumber;
    }

    /**
     * Generate unique cryptographic QR token
     */
    public static function generateQRToken($memberCode) {
        if (function_exists('random_bytes')) {
            $rand = bin2hex(random_bytes(16));
        } else {
            $rand = md5(uniqid(mt_rand(), true));
        }
        return 'QR-FP-' . strtoupper($memberCode) . '-' . strtoupper($rand);
    }

    /**
     * Calculate and sync live membership status
     */
    public static function calculateStatus($subscription, $isFrozen = false) {
        if (!$subscription) {
            return 'expired';
        }

        if ($subscription['status'] === 'cancelled') {
            return 'cancelled';
        }

        if ($isFrozen || $subscription['status'] === 'frozen') {
            return 'frozen';
        }

        $today = date('Y-m-d');
        $endDate = $subscription['end_date'];

        if ($endDate < $today) {
            return 'expired';
        }

        $daysRemaining = (int) (strtotime($endDate) - strtotime($today)) / 86400;
        if ($daysRemaining <= 7) {
            return 'expiring_soon';
        }

        return 'active';
    }

    /**
     * Get all members with filters (search, branch, status)
     */
    public static function getAllMembers($branchId = null, $status = null, $search = null) {
        $query = "SELECT m.*, u.name, u.name as full_name, u.email, u.phone, u.is_active as user_active,
                         b.name as branch_name, b.code as branch_code,
                         ms.id as subscription_id, ms.plan_id, ms.start_date, ms.end_date, ms.status as subscription_status,
                         ms.price, ms.discount,
                         p.name as plan_name, p.duration_days,
                         DATEDIFF(ms.end_date, CURDATE()) as days_remaining,
                         (SELECT MAX(check_in_time) FROM attendance a WHERE a.member_id = m.id) as last_visit_date
                  FROM members m
                  INNER JOIN users u ON m.user_id = u.id
                  INNER JOIN branches b ON m.branch_id = b.id
                  LEFT JOIN member_subscriptions ms ON ms.id = (
                      SELECT id FROM member_subscriptions WHERE member_id = m.id ORDER BY id DESC LIMIT 1
                  )
                  LEFT JOIN membership_plans p ON ms.plan_id = p.id";

        $where = [];
        $params = [];

        if ($branchId !== null) {
            $where[] = "m.branch_id = :branch_id";
            $params[':branch_id'] = (int)$branchId;
        }

        if ($status !== null && $status !== '') {
            $where[] = "m.status = :status";
            $params[':status'] = $status;
        }

        if ($search !== null && trim($search) !== '') {
            $where[] = "(u.name LIKE :s1 OR u.email LIKE :s2 OR u.phone LIKE :s3 OR m.member_code LIKE :s4)";
            $searchTerm = '%' . trim($search) . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
            $params[':s4'] = $searchTerm;
        }

        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }

        $query .= " ORDER BY m.id DESC";

        try {
            $members = Database::fetchAll($query, $params);
            foreach ($members as &$m) {
                $rawStatus = $m['status'] ?? 'active';
                if ($rawStatus === 'frozen') {
                    $m['current_status'] = 'frozen';
                } elseif ($rawStatus === 'cancelled') {
                    $m['current_status'] = 'cancelled';
                } elseif (isset($m['days_remaining']) && $m['days_remaining'] !== null) {
                    if ((int)$m['days_remaining'] < 0) {
                        $m['current_status'] = 'expired';
                    } elseif ((int)$m['days_remaining'] <= 7) {
                        $m['current_status'] = 'expiring_soon';
                    } else {
                        $m['current_status'] = 'active';
                    }
                } else {
                    $m['current_status'] = $rawStatus;
                }
            }
            unset($m);
            return $members;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Find member by ID with full relation graph
     */
    public static function findById($id) {
        $query = "SELECT m.*, u.name, u.name as full_name, u.email, u.phone,
                         b.name as branch_name, b.code as branch_code,
                         ms.id as subscription_id, ms.plan_id, ms.start_date, ms.end_date, ms.status as subscription_status,
                         ms.price, ms.discount,
                         p.name as plan_name, p.duration_days, p.price as plan_price,
                         DATEDIFF(ms.end_date, CURDATE()) as days_remaining
                  FROM members m
                  INNER JOIN users u ON m.user_id = u.id
                  INNER JOIN branches b ON m.branch_id = b.id
                  LEFT JOIN member_subscriptions ms ON ms.id = (
                      SELECT id FROM member_subscriptions WHERE member_id = m.id ORDER BY id DESC LIMIT 1
                  )
                  LEFT JOIN membership_plans p ON ms.plan_id = p.id
                  WHERE m.id = :id LIMIT 1";

        return Database::fetchOne($query, [':id' => (int)$id]);
    }

    /**
     * Find member by Member ID or QR Token (For attendance check-in)
     */
    public static function findByTokenOrCode($tokenOrCode) {
        $val = trim($tokenOrCode);
        $query = "SELECT m.*, u.name, u.name as full_name, b.name as branch_name,
                         ms.id as subscription_id, ms.end_date, ms.status as subscription_status,
                         DATEDIFF(ms.end_date, CURDATE()) as days_remaining
                  FROM members m
                  INNER JOIN users u ON m.user_id = u.id
                  INNER JOIN branches b ON m.branch_id = b.id
                  LEFT JOIN member_subscriptions ms ON ms.id = (
                      SELECT id FROM member_subscriptions WHERE member_id = m.id ORDER BY id DESC LIMIT 1
                  )
                  WHERE (m.qr_token = :val1 OR m.member_code = :val2 OR u.phone = :val3)
                  LIMIT 1";

        return Database::fetchOne($query, [
            ':val1' => $val,
            ':val2' => $val,
            ':val3' => $val
        ]);
    }

    /**
     * Register a new member with user account, plan subscription, and optional initial payment
     */
    public static function register($data, $collectedByUserId = 1) {
        Database::beginTransaction();

        try {
            // 1. Create User Account
            $name = !empty($data['name']) ? trim($data['name']) : (!empty($data['full_name']) ? trim($data['full_name']) : 'Member');
            $password = !empty($data['password']) ? $data['password'] : 'password';
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $userQuery = "INSERT INTO users (branch_id, name, email, phone, password, role, is_active, created_at)
                          VALUES (:branch_id, :name, :email, :phone, :password, 'member', 1, NOW())";
            
            $userId = Database::insert($userQuery, [
                ':branch_id' => (int)$data['branch_id'],
                ':name'      => $name,
                ':email'     => strtolower(trim($data['email'])),
                ':phone'     => isset($data['phone']) ? trim($data['phone']) : null,
                ':password'  => $passwordHash
            ]);

            // 2. Create Member Profile
            $memberCode = self::generateMemberCode();
            $qrToken = self::generateQRToken($memberCode);

            $memberQuery = "INSERT INTO members (user_id, branch_id, member_code, qr_token, pin_code, date_of_birth, gender, blood_group, 
                                                 emergency_contact_name, emergency_contact_phone, address, medical_conditions, status, created_at)
                            VALUES (:user_id, :branch_id, :member_code, :qr_token, :pin_code, :dob, :gender, :blood_group,
                                    :emergency_name, :emergency_phone, :address, :medical_notes, 'active', NOW())";

            $memberId = Database::insert($memberQuery, [
                ':user_id'         => $userId,
                ':branch_id'       => (int)$data['branch_id'],
                ':member_code'     => $memberCode,
                ':qr_token'        => $qrToken,
                ':pin_code'        => !empty($data['pin_code']) ? trim($data['pin_code']) : '1234',
                ':dob'             => !empty($data['date_of_birth']) ? $data['date_of_birth'] : (!empty($data['dob']) ? $data['dob'] : null),
                ':gender'          => !empty($data['gender']) ? $data['gender'] : 'male',
                ':blood_group'     => !empty($data['blood_group']) ? trim($data['blood_group']) : null,
                ':emergency_name'  => isset($data['emergency_contact_name']) ? trim($data['emergency_contact_name']) : null,
                ':emergency_phone' => isset($data['emergency_contact_phone']) ? trim($data['emergency_contact_phone']) : null,
                ':address'         => isset($data['address']) ? trim($data['address']) : null,
                ':medical_notes'   => isset($data['medical_conditions']) ? trim($data['medical_conditions']) : null
            ]);

            // 3. Create Initial Membership Subscription
            if (!empty($data['plan_id'])) {
                $plan = PlanService::findById($data['plan_id']);
                if ($plan) {
                    $startDate = !empty($data['start_date']) ? $data['start_date'] : date('Y-m-d');
                    $durationDays = (int)$plan['duration_days'];
                    $endDate = date('Y-m-d', strtotime("+{$durationDays} days", strtotime($startDate)));

                    $planPrice = (float)$plan['price'];
                    $discount = !empty($data['discount']) ? (float)$data['discount'] : 0.00;
                    $finalPrice = max(0, $planPrice - $discount);
                    $paidAmount = !empty($data['paid_amount']) ? (float)$data['paid_amount'] : $finalPrice;

                    $subQuery = "INSERT INTO member_subscriptions (member_id, plan_id, start_date, end_date, price, discount, status, notes, created_at)
                                 VALUES (:member_id, :plan_id, :start_date, :end_date, :price, :discount, 'active', :notes, NOW())";

                    $subscriptionId = Database::insert($subQuery, [
                        ':member_id'  => $memberId,
                        ':plan_id'    => $plan['id'],
                        ':start_date' => $startDate,
                        ':end_date'   => $endDate,
                        ':price'      => $planPrice,
                        ':discount'   => $discount,
                        ':notes'      => 'Initial plan subscription'
                    ]);

                    // 4. Record Initial Payment (if amount > 0)
                    if ($paidAmount > 0) {
                        $receiptNo = 'REC-' . date('Y') . '-' . str_pad($memberId, 4, '0', STR_PAD_LEFT);
                        $payQuery = "INSERT INTO payments (member_id, subscription_id, receipt_number, amount, payment_method, transaction_id, status, notes, created_at)
                                     VALUES (:member_id, :subscription_id, :receipt_number, :amount, :payment_method, :ref, 'completed', :notes, NOW())";
                        
                        Database::insert($payQuery, [
                            ':member_id'       => $memberId,
                            ':subscription_id' => $subscriptionId,
                            ':receipt_number'  => $receiptNo,
                            ':amount'          => $paidAmount,
                            ':payment_method'  => !empty($data['payment_method']) ? $data['payment_method'] : 'cash',
                            ':ref'             => isset($data['transaction_id']) ? trim($data['transaction_id']) : null,
                            ':notes'           => 'Initial plan registration payment'
                        ]);
                    }
                }
            }

            Database::commit();

            AuditLogger::log('member.registered', 'member', $memberId, ['name' => $name, 'code' => $memberCode], $collectedByUserId, $data['branch_id']);
            return $memberId;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /**
     * Freeze membership for a specific duration
     */
    public static function freezeMembership($memberId, $freezeStart, $freezeEnd, $reason, $approvedByUserId) {
        $member = self::findById($memberId);
        if (!$member || empty($member['subscription_id'])) {
            throw new Exception("Active membership subscription not found for this member.");
        }

        $subscriptionId = (int)$member['subscription_id'];
        $daysFrozen = (int) ((strtotime($freezeEnd) - strtotime($freezeStart)) / 86400) + 1;

        if ($daysFrozen <= 0) {
            throw new Exception("Freeze end date must be after freeze start date.");
        }

        Database::beginTransaction();

        try {
            // 1. Log freeze record
            $freezeQuery = "INSERT INTO member_freezes (member_id, subscription_id, freeze_start_date, freeze_end_date, days_frozen, reason, status, created_at)
                            VALUES (:member_id, :subscription_id, :freeze_start, :freeze_end, :days_frozen, :reason, 'active', NOW())";
            
            Database::insert($freezeQuery, [
                ':member_id'       => $memberId,
                ':subscription_id' => $subscriptionId,
                ':freeze_start'    => $freezeStart,
                ':freeze_end'      => $freezeEnd,
                ':days_frozen'     => $daysFrozen,
                ':reason'          => $reason
            ]);

            // 2. Adjust subscription end date forward by frozen days
            $currentEndDate = $member['end_date'];
            $newEndDate = date('Y-m-d', strtotime("+{$daysFrozen} days", strtotime($currentEndDate)));

            Database::execute("UPDATE member_subscriptions SET end_date = :new_end, status = 'frozen' WHERE id = :id", [
                ':new_end' => $newEndDate,
                ':id'      => $subscriptionId
            ]);

            // 3. Update member status
            Database::execute("UPDATE members SET status = 'frozen' WHERE id = :id", [
                ':id' => $memberId
            ]);

            Database::commit();

            AuditLogger::log('membership.frozen', 'member', $memberId, ['days' => $daysFrozen, 'new_end_date' => $newEndDate, 'reason' => $reason], $approvedByUserId, $member['branch_id']);
            return true;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /**
     * Renew membership (creating next period while preserving history)
     */
    public static function renewMembership($memberId, $planId, $startDate, $paidAmount, $paymentMethod, $discountAmount = 0.00, $collectedByUserId = 1) {
        $member = self::findById($memberId);
        $plan = PlanService::findById($planId);

        if (!$member || !$plan) {
            throw new Exception("Invalid member or plan specified for renewal.");
        }

        Database::beginTransaction();

        try {
            $durationDays = (int)$plan['duration_days'];
            $endDate = date('Y-m-d', strtotime("+{$durationDays} days", strtotime($startDate)));
            $planPrice = (float)$plan['price'];
            $discount = (float)$discountAmount;

            // Insert new subscription
            $subQuery = "INSERT INTO member_subscriptions (member_id, plan_id, start_date, end_date, price, discount, status, notes, created_at)
                         VALUES (:member_id, :plan_id, :start_date, :end_date, :price, :discount, 'active', :notes, NOW())";

            $subscriptionId = Database::insert($subQuery, [
                ':member_id'  => $memberId,
                ':plan_id'    => $plan['id'],
                ':start_date' => $startDate,
                ':end_date'   => $endDate,
                ':price'      => $planPrice,
                ':discount'   => $discount,
                ':notes'      => 'Renewal subscription'
            ]);

            // Update member status to active
            Database::execute("UPDATE members SET status = 'active' WHERE id = :id", [':id' => $memberId]);

            // Record Payment
            if ($paidAmount > 0) {
                $receiptNo = 'REC-RNW-' . date('Y') . '-' . str_pad($memberId, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999);
                $payQuery = "INSERT INTO payments (member_id, subscription_id, receipt_number, amount, payment_method, status, notes, created_at)
                             VALUES (:member_id, :subscription_id, :receipt_number, :amount, :payment_method, 'completed', :notes, NOW())";
                
                Database::insert($payQuery, [
                    ':member_id'       => $memberId,
                    ':subscription_id' => $subscriptionId,
                    ':receipt_number'  => $receiptNo,
                    ':amount'          => (float)$paidAmount,
                    ':payment_method'  => $paymentMethod,
                    ':notes'           => 'Membership renewal - ' . $plan['name']
                ]);
            }

            Database::commit();

            AuditLogger::log('membership.renewed', 'member', $memberId, ['plan' => $plan['name'], 'end_date' => $endDate], $collectedByUserId, $member['branch_id']);
            return $subscriptionId;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
