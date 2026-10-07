<?php
/**
 * Staff Controller
 * Handles staff, branch manager, and trainer management.
 */

class StaffController {
    public function index() {
        RBAC::requireRole(['superadmin', 'owner', 'manager']);

        $user = Auth::user();
        $branchId = ($user['role'] === 'superadmin') ? null : $user['branch_id'];

        $staffList = StaffService::getAllStaff($branchId);

        View::render('admin/staff/index', [
            'pageTitle'  => 'Staff & Trainer Management - FitPulse',
            'activeMenu' => 'staff',
            'staffList'  => $staffList
        ], 'layouts/app');
    }

    public function create() {
        RBAC::requireRole(['superadmin', 'owner']);

        $branches = BranchService::getAllBranches(true);

        View::render('admin/staff/form', [
            'pageTitle'  => 'Add New Staff Member - FitPulse',
            'activeMenu' => 'staff',
            'staff'      => null,
            'branches'   => $branches,
            'isEdit'     => false
        ], 'layouts/app');
    }

    public function store() {
        RBAC::requireRole(['superadmin', 'owner']);
        CSRF::validateOrAbort();

        if (empty($_POST['name']) && !empty($_POST['full_name'])) {
            $_POST['name'] = $_POST['full_name'];
        }

        $validator = Validator::make($_POST, [
            'name'     => 'required|min:3|max:120',
            'email'    => 'required|email',
            'password' => 'required|min:6',
            'role'     => 'required|in:owner,manager,staff,trainer'
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            header('Location: ' . BASE_URL . '/admin/staff/create');
            exit;
        }

        try {
            StaffService::create($_POST);
            Session::flash('success', 'Staff account created successfully!');
            header('Location: ' . BASE_URL . '/admin/staff');
        } catch (Exception $e) {
            Session::flash('error', 'Error creating account: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/staff/create');
        }
        exit;
    }

    public function edit($id) {
        RBAC::requireRole(['superadmin', 'owner']);

        $staff = StaffService::findById($id);
        if (!$staff) {
            Session::flash('error', 'Staff member not found.');
            header('Location: ' . BASE_URL . '/admin/staff');
            exit;
        }

        $branches = BranchService::getAllBranches(true);

        View::render('admin/staff/form', [
            'pageTitle'  => 'Edit Staff - ' . htmlspecialchars($staff['name'] ?? $staff['full_name'] ?? 'Personnel'),
            'activeMenu' => 'staff',
            'staff'      => $staff,
            'branches'   => $branches,
            'isEdit'     => true
        ], 'layouts/app');
    }

    public function update($id) {
        RBAC::requireRole(['superadmin', 'owner']);
        CSRF::validateOrAbort();

        if (empty($_POST['name']) && !empty($_POST['full_name'])) {
            $_POST['name'] = $_POST['full_name'];
        }

        $validator = Validator::make($_POST, [
            'name'  => 'required|min:3|max:120',
            'email' => 'required|email',
            'role'  => 'required|in:owner,manager,staff,trainer'
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            header('Location: ' . BASE_URL . '/admin/staff/' . (int)$id . '/edit');
            exit;
        }

        try {
            StaffService::update($id, $_POST);
            Session::flash('success', 'Staff account updated successfully!');
            header('Location: ' . BASE_URL . '/admin/staff');
        } catch (Exception $e) {
            Session::flash('error', 'Error updating account: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/staff/' . (int)$id . '/edit');
        }
        exit;
    }

    public function toggleStatus($id) {
        RBAC::requireRole(['superadmin', 'owner']);
        CSRF::validateOrAbort();

        $newStatus = StaffService::toggleStatus($id);
        $statusLabel = $newStatus ? 'activated' : 'deactivated';
        Session::flash('success', "Staff account has been {$statusLabel}.");
        header('Location: ' . BASE_URL . '/admin/staff');
        exit;
    }
}
