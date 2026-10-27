# Phase 2: Database Migrations, Multi-Tenant Isolation & Member Management

## 1. Objectives
Implement the complete database migration and seed engine for `gym-premium`. Build strict multi-tenant gym isolation, user role enforcement (Owner vs. Staff), and the comprehensive Member Management module (CRUD, unique member number generation, photo upload, advanced search, filtering, and detailed member profile).

---

## 2. Detailed Deliverables & Checklist

### 2.1 Database Migrations & Seeders
- [ ] Migration runner CLI & web script (`database/migrate.php`).
- [ ] DDL scripts for all 11 tables matching [docs/database.md](file:///d:/Bala/laragon/www/Projects%20All/per/gym/docs/database.md):
  - `gyms`, `users`, `gym_users`, `members`, `membership_plans`, `memberships`, `payments`, `payment_reversals`, `audit_logs`, `import_jobs`, `gym_settings`.
- [ ] Database Seeder (`database/seeders/DatabaseSeeder.php`):
  - Seeds demo gym: **"Gold Fitness Gym"**
  - Seeds Owner account: `owner@gymflow.test` (password: `password123`)
  - Seeds Staff account: `staff@gymflow.test` (password: `password123`)
  - Seeds standard membership plans (1 Month, 3 Months, 6 Months, 1 Year).
  - Seeds realistic active, expiring, and overdue members with payment history.

### 2.2 Multi-Tenant Isolation & Role Authorization
- [ ] **Tenant Context (`app/Services/TenantContext.php`):**
  - Extracts active `gym_id` from session.
  - Automatically injects `gym_id` into all tenant queries.
  - Rejects any query or action where `gym_id` does not match active session.
- [ ] **RoleMiddleware (`app/Middleware/RoleMiddleware.php`):**
  - Enforces `owner` only permissions (e.g., payment reversals, staff management, financial reports).
  - Allows `staff` access to member management and recording payments.

### 2.3 Member Management Module
- [ ] **Member Model & Repository (`app/Models/Member.php`):**
  - Scoped queries with tenant filtering.
  - Member number auto-generation: `GF-{YEAR}-{0001}` unique per gym.
  - Search across name, phone, email, and member number.
  - Filter by membership status: Active, Expired, Expiring in 7/30 days, Dues Pending, Archived.
- [ ] **Member Controller (`app/Controllers/MemberController.php`):**
  - `index()`: Paginated member list with search, status filters, quick actions.
  - `create()` & `store()`: Add new member with client + server validation, optional photo upload.
  - `edit()` & `update()`: Edit contact info and personal details.
  - `show()`: Comprehensive 360° Member Profile showing personal details, active membership status badge, full subscription history, and payment transactions timeline.
  - `archive()` & `restore()`: Safe soft-archiving that retains financial and audit integrity.
- [ ] **Components & Views:**
  - `app/Views/members/index.php`: Modern data table component with avatar thumbnail, badge status, contact links, and dropdown actions.
  - `app/Views/members/create.php`: Structured card form with live validation feedback.
  - `app/Views/members/show.php`: Profile overview with membership card, payment ledger, and quick renewal button.

---

## 3. Verification Criteria
- [ ] Migrations run cleanly on `gym-premium` without errors.
- [ ] Seeder creates realistic sample data matching mockup metrics.
- [ ] Cross-tenant isolation test: User from Gym A cannot access or see members belonging to Gym B even if guessing URL IDs.
- [ ] Member creation automatically issues next sequential member number.
- [ ] Member profile accurately calculates active status from assigned memberships.
