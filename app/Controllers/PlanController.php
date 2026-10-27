<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MembershipPlan;
use App\Services\TenantContext;
use App\Validators\Validator;

class PlanController extends Controller
{
    public function index(): string
    {
        $gymId = TenantContext::getGymId();
        $plans = MembershipPlan::getAll($gymId, false);

        return $this->view('plans.index', [
            'pageTitle' => 'Membership Plans — GymFlow',
            'plans' => $plans,
        ]);
    }

    public function create(): string
    {
        return $this->view('plans.create', [
            'pageTitle' => 'Create Membership Plan — GymFlow',
        ]);
    }

    public function store(): void
    {
        $gymId = TenantContext::getGymId();

        $validator = Validator::make($_POST, [
            'name' => 'required|min:2|max:100',
            'duration_value' => 'required',
            'duration_type' => 'required',
            'price' => 'required',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        try {
            MembershipPlan::create($gymId, [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? null,
                'duration_value' => (int) $_POST['duration_value'],
                'duration_type' => $_POST['duration_type'],
                'price' => (float) $_POST['price'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ]);

            $this->redirect('plans', ['success' => 'Membership plan created successfully!']);
        } catch (\Throwable $e) {
            $this->back(['error' => 'Failed to create plan: ' . $e->getMessage()]);
        }
    }

    public function edit(string $id): string
    {
        $gymId = TenantContext::getGymId();
        $plan = MembershipPlan::find((int) $id, $gymId);

        if (!$plan) {
            http_response_code(404);
            return view('errors.404', ['message' => 'Membership plan not found.']);
        }

        return $this->view('plans.edit', [
            'pageTitle' => "Edit Plan: {$plan['name']} — GymFlow",
            'plan' => $plan,
        ]);
    }

    public function update(string $id): void
    {
        $gymId = TenantContext::getGymId();
        $planId = (int) $id;

        $plan = MembershipPlan::find($planId, $gymId);
        if (!$plan) {
            http_response_code(404);
            exit('Plan not found.');
        }

        $validator = Validator::make($_POST, [
            'name' => 'required|min:2|max:100',
            'duration_value' => 'required',
            'duration_type' => 'required',
            'price' => 'required',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        try {
            MembershipPlan::update($planId, $gymId, [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? null,
                'duration_value' => (int) $_POST['duration_value'],
                'duration_type' => $_POST['duration_type'],
                'price' => (float) $_POST['price'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ]);

            $this->redirect('plans', ['success' => 'Plan details updated successfully!']);
        } catch (\Throwable $e) {
            $this->back(['error' => 'Failed to update plan: ' . $e->getMessage()]);
        }
    }

    public function toggle(string $id): void
    {
        $gymId = TenantContext::getGymId();
        MembershipPlan::toggleStatus((int) $id, $gymId);

        $this->redirect('plans', ['success' => 'Plan status updated.']);
    }
}
