<?php
/**
 * Default System Features Definition
 * Controls all modules across the platform. Can be updated dynamically via Coder Control Center.
 */

return [
    'auth_username_password' => [
        'name' => 'Username & Password Login',
        'category' => 'Authentication',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff', 'trainer', 'member']
    ],
    'attendance_gym_universal_qr' => [
        'name' => 'Gym Universal QR Poster (Scan & Check-In)',
        'category' => 'Attendance',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff', 'member']
    ],
    'attendance_member_qr' => [
        'name' => 'Member Digital QR Pass',
        'category' => 'Attendance',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff', 'member']
    ],
    'attendance_quick_desk' => [
        'name' => 'Front-Desk Fast 1-Click Check-In',
        'category' => 'Attendance',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff', 'trainer']
    ],
    'attendance_pin' => [
        'name' => 'PIN / Phone Number Check-In',
        'category' => 'Attendance',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff']
    ],
    'attendance_rfid' => [
        'name' => 'RFID / NFC Card Tap Check-In',
        'category' => 'Attendance',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff']
    ],
    'attendance_member_onetap' => [
        'name' => 'Member Portal 1-Tap Check-In',
        'category' => 'Attendance',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff', 'member']
    ],
    'member_management' => [
        'name' => 'Member Directory & Profiles',
        'category' => 'Core',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff', 'trainer']
    ],
    'membership_plans' => [
        'name' => 'Membership Plans & Expiry Engine',
        'category' => 'Core',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff']
    ],
    'membership_freeze' => [
        'name' => 'Membership Freeze Engine',
        'category' => 'Core',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff', 'member']
    ],
    'payments_tracking' => [
        'name' => 'Payment Ledger & Invoicing POS',
        'category' => 'Finance',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff', 'member']
    ],
    'progress_photos' => [
        'name' => 'Transformation Photos & Privacy Levels',
        'category' => 'Fitness',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff', 'trainer', 'member']
    ],
    'body_measurements' => [
        'name' => 'Body Measurement & Checkpoint Tracking',
        'category' => 'Fitness',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner', 'manager', 'staff', 'trainer', 'member']
    ],
    'multi_branch' => [
        'name' => 'Multi-Branch Management',
        'category' => 'Facility',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner']
    ],
    'automation_builder' => [
        'name' => 'Automation & Expiry Cron Workflows',
        'category' => 'Facility',
        'enabled' => true,
        'visible_roles' => ['superadmin', 'owner']
    ],
    'system_health' => [
        'name' => 'System Health & Diagnostics Telemetry',
        'category' => 'Facility',
        'enabled' => true,
        'visible_roles' => ['superadmin']
    ]
];
