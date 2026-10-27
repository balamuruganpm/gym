<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\Session;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(array $params = []): bool
    {
        Session::start();
        $user = Session::get('user');

        if ($user) {
            header('Location: ' . url('dashboard'));
            exit;
        }

        return true;
    }
}
