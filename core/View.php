<?php
/**
 * View Core Engine
 * Handles template rendering, layout nesting, flash alerts, and asset path resolution.
 */

class View {
    /**
     * Render a view within a layout
     * @param string $view (e.g. 'auth/login', 'admin/dashboard')
     * @param array $data
     * @param string|null $layout (e.g. 'layouts/app', 'layouts/auth', 'layouts/member')
     */
    public static function render($view, $data = [], $layout = 'layouts/app') {
        $viewFile = BASE_PATH . '/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die("View template not found: " . htmlspecialchars($viewFile, ENT_QUOTES, 'UTF-8'));
        }

        // Extract variables to local scope
        extract($data);

        // Capture view content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // If layout specified, render inside layout
        if ($layout) {
            $layoutFile = BASE_PATH . '/views/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    /**
     * Include a partial sub-view
     */
    public static function partial($partial, $data = []) {
        $partialFile = BASE_PATH . '/views/partials/' . $partial . '.php';
        if (file_exists($partialFile)) {
            extract($data);
            include $partialFile;
        }
    }

    /**
     * Helper to output formatted currency
     */
    public static function currency($amount) {
        return APP_CURRENCY_SYMBOL . number_format((float)$amount, 2);
    }

    /**
     * Helper to output formatted date
     */
    public static function date($dateStr, $format = 'd M Y') {
        if (!$dateStr) return '-';
        return date($format, strtotime($dateStr));
    }

    /**
     * Helper to output formatted time
     */
    public static function time($timeStr, $format = 'h:i A') {
        if (!$timeStr) return '-';
        return date($format, strtotime($timeStr));
    }
}
