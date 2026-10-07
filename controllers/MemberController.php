<?php
/**
 * Member Controller
 * Handles mobile-first member portal: QR digital pass, attendance streak, transformation timeline, receipts, and profile.
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../modules/members/MemberService.php';
require_once __DIR__ . '/../modules/attendance/AttendanceService.php';
require_once __DIR__ . '/../modules/payments/PaymentService.php';
require_once __DIR__ . '/../modules/progress/ProgressService.php';

class MemberController {
    private $memberService;
    private $attendanceService;
    private $paymentService;
    private $progressService;

    public function __construct() {
        Auth::requireLogin();
        $this->memberService = new MemberService();
        $this->attendanceService = new AttendanceService();
        $this->paymentService = new PaymentService();
        $this->progressService = new ProgressService();
    }

    /**
     * Helper to get current logged-in member record
     */
    private function getCurrentMember() {
        $userId = Auth::id();
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM members WHERE user_id = ?");
        $stmt->execute([$userId]);
        $memberId = $stmt->fetchColumn();

        if (!$memberId) {
            die("Member record not associated with this user account.");
        }

        return $this->memberService->getMemberById($memberId);
    }

    /**
     * Member Home Dashboard
     */
    public function dashboard() {
        $member = $this->getCurrentMember();
        $stats = $this->attendanceService->getMemberAttendanceStats($member['id']);
        $recentWorkouts = $this->attendanceService->getMemberAttendanceHistory($member['id'], 5);
        $checkpoints = $this->progressService->getMemberProgress($member['id'], Auth::user());
        $latestCheckpoint = !empty($checkpoints) ? end($checkpoints) : null;

        View::render('member/dashboard', [
            'pageTitle' => 'Home - IronForge Portal',
            'activeTab' => 'home',
            'member' => $member,
            'stats' => $stats,
            'recentWorkouts' => $recentWorkouts,
            'latestCheckpoint' => $latestCheckpoint
        ], 'layouts/member_app');
    }

    /**
     * Digital Member ID & QR Code Pass
     */
    public function qr() {
        $member = $this->getCurrentMember();
        $stats = $this->attendanceService->getMemberAttendanceStats($member['id']);

        View::render('member/qr', [
            'pageTitle' => 'Digital Pass - IronForge',
            'activeTab' => 'qr',
            'member' => $member,
            'stats' => $stats
        ], 'layouts/member_app');
    }

    /**
     * Member Workout History & Streak Calendar
     */
    public function attendance() {
        $member = $this->getCurrentMember();
        $stats = $this->attendanceService->getMemberAttendanceStats($member['id']);
        $history = $this->attendanceService->getMemberAttendanceHistory($member['id'], 30);

        View::render('member/attendance', [
            'pageTitle' => 'Workouts & Streaks - IronForge',
            'activeTab' => 'attendance',
            'member' => $member,
            'stats' => $stats,
            'history' => $history
        ], 'layouts/member_app');
    }

    /**
     * One-Tap In-App Check-in Action
     */
    public function checkin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateToken();
            $member = $this->getCurrentMember();

            $result = $this->attendanceService->recordCheckin([
                'method' => 'member_app',
                'qr_token' => $member['qr_token'],
                'branch_id' => $member['branch_id']
            ]);

            if ($result['success']) {
                Session::flash('success', 'Workout logged successfully! Have an awesome training session! 🔥');
            } else {
                Session::flash('error', $result['error']);
            }

            header('Location: ' . BASE_URL . '/member/attendance');
            exit;
        }
    }

    /**
     * Mobile Camera Scanner for Universal Gym Wall Poster
     */
    public function scanGymQr() {
        $member = $this->getCurrentMember();
        View::render('member/scan_gym_qr', [
            'pageTitle' => 'Scan Gym QR - FitPulse',
            'activeTab' => 'scan_gym',
            'member'    => $member
        ], 'layouts/member_app');
    }

    /**
     * Member Personal Transformation Timeline
     */
    public function progress() {
        $member = $this->getCurrentMember();
        $checkpoints = $this->progressService->getMemberProgress($member['id'], Auth::user());
        $comparison = $this->progressService->getComparisonData($member['id'], Auth::user());

        View::render('member/progress', [
            'pageTitle' => 'My Transformation - FitPulse',
            'activeTab' => 'progress',
            'member' => $member,
            'checkpoints' => $checkpoints,
            'comparison' => $comparison
        ], 'layouts/member_app');
    }

    /**
     * Member Self-Upload Checkpoint Action
     */
    public function storeProgress() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateToken();
            $member = $this->getCurrentMember();
            $_POST['member_id'] = $member['id'];

            try {
                $this->progressService->recordCheckpoint($_POST, $_FILES, Auth::id());
                Session::flash('success', 'New progress checkpoint & photos saved!');
            } catch (Exception $e) {
                Session::flash('error', 'Failed to save checkpoint: ' . $e->getMessage());
            }

            header('Location: /member/progress');
            exit;
        }
    }

    /**
     * Member Payment Receipts & Invoices
     */
    public function payments() {
        $member = $this->getCurrentMember();
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            SELECT p.*, s.start_date, s.end_date, pl.name as plan_name
            FROM payments p
            LEFT JOIN member_subscriptions s ON p.subscription_id = s.id
            LEFT JOIN membership_plans pl ON s.plan_id = pl.id
            WHERE p.member_id = ?
            ORDER BY p.id DESC
        ");
        $stmt->execute([$member['id']]);
        $payments = $stmt->fetchAll();

        View::render('member/payments', [
            'pageTitle' => 'My Receipts - IronForge',
            'activeTab' => 'payments',
            'member' => $member,
            'payments' => $payments
        ], 'layouts/member_app');
    }

    /**
     * Member Profile Settings
     */
    public function profile() {
        $member = $this->getCurrentMember();

        View::render('member/profile', [
            'pageTitle' => 'My Profile - IronForge',
            'activeTab' => 'profile',
            'member' => $member
        ], 'layouts/member_app');
    }

    /**
     * Update Profile & Password Action
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateToken();
            $member = $this->getCurrentMember();
            $userId = Auth::id();
            $db = Database::getInstance();

            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $emergencyName = trim($_POST['emergency_contact_name'] ?? '');
            $emergencyPhone = trim($_POST['emergency_contact_phone'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($name) || empty($phone)) {
                Session::flash('error', 'Name and phone are required.');
                header('Location: /member/profile');
                exit;
            }

            // Update user table
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $hash, $userId]);
            } else {
                $stmt = $db->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $userId]);
            }

            // Update member table
            $stmt = $db->prepare("UPDATE members SET address = ?, emergency_contact_name = ?, emergency_contact_phone = ? WHERE id = ?");
            $stmt->execute([$address, $emergencyName, $emergencyPhone, $member['id']]);

            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['phone'] = $phone;

            Session::flash('success', 'Profile updated successfully!');
            header('Location: /member/profile');
            exit;
        }
    }
}
