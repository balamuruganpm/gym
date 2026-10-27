<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Session;

abstract class Controller
{
    /**
     * Render a view wrapped in layout.
     */
    protected function view(string $viewPath, array $data = [], string $layout = 'layouts.main'): string
    {
        $content = view($viewPath, $data);
        return view($layout, array_merge($data, ['content' => $content]));
    }

    /**
     * Return JSON response.
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Redirect to a specific URL path.
     */
    protected function redirect(string $path, array $flashMessages = []): void
    {
        foreach ($flashMessages as $key => $msg) {
            Session::flash($key, $msg);
        }
        header('Location: ' . url($path));
        exit;
    }

    /**
     * Redirect back with old input and errors.
     */
    protected function back(array $flashErrors = []): void
    {
        Session::setOldInput($_POST);
        foreach ($flashErrors as $key => $msg) {
            Session::flash($key, $msg);
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? url();
        header('Location: ' . $referer);
        exit;
    }
}
