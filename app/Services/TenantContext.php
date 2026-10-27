<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class TenantContext
{
    /**
     * Get the authenticated gym ID from the server session.
     */
    public static function getGymId(): int
    {
        Session::start();
        $gymId = Session::get('gym_id');

        if (!$gymId || !is_numeric($gymId)) {
            throw new RuntimeException("Tenant session expired or unauthorized access.");
        }

        return (int) $gymId;
    }

    /**
     * Verify that a given database record belongs to the active tenant gym.
     */
    public static function verifyOwnership(int $recordGymId): void
    {
        $currentGymId = self::getGymId();
        if ($recordGymId !== $currentGymId) {
            http_response_code(403);
            throw new RuntimeException("Access Denied: Record does not belong to active gym tenant.");
        }
    }
}
