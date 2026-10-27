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

        if (!$user || !$gym) {
            Session::flash('error', 'Please log in to continue.');
            header('Location: ' . url('login'));
            exit;
        }

        return true;
    }
}
