<?php
/**
 * Branch Controller
 * Manages Multi-Branch creation, editing, status toggling and isolation.
 */

class BranchController {
    public function index() {
        RBAC::requireRole(['superadmin', 'owner']);
        FeatureManager::requireFeature('multi_branch');

        $branches = BranchService::getAllBranches();

        View::render('admin/branches/index', [
            'pageTitle'  => 'Branch Management - FitPulse',
            'activeMenu' => 'branches',
            'branches'   => $branches
        ], 'layouts/app');
    }

    public function create() {
        RBAC::requireRole(['superadmin', 'owner']);
        FeatureManager::requireFeature('multi_branch');

        View::render('admin/branches/form', [
            'pageTitle'  => 'Create New Branch - FitPulse',
            'activeMenu' => 'branches',
            'branch'     => null,
            'isEdit'     => false
        ], 'layouts/app');
    }

    public function store() {
        RBAC::requireRole(['superadmin', 'owner']);
        FeatureManager::requireFeature('multi_branch');
        CSRF::validateOrAbort();

        $validator = Validator::make($_POST, [
            'code' => 'required|min:2|max:30',
            'name' => 'required|min:3|max:150'
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            header('Location: ' . BASE_URL . '/admin/branches/create');
            exit;
        }

        try {
            BranchService::create($_POST);
            Session::flash('success', 'Branch created successfully!');
            header('Location: ' . BASE_URL . '/admin/branches');
        } catch (Exception $e) {
            Session::flash('error', 'Error creating branch: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/branches/create');
        }
        exit;
    }

    public function edit($id) {
        RBAC::requireRole(['superadmin', 'owner']);
        FeatureManager::requireFeature('multi_branch');

        $branch = BranchService::findById($id);
        if (!$branch) {
            Session::flash('error', 'Branch not found.');
            header('Location: ' . BASE_URL . '/admin/branches');
            exit;
        }

        View::render('admin/branches/form', [
            'pageTitle'  => 'Edit Branch - ' . htmlspecialchars($branch['name']),
            'activeMenu' => 'branches',
            'branch'     => $branch,
            'isEdit'     => true
        ], 'layouts/app');
    }

    public function update($id) {
        RBAC::requireRole(['superadmin', 'owner']);
        FeatureManager::requireFeature('multi_branch');
        CSRF::validateOrAbort();

        $validator = Validator::make($_POST, [
            'code' => 'required|min:2|max:30',
            'name' => 'required|min:3|max:150'
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            header('Location: ' . BASE_URL . '/admin/branches/' . (int)$id . '/edit');
            exit;
        }

        try {
            BranchService::update($id, $_POST);
            Session::flash('success', 'Branch updated successfully!');
            header('Location: ' . BASE_URL . '/admin/branches');
        } catch (Exception $e) {
            Session::flash('error', 'Error updating branch: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/branches/' . (int)$id . '/edit');
        }
        exit;
    }

    public function toggleStatus($id) {
        RBAC::requireRole(['superadmin', 'owner']);
        FeatureManager::requireFeature('multi_branch');
        CSRF::validateOrAbort();

        $newStatus = BranchService::toggleStatus($id);
        $statusLabel = $newStatus ? 'activated' : 'deactivated';
        Session::flash('success', "Branch has been {$statusLabel}.");
        header('Location: ' . BASE_URL . '/admin/branches');
        exit;
    }
}
