<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\Session;

class RoleMiddleware implements MiddlewareInterface
{
    private string $requiredRole;

    public function __construct(string $role = 'owner')
    {
        $this->requiredRole = $role;
    }

    public function handle(array $params = []): bool
    {
        Session::start();
        $userRole = Session::get('user_role');

        if ($userRole !== $this->requiredRole) {
            http_response_code(403);
            Session::flash('error', 'Unauthorized access: this action requires ' . ucfirst($this->requiredRole) . ' privileges.');
            header('Location: ' . url('dashboard'));
            exit;
        }

        return true;
    }
}
