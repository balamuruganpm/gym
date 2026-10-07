<?php
/**
 * Member Admin Controller
 * Handles staff/admin member operations, registrations, profile viewing, freeze, and renewals.
 */

class MemberAdminController {
    public function index() {
        RBAC::requireRole(['superadmin', 'owner', 'manager', 'staff']);
        FeatureManager::requireFeature('member_management');

        $user = Auth::user();
        $branchId = ($user['role'] === 'superadmin') ? null : $user['branch_id'];
        
        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;

        $members = MemberService::getAllMembers($branchId, $status, $search);

        View::render('admin/members/index', [
            'pageTitle'      => 'Member Directory - FitPulse',
            'activeMenu'     => 'members',
            'members'        => $members,
            'selectedStatus' => $status,
            'searchQuery'    => $search
        ], 'layouts/app');
    }

    public function create() {
        RBAC::requireRole(['superadmin', 'owner', 'manager', 'staff']);
        FeatureManager::requireFeature('member_management');

        $user = Auth::user();
        $branches = BranchService::getAllBranches(true);
        $branchId = ($user['role'] === 'superadmin') ? ($branches[0]['id'] ?? 1) : $user['branch_id'];
        $plans = PlanService::getAllPlans($branchId, true);

        View::render('admin/members/form', [
            'pageTitle'  => 'Register New Member - FitPulse',
            'activeMenu' => 'members',
            'branches'   => $branches,
            'plans'      => $plans,
            'user'       => $user
        ], 'layouts/app');
    }

    public function store() {
        RBAC::requireRole(['superadmin', 'owner', 'manager', 'staff']);
        FeatureManager::requireFeature('member_management');
        CSRF::validateOrAbort();

        $validator = Validator::make($_POST, [
            'full_name' => 'required|min:3|max:120',
            'email'     => 'required|email',
            'branch_id' => 'required|numeric',
            'plan_id'   => 'required|numeric'
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            header('Location: ' . BASE_URL . '/admin/members/create');
            exit;
        }

        try {
            $memberId = MemberService::register($_POST, Auth::id());
            Session::flash('success', 'Member registered successfully!');
            header('Location: ' . BASE_URL . '/admin/members/' . $memberId);
        } catch (Exception $e) {
            Session::flash('error', 'Error during registration: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/members/create');
        }
        exit;
    }

    public function show($id) {
        RBAC::requireRole(['superadmin', 'owner', 'manager', 'staff', 'trainer']);
        FeatureManager::requireFeature('member_management');

        $member = MemberService::findById($id);
        if (!$member) {
            Session::flash('error', 'Member not found.');
            header('Location: ' . BASE_URL . '/admin/members');
            exit;
        }

        // Fetch recent payments for this member
        $payments = Database::fetchAll(
            "SELECT * FROM payments WHERE member_id = :mid ORDER BY payment_date DESC, id DESC LIMIT 10",
            [':mid' => (int)$id]
        );

        // Fetch recent attendance for this member
        $attendance = Database::fetchAll(
            "SELECT * FROM attendance WHERE member_id = :mid ORDER BY attendance_date DESC, check_in_time DESC LIMIT 15",
            [':mid' => (int)$id]
        );

        $plans = PlanService::getAllPlans($member['branch_id'], true);

        View::render('admin/members/view', [
            'pageTitle'  => 'Member Profile - ' . htmlspecialchars($member['full_name']),
            'activeMenu' => 'members',
            'member'     => $member,
            'payments'   => $payments,
            'attendance' => $attendance,
            'plans'      => $plans
        ], 'layouts/app');
    }

    public function freeze($id) {
        RBAC::requireRole(['superadmin', 'owner', 'manager', 'staff']);
        FeatureManager::requireFeature('membership_freeze');
        CSRF::validateOrAbort();

        $freezeStart = isset($_POST['freeze_start']) ? $_POST['freeze_start'] : '';
        $freezeEnd = isset($_POST['freeze_end']) ? $_POST['freeze_end'] : '';
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : 'Requested by member';

        try {
            MemberService::freezeMembership($id, $freezeStart, $freezeEnd, $reason, Auth::id());
            Session::flash('success', 'Membership has been frozen and expiry date adjusted forward.');
        } catch (Exception $e) {
            Session::flash('error', 'Freeze Error: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/admin/members/' . (int)$id);
        exit;
    }

    public function renew($id) {
        RBAC::requireRole(['superadmin', 'owner', 'manager', 'staff']);
        FeatureManager::requireFeature('membership_plans');
        CSRF::validateOrAbort();

        $planId = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;
        $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
        $paidAmount = isset($_POST['paid_amount']) ? (float)$_POST['paid_amount'] : 0.00;
        $discountAmount = isset($_POST['discount_amount']) ? (float)$_POST['discount_amount'] : 0.00;
        $paymentMethod = !empty($_POST['payment_method']) ? $_POST['payment_method'] : 'cash';

        try {
            MemberService::renewMembership($id, $planId, $startDate, $paidAmount, $paymentMethod, $discountAmount, Auth::id());
            Session::flash('success', 'Membership renewed successfully!');
        } catch (Exception $e) {
            Session::flash('error', 'Renewal Error: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/admin/members/' . (int)$id);
        exit;
    }
}
