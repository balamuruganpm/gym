# Phase 6: Multi-Staff Management, Audit Logs, Gym Settings & Futuristic UI Polish

## 1. Objectives
Implement staff accounts management with role-based access control (RBAC), an immutable audit log viewer for security events, a settings panel for gym profile and renewal preferences, and complete futuristic UI polish matching the mockup (mobile responsive navigation, fluid micro-animations, accessible touch states, dark/light harmonious styling).

---

## 2. Detailed Deliverables & Checklist

### 2.1 Multi-Staff & Role-Based Access Control (RBAC)
- [ ] **Staff Controller (`app/Controllers/StaffController.php`):**
  - Owner can invite or register staff members.
  - Set active/inactive status for staff accounts.
  - Reset staff passwords securely.
  - Server-side role middleware blocks staff from accessing financial reports, settings, reversals, or other staff profiles.

### 2.2 System-Wide Audit Logging
- [ ] **Audit Logger Service (`app/Services/AuditLogger.php`):**
  - Logs critical actions:
    - Member created/updated/archived.
    - Payment recorded/voided.
    - Plan created/updated.
    - Staff created/modified.
    - CSV import executed.
  - Captures `user_id`, `gym_id`, `action`, `entity_type`, `entity_id`, IP address, timestamp, and JSON before/after diff.
- [ ] **Audit Log View (`app/Views/audit/index.php`):**
  - Searchable, filterable event timeline for gym owner.

### 2.3 Gym Profile & Preferences Settings
- [ ] **Settings Controller (`app/Controllers/SettingsController.php`):**
  - Update gym name, logo, phone, address, tax/GST number.
  - Configure renewal reminder thresholds (e.g. 7 days, 15 days, 30 days).
  - Customize WhatsApp reminder message templates.
  - Select currency symbol (`₹`, `$`, etc.) and date display format.

### 2.4 Futuristic UI Polish & Responsive Refinements
- [ ] **Component-Based Styling Refinements (`public/assets/css/app.css`):**
  - Enforce uniform subtle all-around borders: `border: 1px solid rgba(0,0,0,0.07);`.
  - Enforce soft elevation and layered shadows.
  - Glassmorphic top navigation with blurred backdrop (`backdrop-filter: blur(12px)`).
  - Hover micro-interactions (`transform: translateY(-4px); transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1)`).
  - Mobile bottom navigation / accessible off-canvas drawer (matching mobile mockup screen in attachment).
  - Compact responsive data tables with card-mode fallback on mobile screens.
  - Strictly **NO single-sided borders**, no decorative colored bars or single-edge accents.

---

## 3. Verification Criteria
- [ ] Staff user cannot view or navigate to `/settings`, `/staff`, or `/audit-logs`.
- [ ] Changes to membership or payment trigger audit log entries with user attribution.
- [ ] Mobile view toggles sidebar drawer cleanly and maintains full touch-target usability.
- [ ] Settings changes immediately update currency symbol and reminder templates across views.
