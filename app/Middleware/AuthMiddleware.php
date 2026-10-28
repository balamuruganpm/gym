<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\Session;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(array $params = []): bool
    {
        Session::start();
        $user = Session::get('user');
        $gym = Session::get('gym');

        if (!$user) {
            Session::flash('error', 'Please log in to continue.');
            header('Location: ' . url('login'));
            exit;
        }

        $isSupreme = (bool) (Session::get('is_supreme_leader') ?? ($user['is_supreme_leader'] ?? false));
        if (!$isSupreme && !$gym) {
            Session::flash('error', 'No active gym session found. Please log in again.');
            header('Location: ' . url('login'));
            exit;
        }

        return true;
    }
}
