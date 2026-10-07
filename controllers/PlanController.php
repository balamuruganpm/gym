<?php
/**
 * Plan Controller
 * Manages membership plans, pricing, durations, and admission fees.
 */

class PlanController {
    public function index() {
        RBAC::requireRole(['superadmin', 'owner', 'manager']);
        FeatureManager::requireFeature('membership_plans');

        $user = Auth::user();
        $branchId = ($user['role'] === 'superadmin') ? null : $user['branch_id'];

        $plans = PlanService::getAllPlans($branchId);

        View::render('admin/plans/index', [
            'pageTitle'  => 'Membership Plans - FitPulse',
            'activeMenu' => 'plans',
            'plans'      => $plans
        ], 'layouts/app');
    }

    public function create() {
        RBAC::requireRole(['superadmin', 'owner', 'manager']);
        FeatureManager::requireFeature('membership_plans');

        $branches = BranchService::getAllBranches(true);

        View::render('admin/plans/form', [
            'pageTitle'  => 'Create Membership Plan - FitPulse',
            'activeMenu' => 'plans',
            'plan'       => null,
            'branches'   => $branches,
            'isEdit'     => false
        ], 'layouts/app');
    }

    public function store() {
        RBAC::requireRole(['superadmin', 'owner', 'manager']);
        FeatureManager::requireFeature('membership_plans');
        CSRF::validateOrAbort();

        $validator = Validator::make($_POST, [
            'branch_id'     => 'required|numeric',
            'name'          => 'required|min:2|max:100',
            'duration_days' => 'required|numeric|min:1',
            'price'         => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            header('Location: ' . BASE_URL . '/admin/plans/create');
            exit;
        }

        try {
            PlanService::create($_POST);
            Session::flash('success', 'Membership plan created successfully!');
            header('Location: ' . BASE_URL . '/admin/plans');
        } catch (Exception $e) {
            Session::flash('error', 'Error creating plan: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/plans/create');
        }
        exit;
    }

    public function edit($id) {
        RBAC::requireRole(['superadmin', 'owner', 'manager']);
        FeatureManager::requireFeature('membership_plans');

        $plan = PlanService::findById($id);
        if (!$plan) {
            Session::flash('error', 'Plan not found.');
            header('Location: ' . BASE_URL . '/admin/plans');
            exit;
        }

        $branches = BranchService::getAllBranches(true);

        View::render('admin/plans/form', [
            'pageTitle'  => 'Edit Plan - ' . htmlspecialchars($plan['name']),
            'activeMenu' => 'plans',
            'plan'       => $plan,
            'branches'   => $branches,
            'isEdit'     => true
        ], 'layouts/app');
    }

    public function update($id) {
        RBAC::requireRole(['superadmin', 'owner', 'manager']);
        FeatureManager::requireFeature('membership_plans');
        CSRF::validateOrAbort();

        $validator = Validator::make($_POST, [
            'branch_id'     => 'required|numeric',
            'name'          => 'required|min:2|max:100',
            'duration_days' => 'required|numeric|min:1',
            'price'         => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            header('Location: ' . BASE_URL . '/admin/plans/' . (int)$id . '/edit');
            exit;
        }

        try {
            PlanService::update($id, $_POST);
            Session::flash('success', 'Plan updated successfully!');
            header('Location: ' . BASE_URL . '/admin/plans');
        } catch (Exception $e) {
            Session::flash('error', 'Error updating plan: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/plans/' . (int)$id . '/edit');
        }
        exit;
    }

    public function toggleStatus($id) {
        RBAC::requireRole(['superadmin', 'owner', 'manager']);
        FeatureManager::requireFeature('membership_plans');
        CSRF::validateOrAbort();

        $newStatus = PlanService::toggleStatus($id);
        $statusLabel = $newStatus ? 'activated' : 'deactivated';
        Session::flash('success', "Plan has been {$statusLabel}.");
        header('Location: ' . BASE_URL . '/admin/plans');
        exit;
    }
}
