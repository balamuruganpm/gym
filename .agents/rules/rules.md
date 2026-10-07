# Project Stack & Development Rules

## 1. Technical Stack
- **Backend**: PHP (Modular Object-Oriented Architecture, compatible across PHP 5.6+ / 7.x / 8.x)
- **Database**: MySQL / MariaDB (InnoDB, UTF8mb4, PDO with prepared statements, foreign keys, index optimization)
- **Frontend**: HTML5, Vanilla JavaScript (ES6+ with modern helpers), Bootstrap 5 & Custom CSS Design System
- **Icons & UI**: Bootstrap Icons / Modern SVG Icons, Glassmorphism, Clean Data Cards, Chart.js for trends
- **Environments**: Laragon Local (`http://gym.test` or `localhost/Projects All/per/gym`) & Production Cloud

---

## 2. Directory & Architectural Conventions
```text
/gym/
├── .agents/                 # AI agent rules and workspace configurations
│   └── rules/rules.md
├── assets/                  # Static assets (css, js, images, icons, fonts)
│   ├── css/
│   ├── js/
│   └── img/
├── config/                  # Configuration files
│   ├── app.php              # App constants, timezone, currency, version
│   ├── database.php         # PDO database connection handler
│   └── features.php         # Default feature toggles
├── core/                    # Core framework & engine services
│   ├── App.php              # Application bootstrapper
│   ├── Database.php         # Singleton PDO wrapper
│   ├── Auth.php             # Authentication & session manager
│   ├── RBAC.php             # Role-Based Access Control & Permission engine
│   ├── Router.php           # Clean routing & request handling
│   ├── View.php             # View rendering engine & layout helper
│   ├── Session.php          # Secure session handler & Flash messages
│   ├── CSRF.php             # CSRF token generator & validator
│   ├── Validator.php        # Input validation rules & sanitization
│   ├── FeatureManager.php   # Dynamic module/feature toggle engine
│   ├── AuditLogger.php      # System audit trail logger
│   └── Notification.php     # Multi-channel notification engine (Email/WhatsApp/SMS)
├── database/                # Database migrations and seeders
│   ├── schema.sql           # Complete initial database DDL
│   └── seeds.sql            # Default roles, superadmin, settings, sample plans
├── modules/                 # Modular domain business engines
│   ├── members/             # Member service, profile, notes, freeze
│   ├── membership/          # Plans, calculations, active/expiry engine
│   ├── attendance/          # QR, PIN, manual check-in, streak, duplicate prevention
│   ├── payments/            # Recording, history, receipts, revenue analytics
│   ├── progress/            # Photos, body measurements, privacy engine, comparison
│   ├── branches/            # Multi-branch management & isolation
│   ├── staff/               # Staff, trainers, assignees, worklogs
│   ├── reports/             # Daily owner report, financial, retention analytics
│   └── automation/          # Automation builder, cron runners, alerts
├── controllers/             # Request handlers (Admin, Member, Staff, Api, Cron)
│   ├── AuthController.php
│   ├── AdminController.php
│   ├── MemberController.php
│   ├── AttendanceController.php
│   ├── PaymentController.php
│   ├── ProgressController.php
│   ├── FeatureController.php
│   └── ApiController.php
├── views/                   # Template layouts and component views
│   ├── layouts/             # Main layout, auth layout, member layout, kiosk layout
│   ├── partials/            # Header, Sidebar, BottomNav (mobile), Modals, Alerts
│   ├── admin/               # Admin / Owner screens (Dashboard, Members, Payments, etc.)
│   ├── member/              # Member portal (Dashboard, QR, Attendance, Progress, etc.)
│   ├── kiosk/               # Self-service check-in kiosk mode (Fullscreen QR/PIN)
│   └── errors/              # 403, 404, 500 pages
├── uploads/                 # Uploaded media (member photos, progress photos, receipts)
│   ├── members/
│   ├── progress/
│   └── receipts/
├── docs/                    # Architectural & schema documentation
├── Readme/                  # Initial user ideas and requirements
├── Agentimplementation.md   # Progress tracker & phase checklist
├── GEMINI.md                # Agent instructions & core behavior
├── README.md                # Project documentation
└── index.php                # Entry point
```

---

## 3. Coding & Security Standards
1. **Prepared Statements**: ALWAYS use PDO prepared statements with parameter binding:
   ```php
   $stmt = $pdo->prepare("SELECT * FROM members WHERE id = :id AND branch_id = :branch_id");
   $stmt->execute([':id' => $id, ':branch_id' => $branchId]);
   ```
2. **Output Escaping**: NEVER echo untrusted data without escaping:
   ```php
   echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
   ```
3. **Strict Validation**: All input (POST, GET, JSON) must be validated server-side.
4. **CSRF Tokens**: Every form POST and destructive action must validate CSRF tokens.
5. **Granular Access Control**: Verify RBAC permissions in every controller/service:
   ```php
   RBAC::requirePermission('members.create');
   ```
6. **Feature Check**: Verify feature status before execution:
   ```php
   FeatureManager::requireFeature('progress_photos');
   ```
7. **No Hard-coded Business Logic in Views**: Views only display data passed by controllers.
8. **Responsive UI**: Mobile-first design for member portal and owner quick-dashboard.
