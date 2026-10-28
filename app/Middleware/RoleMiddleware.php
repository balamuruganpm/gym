<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\Session;

class RoleMiddleware implements MiddlewareInterface
{
    private array $allowedRoles;

    /**
     * @param string|array $roles Single role string or array of allowed roles
     */
    public function __construct(string|array $roles = 'owner')
    {
        $this->allowedRoles = is_array($roles) ? $roles : [$roles];
    }

    public function isAuthorized(): bool
    {
        Session::start();
        $user = Session::get('user');
        $userRole = Session::get('user_role') ?? Session::get('user.role');
        $isSupreme = (bool) (Session::get('is_supreme_leader') ?? ($user['is_supreme_leader'] ?? false));

        // If not logged in at all
        if (!$user) {
            return false;
        }

        // SupremeLeader in system routes or in audited support mode
        if ($isSupreme) {
            if (in_array('supreme_leader', $this->allowedRoles, true) || Session::get('support_mode') === true) {
                return true;
            }
        }

        // Check if user's role is in allowed roles
        if ($userRole && in_array($userRole, $this->allowedRoles, true)) {
            return true;
        }

        return false;
    }

    public function handle(array $params = []): bool
    {
        if ($this->isAuthorized()) {
            return true;
        }

        Session::start();
        $user = Session::get('user');
        $userRole = Session::get('user_role') ?? Session::get('user.role');
        $isSupreme = (bool) (Session::get('is_supreme_leader') ?? ($user['is_supreme_leader'] ?? false));

        // If not logged in at all
        if (!$user) {
            Session::flash('error', 'Please log in to continue.');
            header('Location: ' . url('login'));
            exit;
        }

        // Unauthorized access - 403
        http_response_code(403);
        $roleNames = implode(' or ', array_map('ucfirst', $this->allowedRoles));
        Session::flash('error', "Unauthorized access: this section requires {$roleNames} privileges.");

        // Smart role redirect
        if ($isSupreme) {
            header('Location: ' . url('admin/system'));
        } elseif ($userRole === 'member') {
            header('Location: ' . url('portal/dashboard'));
        } elseif ($userRole === 'trainer') {
            header('Location: ' . url('trainer/dashboard'));
        } else {
            header('Location: ' . url('dashboard'));
        }
        exit;
    }
}
