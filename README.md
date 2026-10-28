# 🏋️ GymFlow — Futuristic Gym Management Platform

> **Run your gym's daily membership, payment operations, and renewals from one intuitive dashboard with real-time financial tracking and zero unnecessary complexity.**

![GymFlow UI Mockup](https://images.openai.com/static-rsc-4/KAnWPXobzE3kMokiywJgGXcWhKHtla3YYJWciV0AI5_5LlZGqZ0a1URs6Vo2U1O69yKUNzXnfIpYVPhSwoZ70aKbYc8LNwL2QcFpQX3ferHhC97wKjgPAdHO8RkbCKryNRIXeQeVTuzeQuqG7eL0CMrQDKHy9tc4p7Dx71v2cBg?purpose=inline)

---

## ⚡ Key Highlights
- **Multi-Tenant Gym Isolation:** Complete data separation for gym owners and staff with role-based access control.
- **Real-Time KPI Dashboard:** Live metrics for total members, active subscriptions, upcoming expiries, today's and monthly collections, and outstanding dues.
- **Component-Based UI:** Modular, reusable UI components (`metric_card`, `data_table`, `status_badge`, `receipt_modal`, etc.) designed with a futuristic aesthetic.
- **Multi-Method Billing & Balances:** Support for Cash, UPI, Card, and Bank Transfers with automatic dues calculation and decimal precision.
- **Printable Receipts:** Thermal POS slip (80mm) and standard A4 receipt generation.
- **Audited Payment Reversals:** Non-destructive corrections with mandatory reasons, logged to an immutable audit trail.
- **Spreadsheet Import & Export:** Bulk member onboarding via CSV/XLSX with preview, validation, and row-level error reporting.
- **WhatsApp Reminder Integration:** Click-to-chat links with prefilled, personalized reminder templates.

---

## 🛠️ Technology Stack
- **Backend:** PHP 8.3+ (ZTS Visual C++ 2019 x64)
- **Database:** MySQL 8.4+ (InnoDB, UTF-8 `utf8mb4_unicode_ci`, Database: `gym-premium`)
- **Database Access:** PDO with strictly prepared statements
- **Frontend:** HTML5, CSS3, Bootstrap 5.3, Vanilla JavaScript (ES6+ modules)
- **Styling Architecture:** Component-Based Design System with Modern CSS Tokens (Unified all-around borders, soft elevation, zero single-sided borders)
- **Data Visualizations:** Chart.js 4+
- **Icons:** Bootstrap Icons
- **Spreadsheet Processing:** PhpSpreadsheet
- **Testing:** PHPUnit 10+ / Custom Integration Test Suite

---

## 📐 Project Structure
```
gym/
├── app/
│   ├── Controllers/         # HTTP Controllers (thin orchestration)
│   ├── Models/              # Database Models & Query Builders
│   ├── Services/            # Business & Financial Domain Services
│   ├── Middleware/          # Auth, Role, Tenant, and CSRF Filters
│   ├── Validators/          # Request Validation & Sanitizers
│   └── Views/
│       ├── layouts/         # Base layout templates (main.php, auth.php)
│       ├── components/      # Reusable UI component partials
│       ├── partials/        # Sidebar, topbar, alerts, footer
│       ├── dashboard/       # KPI dashboard & chart views
│       ├── members/         # Member CRUD & 360° profile view
│       ├── plans/           # Membership plan management
│       ├── payments/        # Payments, partial billing, receipts
│       ├── renewals/        # Expiry tracking (7d/30d) & WhatsApp links
│       ├── reports/         # Collections, Dues, and Plan reports
│       └── imports/         # CSV/XLSX upload, preview & import
├── config/
│   ├── app.php              # App settings, timezone, currency
│   └── database.php         # PDO connection to `gym-premium`
├── database/
│   ├── migrations/          # Table migration scripts
│   └── seeders/             # Demo development seeder
├── docs/
│   ├── database.md          # Complete schema definition
│   └── phases/              # Phase-wise roadmap & specifications
├── public/
│   ├── index.php            # Front Controller & App Bootstrap
│   ├── .htaccess            # Clean URL rewriting
│   └── assets/              # CSS tokens, JS modules, images
├── routes/
│   └── web.php              # Application Route Map
├── storage/
│   ├── logs/                # Error & audit logs
│   ├── imports/             # Private uploaded spreadsheets
│   └── exports/             # Private generated reports
├── tests/                   # Automated test suites
├── .agents/rules/rules.md   # Architectural & stack conventions
├── GEMINI.md                # Master rules & design standards
├── .env.example             # Environment template
└── composer.json            # Composer dependencies
```

---

## 🚀 Phased Implementation Progress

| Phase | Milestone | Key Features | Status |
|---|---|---|---|
| [**Phase 1**](file:///d:/Bala/laragon/www/Projects%20All/per/gym/docs/phases/phase-01-foundation-mvc-auth.md) | **Foundation, MVC & Auth** | Project scaffolding, Composer, PDO (`gym-premium`), Routing, Auth & Session Security, Core Futuristic Layout & Component Engine | 🟢 Completed |
| [**Phase 2**](file:///d:/Bala/laragon/www/Projects%20All/per/gym/docs/phases/phase-02-migrations-tenancy-members.md) | **Migrations, Tenancy & Members** | Database schema & migrations, Gym Isolation, Roles & Permissions, Member CRUD, Search, Auto-ID generator, Profile view, Photo upload | 🟢 Completed |
| [**Phase 3**](file:///d:/Bala/laragon/www/Projects%20All/per/gym/docs/phases/phase-03-plans-renewals-whatsapp.md) | **Plans, Renewals & WhatsApp** | Membership plans (1m, 3m, 6m, 1y), Subscription assignments, Expiry calculations (7d/30d), WhatsApp click-to-chat reminder prefill | 🟢 Completed |
| [**Phase 4**](file:///d:/Bala/laragon/www/Projects%20All/per/gym/docs/phases/phase-04-payments-billing-reversals.md) | **Payments, Balances & Reversals** | Multi-method payments (Cash, UPI, Card, Transfer), Partial/Full billing, Auto remaining dues, PDF printable receipt, Audited reversals | 🟢 Completed |
| [**Phase 5**](file:///d:/Bala/laragon/www/Projects%20All/per/gym/docs/phases/phase-05-dashboard-analytics-import-export.md) | **Dashboard & Import/Export** | Real-time futuristic KPI cards, Chart.js collection trends, Collections & Dues reports, Batch CSV import with validation & auto-generation | 🟢 Completed |
| [**Phase 6**](file:///d:/Bala/laragon/www/Projects%20All/per/gym/docs/phases/phase-06-audit-logs-staff-settings-ui-polish.md) | **Staff, Audit Logs & Settings** | Staff management, Audit trail logger, Gym profile settings, Mobile responsiveness & futuristic micro-animations | 🟢 Completed |
| [**Phase 7**](file:///d:/Bala/laragon/www/Projects%20All/per/gym/docs/phases/phase-07-testing-hardening-deployment.md) | **Testing, Hardening & Docs** | Comprehensive master automated test suite, Cross-gym isolation tests, CSRF/XSS audit, Deployment runbook, User documentation | 🟢 Completed |
| [**Phase 8**](file:///d:/Bala/laragon/www/Projects%20All/per/gym/docs/phases/phase-08-four-role-architecture.md) | **Four-Role Product Architecture** | SupremeLeader platform console, Owner provisioning CLI, Trainer scheduling & progress, Member self-service portal, Offline payment requests | 🟢 Completed |

---

## 👥 Four-Role Product Architecture

GymFlow operates as a dedicated single-gym product with four distinct roles:

1. **SupremeLeader (Platform Administrator & Support Operator):**
   - Exactly one active system administrator account.
   - Real-time system health (PHP, MySQL, disk storage, database statistics).
   - Safe daily rotating log viewer (`storage/logs/`) with severity filters, line bounding, and request ID search.
   - Protected Owner account activation/deactivation workflow.
   - Audited Support Context Mode allowing temporary owner workspace inspection with mandatory reason and exit action.
   - Provisioned securely via CLI: `php bin/provision-supreme-leader.php`.

2. **Owner (Gym Command Center):**
   - Full operational management of members, plans, payments, partial dues, renewals, and staff.
   - Trainer-to-member assignment management.
   - Offline payment requests review hub with atomic approval/rejection and automatic `BillingService` payment recording.
   - Member portal account provisioning directly from member profiles.

3. **Trainer (Trainer Workspace):**
   - Dashboard displaying today's scheduled sessions and assigned trainees.
   - Trainee-scoped member roster (strict isolation from non-assigned members).
   - Weekly recurring availability & date-specific schedule exceptions.
   - Session scheduling with automated conflict and overlap detection.
   - Progress and measurement logging (weight, body fat %, workout summaries, coach feedback).

4. **Member (Self-Service Member Portal):**
   - Personal dashboard at `/portal/dashboard`.
   - Live membership status, start/expiry dates, and real-time dues balance.
   - Printable receipt access for all past payments.
   - Scheduled personal training session calendar and progress tracking history.
   - Offline payment request submission (Cash, UPI, Bank Transfer) with reference numbers and status tracking.
   - Profile contact updates.

---

## 💻 Quick Start & Provisioning

### 1. Requirements
- Laragon with PHP 8.3+ and MySQL 8.4+
- Composer installed

### 2. Database Setup
The target database is configured as:
- **Database Name:** `gym-premium`
- **Host:** `127.0.0.1:3306`
- **Username:** `root`
- **Password:** `123`

### 3. Installation & Provisioning Steps
```bash
# 1. Install dependencies
composer install

# 2. Configure environment
cp .env.example .env

# 3. Run all database migrations
php database/migrate.php

# 4. Provision the SupremeLeader administrator
php bin/provision-supreme-leader.php

# 5. Provision the Gym Owner account
php bin/provision-owner.php
```

### 4. Running Test Suites
GymFlow includes comprehensive, non-destructive automated test suites verifying all architectural, tenant, and financial invariants:

```bash
# Master Regression Test Suite (Phases 1 - 7)
php tests/GymFlowFullTestSuite.php

# Phase A: Foundation Hardening, Session Dot-Notation & Daily Logger
php tests/PhaseATest.php

# Phase B: Four-Role Model, Provisioning & Route Isolation Matrix
php tests/PhaseBTest.php

# Phase C: Trainer Management, Availability, Scheduling & Progress
php tests/PhaseCTest.php

# Phase D: Member Portal, Offline Payment Requests & Approvals
php tests/PhaseDTest.php

# Phase E: SupremeLeader System Console, Logs & Full Verification
php tests/PhaseETest.php
```

---

## 🔒 Security & Financial Standards
- Strict tenant data isolation enforcing `WHERE gym_id = :gym_id` on all operations.
- `DECIMAL(12,2)` used exclusively for financial amounts.
- Database transactions (`PDO::beginTransaction`) for all multi-step financial mutations.
- Offline payment requests remain pending and do not affect balances until explicitly approved.
- CSRF token validation on all state-changing requests.
- Strict prohibition of single-sided borders, pseudo-element border stripes, and visual clutter.

