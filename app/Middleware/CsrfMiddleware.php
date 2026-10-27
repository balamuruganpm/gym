<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\Csrf;
use App\Services\Session;

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(array $params = []): bool
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!Csrf::validate($token)) {
                http_response_code(419);
                Session::flash('error', 'Your session has expired. Please refresh and try again.');
                if (!empty($_SERVER['HTTP_REFERER'])) {
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                } else {
                    header('Location: ' . url('login'));
                }
                exit;
            }
        }
        return true;
    }
}
