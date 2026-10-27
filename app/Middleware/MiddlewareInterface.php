<?php

declare(strict_types=1);

namespace App\Middleware;

interface MiddlewareInterface
{
    /**
     * Handle the incoming request.
     * Return true to continue pipeline, or handle redirection / error response.
     */
    public function handle(array $params = []): bool;
}
