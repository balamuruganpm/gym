<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Database;
use App\Services\Session;
use App\Services\TenantContext;
use App\Services\TrainerService;
use App\Validators\Validator;

class TrainerAssignmentController extends Controller
{
    /**
     * Owner Trainer Management & Member Assignments Hub.
     */
    public function index(): string
    {
        $gymId = TenantContext::getGymId();

        // 1. All Trainers in Gym
        $trainers = Database::fetchAll("
            SELECT 
                u.id as user_id,
                u.name,
                u.email,
                u.phone,
                gu.is_active,
                (SELECT COUNT(*) FROM `trainer_member_assignments` tma 
                 WHERE tma.trainer_user_id = u.id AND tma.gym_id = :g1 AND tma.status = 'active') as active_trainees_count,
                (SELECT COUNT(*) FROM `training_sessions` ts 
                 WHERE ts.trainer_user_id = u.id AND ts.gym_id = :g2 AND ts.session_date = CURRENT_DATE() AND ts.status = 'scheduled') as today_sessions_count
            FROM `gym_users` gu
            INNER JOIN `users` u ON u.id = gu.user_id
            WHERE gu.gym_id = :gym_id AND gu.role = 'trainer'
            ORDER BY u.name ASC
        ", [':gym_id' => $gymId, ':g1' => $gymId, ':g2' => $gymId]);

        // 2. Active Member Assignments
        $assignments = Database::fetchAll("
            SELECT 
                tma.id,
                tma.trainer_user_id,
                tma.member_id,
                tma.assigned_at,
                tma.notes,
                u.name as trainer_name,
                m.member_number,
                m.first_name,
                m.last_name,
                m.phone as member_phone,
                assigner.name as assigned_by_name
            FROM `trainer_member_assignments` tma
            INNER JOIN `users` u ON u.id = tma.trainer_user_id
            INNER JOIN `members` m ON m.id = tma.member_id
            LEFT JOIN `users` assigner ON assigner.id = tma.assigned_by_user_id
            WHERE tma.gym_id = :gym_id AND tma.status = 'active'
            ORDER BY tma.id DESC
        ", [':gym_id' => $gymId]);

        // 3. Eligible Members for new assignment
        $members = Database::fetchAll("
            SELECT id, member_number, first_name, last_name, phone 
            FROM `members` 
            WHERE `gym_id` = :gym_id AND `is_archived` = 0 
            ORDER BY first_name ASC, last_name ASC
        ", [':gym_id' => $gymId]);

        return $this->view('trainers.index', [
            'pageTitle' => 'Trainer Hub & Member Assignments — GymFlow',
            'trainers' => $trainers,
            'assignments' => $assignments,
            'members' => $members,
        ]);
    }

    /**
     * Assign a member to a trainer.
     */
    public function assign(): void
    {
        $gymId = TenantContext::getGymId();
        $actorId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);

        $validator = Validator::make($_POST, [
            'trainer_user_id' => 'required',
            'member_id' => 'required',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        $trainerUserId = (int) $_POST['trainer_user_id'];
        $memberId = (int) $_POST['member_id'];
        $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;

        try {
            TrainerService::assignMember($gymId, $trainerUserId, $memberId, $actorId, $notes);
            $this->redirect('trainers', ['success' => 'Member assigned to trainer successfully!']);
        } catch (\Throwable $e) {
            $this->back(['error' => $e->getMessage()]);
        }
    }

    /**
     * Terminate an active member assignment.
     */
    public function unassign(string $id): void
    {
        $gymId = TenantContext::getGymId();
        $actorId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $assignmentId = (int) $id;

        try {
            TrainerService::unassignMember($gymId, $assignmentId, $actorId);
            $this->redirect('trainers', ['info' => 'Trainer assignment terminated successfully.']);
        } catch (\Throwable $e) {
            $this->back(['error' => $e->getMessage()]);
        }
    }
}
