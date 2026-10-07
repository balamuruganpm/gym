<?php
/**
 * Attendance Controller
 * Manages daily attendance register, Gym Universal QR Poster, fast desk check-in, and mobile QR scanning.
 */

class AttendanceController {
    public function index() {
        RBAC::requireRole(['superadmin', 'owner', 'manager', 'staff', 'trainer']);

        $user = Auth::user();
        $branchId = ($user['role'] === 'superadmin') ? null : $user['branch_id'];
        $date = isset($_GET['date']) ? trim($_GET['date']) : date('Y-m-d');

        $records = AttendanceService::getDailyAttendance($branchId, $date);
        $branches = BranchService::getAllBranches(true);

        View::render('admin/attendance/index', [
            'pageTitle'    => 'Daily Attendance Register - FitPulse',
            'activeMenu'   => 'attendance',
            'records'      => $records,
            'branches'     => $branches,
            'selectedDate' => $date
        ], 'layouts/app');
    }

    /**
     * Gym Universal QR Poster Generation (For Wall / Desk Display)
     */
    public function qrPoster() {
        RBAC::requireRole(['superadmin', 'owner', 'manager']);
        FeatureManager::requireFeature('attendance_gym_universal_qr');

        $user = Auth::user();
        $branchId = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : ($user['branch_id'] ?? 1);
        $branch = BranchService::findById($branchId) ?: BranchService::findById(1);

        $qrToken = BranchService::getBranchQrToken($branch['id']);
        $scanUrl = BASE_URL . '/checkin/gym-qr?branch_token=' . urlencode($qrToken);

        View::render('admin/attendance/qr_poster', [
            'pageTitle' => 'Gym Universal QR Poster - FitPulse',
            'branch'    => $branch,
            'qrToken'   => $qrToken,
            'scanUrl'   => $scanUrl
        ], 'layouts/app');
    }

    /**
     * Fast Front-Desk 1-Click & PIN Check-in Panel
     */
    public function quickDesk() {
        RBAC::requireRole(['superadmin', 'owner', 'manager', 'staff', 'trainer']);
        FeatureManager::requireFeature('attendance_quick_desk');

        $user = Auth::user();
        $branchId = ($user['role'] === 'superadmin') ? null : $user['branch_id'];
        
        $recentCheckins = (new AttendanceService())->getRecentCheckins($branchId, 15);
        $branches = BranchService::getAllBranches(true);

        View::render('admin/attendance/quick_desk', [
            'pageTitle'      => 'Fast Front-Desk Check-In - FitPulse',
            'activeMenu'     => 'quick_desk',
            'recentCheckins' => $recentCheckins,
            'branches'       => $branches
        ], 'layouts/app');
    }

    /**
     * Process Check-in (POST)
     */
    public function processCheckin() {
        Auth::requireLogin();
        CSRF::validateOrAbort();

        $tokenOrCode = isset($_POST['token_or_code']) ? trim($_POST['token_or_code']) : '';
        $method = isset($_POST['method']) ? trim($_POST['method']) : 'staff_manual';
        $pin = isset($_POST['pin']) ? trim($_POST['pin']) : null;

        $user = Auth::user();
        $branchId = $user ? $user['branch_id'] : 1;

        $result = AttendanceService::validateAndCheckIn($tokenOrCode, $method, $branchId, $pin, Auth::id());

        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $redirect = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : BASE_URL . '/admin/attendance';
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Universal Gym QR Check-in Endpoint (When Member scans Gym's Wall Poster)
     */
    public function scanGymQr() {
        FeatureManager::requireFeature('attendance_gym_universal_qr');

        $branchToken = isset($_GET['branch_token']) ? trim($_GET['branch_token']) : (isset($_POST['branch_token']) ? trim($_POST['branch_token']) : '');
        $branch = BranchService::findByQrToken($branchToken);

        if (!$branch) {
            View::render('member/gym_qr_result', [
                'success' => false,
                'message' => 'Invalid Gym QR Code. Please scan the official FitPulse Gym Wall Poster.',
                'branch'  => null
            ], 'layouts/auth');
            return;
        }

        // If member is not logged in, prompt quick sign in
        if (!Auth::check()) {
            Session::set('post_login_redirect', BASE_URL . '/checkin/gym-qr?branch_token=' . urlencode($branchToken));
            View::render('auth/quick_member_scan_login', [
                'branch'      => $branch,
                'branchToken' => $branchToken
            ], 'layouts/auth');
            return;
        }

        $user = Auth::user();
        // If logged in as admin/staff, show info or allow test
        if ($user['role'] !== 'member') {
            View::render('member/gym_qr_result', [
                'success' => true,
                'message' => 'Verified Gym QR for branch: ' . htmlspecialchars($branch['name']) . '. (Logged in as Staff/Admin: ' . htmlspecialchars($user['name']) . ')',
                'branch'  => $branch
            ], 'layouts/app');
            return;
        }

        // Fetch member profile
        $member = MemberService::findByUserId($user['id']);
        if (!$member) {
            View::render('member/gym_qr_result', [
                'success' => false,
                'message' => 'Member profile not found.',
                'branch'  => $branch
            ], 'layouts/auth');
            return;
        }

        // Record attendance with method 'gym_universal_qr'
        $result = AttendanceService::validateAndCheckIn($member['qr_token'], 'qr_scanner', $branch['id'], null, null);

        View::render('member/gym_qr_result', [
            'success'   => $result['success'],
            'message'   => $result['message'],
            'member'    => $member,
            'branch'    => $branch,
            'checkinRes'=> $result
        ], 'layouts/member_app');
    }

    /**
     * API for live member search at front desk
     */
    public function apiSearchMembers() {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (strlen($q) < 1) {
            echo json_encode([]);
            exit;
        }

        $user = Auth::user();
        $branchId = ($user['role'] === 'superadmin') ? null : $user['branch_id'];

        $query = "SELECT m.id, m.member_code, m.qr_token, m.pin_code, m.status, m.date_of_birth,
                         u.name, u.email, u.phone, b.name as branch_name,
                         p.name as plan_name, ms.end_date,
                         DATEDIFF(ms.end_date, CURRENT_DATE()) as days_remaining
                  FROM members m
                  INNER JOIN users u ON m.user_id = u.id
                  INNER JOIN branches b ON m.branch_id = b.id
                  LEFT JOIN member_subscriptions ms ON ms.id = (
                      SELECT id FROM member_subscriptions WHERE member_id = m.id ORDER BY id DESC LIMIT 1
                  )
                  LEFT JOIN membership_plans p ON ms.plan_id = p.id
                  WHERE (u.name LIKE :q OR u.phone LIKE :q OR m.member_code LIKE :q OR u.email LIKE :q)";
        
        $params = [':q' => "%{$q}%"];
        if ($branchId !== null) {
            $query .= " AND m.branch_id = :bid";
            $params[':bid'] = $branchId;
        }
        $query .= " ORDER BY u.name ASC LIMIT 10";

        $members = Database::fetchAll($query, $params);
        echo json_encode($members);
        exit;
    }

    /**
     * API Fast Checkin (JSON POST)
     */
    public function apiCheckin() {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $tokenOrCode = isset($input['token_or_code']) ? trim($input['token_or_code']) : '';
        $method = isset($input['method']) ? trim($input['method']) : 'staff_manual';
        $pin = isset($input['pin']) ? trim($input['pin']) : null;
        $branchId = isset($input['branch_id']) ? (int)$input['branch_id'] : (Auth::check() ? (Auth::user()['branch_id'] ?? 1) : 1);

        if (empty($tokenOrCode)) {
            echo json_encode(['success' => false, 'message' => 'Member Code, Phone or QR Token is required.']);
            exit;
        }

        $result = AttendanceService::validateAndCheckIn($tokenOrCode, $method, $branchId, $pin, Auth::id());
        echo json_encode($result);
        exit;
    }
}
