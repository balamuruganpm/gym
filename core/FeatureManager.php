<?php
/**
 * Feature Manager Engine - Coder Control Center
 * Handles dynamic activation/deactivation of modules globally or on a per-branch basis.
 */

class FeatureManager {
    private static $cachedFeatures = null;

    public static function getAll($branchId = null) {
        return self::getAllFeatures($branchId);
    }

    /**
     * Load features from database with fallback to config file
     */
    public static function getAllFeatures($branchId = null) {
        if (self::$cachedFeatures !== null && $branchId === null) {
            return self::$cachedFeatures;
        }

        $defaultFeatures = require BASE_PATH . '/config/features.php';

        // Check if database table exists
        if (Database::tableExists('system_features')) {
            try {
                $rows = Database::fetchAll("SELECT * FROM system_features");
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $key = $row['feature_key'];
                        $visibleRoles = json_decode($row['visible_roles_json'], true) ?: [];
                        $overrides = json_decode($row['branch_overrides_json'], true) ?: [];

                        $isEnabled = (bool) $row['is_enabled'];
                        if ($branchId !== null && isset($overrides[$branchId])) {
                            $isEnabled = (bool) $overrides[$branchId];
                        }

                        $defaultFeatures[$key] = [
                            'name' => $row['name'],
                            'category' => $row['category'],
                            'enabled' => $isEnabled,
                            'visible_roles' => $visibleRoles,
                            'branch_overrides' => $overrides
                        ];
                    }
                }
            } catch (Exception $e) {
                // Fallback to static defaults
            }
        }

        if ($branchId === null) {
            self::$cachedFeatures = $defaultFeatures;
        }

        return $defaultFeatures;
    }

    /**
     * Check if a feature is enabled
     */
    public static function isEnabled($featureKey, $branchId = null) {
        $features = self::getAllFeatures($branchId);
        if (!isset($features[$featureKey])) {
            return false;
        }
        return (bool) $features[$featureKey]['enabled'];
    }

    /**
     * Check if current user can see and access this feature
     */
    public static function isVisibleToUser($featureKey, $userRole = null) {
        if ($userRole === null) {
            $user = Auth::user();
            $userRole = $user ? $user['role'] : null;
        }

        if ($userRole === 'superadmin') {
            return true;
        }

        $features = self::getAllFeatures();
        if (!isset($features[$featureKey])) {
            return false;
        }

        $feature = $features[$featureKey];
        if (!$feature['enabled']) {
            return false;
        }

        if (empty($feature['visible_roles'])) {
            return true;
        }

        return in_array($userRole, $feature['visible_roles'], true);
    }

    /**
     * Require a feature to be enabled or abort
     */
    public static function requireFeature($featureKey, $branchId = null) {
        if (!self::isEnabled($featureKey, $branchId)) {
            http_response_code(404);
            View::render('errors/404', ['message' => "Module '{$featureKey}' is currently disabled in Coder Control Center."], 'layouts/app');
            exit;
        }
    }

    /**
     * Toggle or update feature setting
     */
    public static function updateFeature($featureKey, $isEnabled, $visibleRoles = null, $branchOverrides = null) {
        $params = [
            ':key' => $featureKey,
            ':enabled' => $isEnabled ? 1 : 0
        ];

        $updateSql = "UPDATE system_features SET is_enabled = :enabled";
        if ($visibleRoles !== null) {
            $updateSql .= ", visible_roles_json = :roles";
            $params[':roles'] = json_encode($visibleRoles);
        }
        if ($branchOverrides !== null) {
            $updateSql .= ", branch_overrides_json = :overrides";
            $params[':overrides'] = json_encode($branchOverrides);
        }
        $updateSql .= " WHERE feature_key = :key";

        self::$cachedFeatures = null;
        return Database::execute($updateSql, $params);
    }
}
