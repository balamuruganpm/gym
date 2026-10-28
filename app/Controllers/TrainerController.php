<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Database;
use App\Services\Session;
use App\Services\TrainerService;
use App\Validators\Validator;

class TrainerController extends Controller
{
    /**
     * Trainer Personal Dashboard.
     */
    public function dashboard(): string
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);
        $user = Session::get('user') ?? [];
        $gym = Session::get('gym') ?? ['name' => 'GymFlow'];

        $assignedMembers = TrainerService::getAssignedMembers($gymId, $userId);
        $assignedCount = count($assignedMembers);

        // Today's sessions
        $todaySessions = Database::fetchAll("
            SELECT ts.*, m.first_name, m.last_name, m.phone, m.member_number
            FROM `training_sessions` ts
            INNER JOIN `members` m ON m.id = ts.member_id
            WHERE ts.trainer_user_id = :trainer_id AND ts.gym_id = :gym_id 
              AND ts.session_date = CURRENT_DATE()
            ORDER BY ts.start_time ASC
        ", [':trainer_id' => $userId, ':gym_id' => $gymId]);

        $todaySessionsCount = count(array_filter($todaySessions, fn($s) => $s['status'] === 'scheduled'));

        // Upcoming scheduled sessions (next 5)
        $upcomingSessions = Database::fetchAll("
            SELECT ts.*, m.first_name, m.last_name, m.phone, m.member_number
            FROM `training_sessions` ts
            INNER JOIN `members` m ON m.id = ts.member_id
            WHERE ts.trainer_user_id = :trainer_id AND ts.gym_id = :gym_id 
              AND ts.session_date >= CURRENT_DATE() AND ts.status = 'scheduled'
            ORDER BY ts.session_date ASC, ts.start_time ASC
            LIMIT 5
        ", [':trainer_id' => $userId, ':gym_id' => $gymId]);

        // Recent progress records
        $recentProgress = Database::fetchAll("
            SELECT tpr.*, m.first_name, m.last_name, m.member_number
            FROM `training_progress_records` tpr
            INNER JOIN `members` m ON m.id = tpr.member_id
            WHERE tpr.trainer_user_id = :trainer_id AND tpr.gym_id = :gym_id
            ORDER BY tpr.record_date DESC, tpr.id DESC
            LIMIT 4
        ", [':trainer_id' => $userId, ':gym_id' => $gymId]);

        return $this->view('trainer.dashboard', [
            'pageTitle' => 'Trainer Workspace — GymFlow',
            'user' => $user,
            'gym' => $gym,
            'assignedCount' => $assignedCount,
            'todaySessionsCount' => $todaySessionsCount,
            'activeMembers' => $assignedMembers,
            'todaySessions' => $todaySessions,
            'upcomingSessions' => $upcomingSessions,
            'recentProgress' => $recentProgress,
        ]);
    }

    /**
     * View all assigned members.
     */
    public function members(): string
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);

        $assignedMembers = TrainerService::getAssignedMembers($gymId, $userId);

        return $this->view('trainer.members', [
            'pageTitle' => 'My Assigned Trainees — GymFlow',
            'members' => $assignedMembers,
        ]);
    }

    /**
     * View single assigned member profile & history.
     */
    public function memberDetails(string $id): string
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);
        $memberId = (int) $id;

        // Strict isolation check: Trainer can only view assigned members!
        if (!TrainerService::isMemberAssigned($gymId, $userId, $memberId)) {
            $this->redirect('trainer/members', ['error' => 'Access Denied: You can only view members assigned directly to you.']);
        }

        $member = Database::fetchOne("
            SELECT * FROM `members` WHERE `id` = :id AND `gym_id` = :gym_id LIMIT 1
        ", [':id' => $memberId, ':gym_id' => $gymId]);

        $activeMembership = Database::fetchOne("
            SELECT * FROM `memberships` WHERE `member_id` = :m_id AND `gym_id` = :gym_id AND `status` = 'active' ORDER BY id DESC LIMIT 1
        ", [':m_id' => $memberId, ':gym_id' => $gymId]);

        $sessions = Database::fetchAll("
            SELECT * FROM `training_sessions` 
            WHERE `member_id` = :m_id AND `trainer_user_id` = :trainer_id AND `gym_id` = :gym_id
            ORDER BY session_date DESC, start_time DESC
        ", [':m_id' => $memberId, ':trainer_id' => $userId, ':gym_id' => $gymId]);

        $progressHistory = TrainerService::getMemberProgressHistory($gymId, $memberId, $userId);

        return $this->view('trainer.member_details', [
            'pageTitle' => ($member['first_name'] . ' ' . $member['last_name']) . ' — Trainee Profile',
            'member' => $member,
            'activeMembership' => $activeMembership,
            'sessions' => $sessions,
            'progressHistory' => $progressHistory,
        ]);
    }

    /**
     * View & Manage Trainer Availability.
     */
    public function availability(): string
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);

        $availabilities = TrainerService::getTrainerAvailability($gymId, $userId);

        return $this->view('trainer.availability', [
            'pageTitle' => 'My Availability Schedule — GymFlow',
            'availabilities' => $availabilities,
        ]);
    }

    /**
     * Save availability slot.
     */
    public function storeAvailability(): void
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);

        $validator = Validator::make($_POST, [
            'type' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        try {
            TrainerService::setAvailability($gymId, $userId, $_POST);
            $this->redirect('trainer/availability', ['success' => 'Availability slot saved successfully!']);
        } catch (\Throwable $e) {
            $this->back(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove an availability slot.
     */
    public function deleteAvailability(string $id): void
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);
        $availId = (int) $id;

        TrainerService::deleteAvailability($gymId, $userId, $availId);
        $this->redirect('trainer/availability', ['info' => 'Availability slot removed.']);
    }

    /**
     * Sessions Calendar & Agenda.
     */
    public function sessions(): string
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);

        $sessions = Database::fetchAll("
            SELECT ts.*, m.first_name, m.last_name, m.member_number, m.phone
            FROM `training_sessions` ts
            INNER JOIN `members` m ON m.id = ts.member_id
            WHERE ts.trainer_user_id = :trainer_id AND ts.gym_id = :gym_id
            ORDER BY ts.session_date DESC, ts.start_time DESC
        ", [':trainer_id' => $userId, ':gym_id' => $gymId]);

        $assignedMembers = TrainerService::getAssignedMembers($gymId, $userId);

        return $this->view('trainer.sessions', [
            'pageTitle' => 'Training Sessions & Calendar — GymFlow',
            'sessions' => $sessions,
            'assignedMembers' => $assignedMembers,
        ]);
    }

    /**
     * Schedule a new training session.
     */
    public function storeSession(): void
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);

        $validator = Validator::make($_POST, [
            'member_id' => 'required',
            'session_date' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        $memberId = (int) $_POST['member_id'];
        $sessionDate = trim($_POST['session_date']);
        $startTime = trim($_POST['start_time']);
        $endTime = trim($_POST['end_time']);
        $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;

        try {
            TrainerService::scheduleSession(
                $gymId,
                $userId,
                $memberId,
                $sessionDate,
                $startTime,
                $endTime,
                $userId,
                $notes
            );

            $this->redirect('trainer/sessions', ['success' => 'Training session scheduled successfully!']);
        } catch (\Throwable $e) {
            $this->back(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update session status (completed, cancelled, no_show).
     */
    public function updateSessionStatus(string $id): void
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);
        $sessionId = (int) $id;
        $status = trim((string) ($_POST['status'] ?? 'completed'));
        $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;

        try {
            TrainerService::updateSessionStatus($gymId, $sessionId, $userId, $status, $notes);
            $this->redirect('trainer/sessions', ['success' => "Session marked as {$status}!"]);
        } catch (\Throwable $e) {
            $this->back(['error' => $e->getMessage()]);
        }
    }

    /**
     * Progress tracking list.
     */
    public function progress(): string
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);

        $progressRecords = Database::fetchAll("
            SELECT tpr.*, m.first_name, m.last_name, m.member_number
            FROM `training_progress_records` tpr
            INNER JOIN `members` m ON m.id = tpr.member_id
            WHERE tpr.trainer_user_id = :trainer_id AND tpr.gym_id = :gym_id
            ORDER BY tpr.record_date DESC, tpr.id DESC
        ", [':trainer_id' => $userId, ':gym_id' => $gymId]);

        $assignedMembers = TrainerService::getAssignedMembers($gymId, $userId);

        return $this->view('trainer.progress', [
            'pageTitle' => 'Workout & Training Progress — GymFlow',
            'records' => $progressRecords,
            'assignedMembers' => $assignedMembers,
        ]);
    }

    /**
     * Record new workout progress.
     */
    public function storeProgress(): void
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);

        $validator = Validator::make($_POST, [
            'member_id' => 'required',
            'workout_summary' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        $memberId = (int) $_POST['member_id'];

        try {
            TrainerService::recordProgress($gymId, $userId, $memberId, $_POST);
            $this->redirect('trainer/progress', ['success' => 'Workout progress recorded successfully!']);
        } catch (\Throwable $e) {
            $this->back(['error' => $e->getMessage()]);
        }
    }
}
