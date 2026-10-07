<?php
/**
 * Multi-Channel Notification Service
 * Dispatches notifications across Email, WhatsApp, and In-App channels.
 */

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/AuditLogger.php';

class NotificationService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Compile template with placeholders
     */
    public function compileTemplate($template, $data) {
        foreach ($data as $key => $val) {
            $template = str_replace('{' . $key . '}', $val, $template);
        }
        return $template;
    }

    /**
     * Send Email notification (with PHP mail or simulated SMTP)
     */
    public function sendEmail($to, $subject, $body) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: IronForge Gym <no-reply@ironforgegym.com>\r\n";

        // In production mail($to, $subject, $body, $headers)
        // Log notification to audit
        AuditLogger::log('email_dispatched', 'notifications', null, [
            'to' => $to,
            'subject' => $subject,
            'status' => 'sent'
        ]);

        return true;
    }

    /**
     * Generate WhatsApp Direct Click URL
     */
    public function generateWhatsAppUrl($phone, $message) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone; // Default India country code
        }
        return "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
    }

    /**
     * Dispatch notification based on channel
     */
    public function dispatch($channel, $recipient, $subject, $message, $meta = []) {
        if ($channel === 'email' && !empty($recipient['email'])) {
            $this->sendEmail($recipient['email'], $subject, $message);
        }

        AuditLogger::log('notification_sent', 'notifications', null, [
            'channel' => $channel,
            'recipient' => $recipient['name'] ?? 'Unknown',
            'subject' => $subject,
            'meta' => $meta
        ]);

        return true;
    }
}
