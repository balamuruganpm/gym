# System Architecture Document

## 1. Executive Overview
The Gym Management & Automation Platform is an enterprise-grade, multi-branch, modular web system designed to empower gym owners, staff, trainers, and members. Unlike rigid, single-gym scripts, this platform implements a dynamic **Coder Control Center (Super Admin)** that governs feature availability, security policies, and automation workflows.

---

## 2. High-Level Layered Architecture

```text
┌─────────────────────────────────────────────────────────────┐
│                       PRESENTATION LAYER                     │
│  ┌──────────────────┐  ┌──────────────────┐  ┌───────────┐  │
│  │   Admin Portal   │  │   Member Portal  │  │   Kiosk   │  │
│  │ (Desktop/Tablet) │  │   (Mobile App)   │  │ Terminal  │  │
│  └─────────┬────────┘  └────────┬─────────┘  └─────┬─────┘  │
└────────────┼────────────────────┼──────────────────┼────────┘
             ▼                    ▼                  ▼
┌─────────────────────────────────────────────────────────────┐
│                      APPLICATION ROUTING                     │
│                  Router & Middleware Stack                  │
│       (Auth Check, RBAC Gate, CSRF, Feature Toggle)         │
└─────────────────────────────┬───────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                       CONTROLLER LAYER                      │
│   AuthController | AdminController | MemberController       │
│   AttendanceController | PaymentController | ApiController │
└─────────────────────────────┬───────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     SERVICE & ENGINE LAYER                  │
│  ┌───────────────────────┐   ┌───────────────────────────┐  │
│  │   Membership Engine   │   │     Attendance Engine     │  │
│  │ (Active/Expiry/Freeze)│   │  (QR/PIN/Duplicate/Block) │  │
│  ├───────────────────────┤   ├───────────────────────────┤  │
│  │     Payment Engine    │   │      Progress Engine      │  │
│  │  (Receipts/Due/Logs)  │   │   (Privacy/Photos/Diff)   │  │
│  ├───────────────────────┤   ├───────────────────────────┤  │
│  │  Notification Engine  │   │     Automation Engine     │  │
│  │ (Email/WhatsApp/SMS)  │   │     (Cron/Alerts/Jobs)    │  │
│  └───────────────────────┘   └───────────────────────────┘  │
└─────────────────────────────┬───────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      CORE SYSTEM SERVICES                   │
│   Database (PDO) | RBAC | FeatureManager | AuditLogger     │
└─────────────────────────────┬───────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                        DATA PERSISTENCE                     │
│               MySQL / MariaDB Relational Database           │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. Core Engine Specifications

### 3.1 Coder Control Center & Feature Manager
- Stores all platform capabilities in a persistent configuration table (`system_features`).
- Allows Super Admin / Developer to enable/disable modules globally or per branch.
- Each feature check is enforced both at the UI navigation layer and strictly at the controller/service layer (`FeatureManager::isEnabled('feature_name')`).

### 3.2 Role-Based Access Control (RBAC)
Hierarchy:
1. `superadmin` - Platform Developer / System Owner. Complete platform & coder control.
2. `owner` - Gym Owner. Full access to their gym branches, finances, members, reports.
3. `manager` - Branch Manager. Manages branch operations, staff, check-ins, members.
4. `staff` - Front desk / Reception. Member search, attendance recording, payment collection.
5. `trainer` - Personal / General Trainer. Assigned members, workout plans, progress reviews.
6. `member` - Gym Member. Personal attendance, QR code, progress timeline, receipts.

### 3.3 Membership Lifecycle & Expiry Engine
- Statuses: `ACTIVE`, `EXPIRING_SOON` (<=7 days), `EXPIRED`, `FROZEN`, `CANCELLED`.
- Freeze functionality: Shifts the `end_date` forward by the freeze duration and logs the freeze window in `membership_freezes`.
- Renewal: Extends active memberships or creates fresh renewals while maintaining unbroken financial and membership logs.

### 3.4 Attendance & Expiry Blocking Engine
- Zero-bypass server validation:
  1. Lookup member by QR token, Member ID, or PIN.
  2. Verify account is active and belonging to target branch.
  3. Validate membership end date >= current date.
  4. Verify not in frozen status.
  5. Check duplicate check-in within configured cooldown (e.g. 30 minutes).
  6. If valid: write to `attendance` table, increment streak, send owner alert (if enabled).
  7. If invalid: log rejection in `audit_logs` and return structured rejection payload with renewal prompt.

### 3.5 Progress Photo & Granular Privacy Engine
- Checkpoints: Baseline (Day 0), 30 Days, 60 Days, 90 Days, 6 Months, 1 Year, Custom.
- Angle tags: Front, Back, Left, Right, Flexed.
- Privacy Levels:
  - `ONLY_ME`: Visible only to the authenticated member.
  - `ME_TRAINER`: Visible to member and their assigned trainer.
  - `ME_STAFF`: Visible to member, assigned trainer, and gym management.
  - `COMMUNITY`: Shared in the gym feed / showcase.
- Enforced at the SQL query level (`WHERE (privacy = 'COMMUNITY' OR member_id = :uid OR (privacy IN ('ME_TRAINER', 'ME_STAFF') AND :role IN ('trainer', 'manager', 'owner')))`).

---

## 4. Multi-Branch Architecture
- Every tenant record contains `branch_id`.
- Members can have a `home_branch_id` and optional `cross_branch_access` flag for multi-branch gym networks.
- Multi-branch managers can switch branch context via a header selector without re-authenticating.
