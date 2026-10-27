# GymFlow — Master Engineering Rules & Guidelines

## 1. Project Identity & Overview
- **Project Name:** GymFlow — Futuristic Gym Management Platform
- **Database Name:** `gym-premium`
- **Environment:** PHP 8.3+ (ZTS Visual C++ 2019 x64), MySQL 8.4+ (InnoDB), Laragon Stack
- **Architecture:** Maintainable lightweight PHP MVC with reusable component-based UI
- **Design Language:** Modern futuristic dashboard (inspired by the GymFlow UI mockup): clean white/pearl surface, dark slate contrast, vibrant cyan/indigo/emerald accents, unified uniform borders, soft ambient elevation, glassmorphism touches, rounded 16px–24px corners.

---

## 2. Core Architectural & Code Quality Standards

### MVC & Component-Based Structure
1. **Thin Controllers, Rich Services:** Controllers only orchestrate HTTP requests, delegate validation, call domain services, and return responses (JSON or View).
2. **Domain Services:** All business logic (membership duration calculation, dues calculation, payment reversal validation, multi-tenant checks) lives in `app/Services/`.
3. **Database Access:** Use strictly PDO prepared statements with parameter binding. Never concatenate SQL strings.
4. **Component-Based Views:** UI elements must be modular, reusable component partials (`app/Views/components/`):
   - `metric_card.php` (KPI summary cards with percentage badges and subtle icons)
   - `data_table.php` (Compact, accessible tables with sorting, filtering, and empty states)
   - `status_badge.php` (Active, Expired, Expiring Soon, Pending, Voided)
   - `action_button.php` / `quick_action_card.php`
   - `modal.php` / `confirm_dialog.php`
   - `receipt_template.php` (Printable thermal & A4 receipt modal)
   - `sidebar.php` & `topbar.php` (Responsive navigation with tenant gym switcher)
5. **DRY Principle:** No duplicated validation logic, query building, or template markup.

---

## 3. Strict Modern UI & Design System Rules

### ❌ Absolute Prohibitions (Single-Sided Borders & Visual Clutter)
1. **NO SINGLE-SIDED BORDERS:** Never use `border-top`, `border-bottom`, `border-left`, or `border-right` alone on cards, modals, dropdowns, table rows, or nav links.
2. **NO ACCENT BORDER HOVER EFFECTS:** Never expand or highlight a single edge on `:hover` or `:active`.
3. **NO PSEUDO-ELEMENT BORDER STRIPES:** Never use `::before` or `::after` to create colored lines on a single edge (e.g., colored 3px left/top bar).
4. **NO ISOLATED ACCENT UNDERLINES:** Do not add standalone colored horizontal lines under card headers, stat labels, or widgets.

### ✅ Required Modern Aesthetic System
1. **Unified Borders:** Always use uniform all-around borders:
   - Light mode: `border: 1px solid rgba(0, 0, 0, 0.07);`
   - Focused/Active: `border: 1px solid rgba(14, 165, 233, 0.4); box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);`
2. **Soft Depth & Layered Shadows:** Use multi-layered soft shadows:
   - Card default: `box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04), 0 2px 6px -1px rgba(0, 0, 0, 0.02);`
   - Card hover: `box-shadow: 0 12px 30px -4px rgba(0, 0, 0, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.03); transform: translateY(-4px);`
3. **Curated Futuristic Palette:**
   - Primary: `#0284c7` (Electric Ocean Blue) / `#2563eb` (Royal Blue)
   - Success / Positive: `#10b981` (Emerald Green)
   - Warning / Expiring: `#f59e0b` (Amber)
   - Danger / Overdue: `#ef4444` (Coral Red)
   - Neutral Surface: `#f8fafc` (Background), `#ffffff` (Card background), `#0f172a` (Deep Slate text)
4. **Border Radii:** Standard cards `16px` to `20px`, buttons `10px` to `12px`, pills/badges `9999px`.

---

## 4. Multi-Tenant Security & Financial Integrity

1. **Strict Multi-Tenant Isolation:**
   - Every database record (members, plans, memberships, payments, reversals, audit logs, settings) must be strictly bound to `gym_id`.
   - The active `gym_id` must ALWAYS come from the verified server-side session, NEVER from `$_GET`, `$_POST`, or client headers.
   - All SQL queries must enforce `WHERE gym_id = :gym_id` or join through an ownership-verified parent record.
2. **Financial Precision:**
   - Store all monetary values as `DECIMAL(12,2)`. NEVER float or double.
   - Outstanding balance formula: `balance = agreed_price - SUM(valid_payments)`.
   - Voided or reversed payments are NEVER counted towards collected revenue.
   - Payment creation and payment reversals must execute inside a PDO database transaction (`beginTransaction()`, `commit()`, `rollBack()`).
   - Voiding/reversals must be audited with reason, timestamp, and actor ID.
3. **Defensive Web Security:**
   - CSRF tokens required on all non-GET requests (`POST`, `PUT`, `DELETE`).
   - Secure cookie attributes: `HttpOnly`, `SameSite=Lax`, `Secure` when HTTPS.
   - Session regeneration on login (`session_regenerate_id(true)`).
   - Password hashing with PHP's `password_hash($pwd, PASSWORD_DEFAULT)`.
   - Output escaping via `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.

---

## 5. Context Memory & File Tracking Strategy
- `README.md` → Project overview, system requirements, quick start, live status.
- `.agents/rules/rules.md` → Technology stack, design tokens, coding conventions.
- `GEMINI.md` → Master rules and architecture principles.
- `docs/database.md` → Database schema, table structures, foreign keys, indexes.
- `docs/phases/` → Phase tracking, completion milestones, and testing checklists.
