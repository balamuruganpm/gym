# Phase 3: Membership Plans, Subscriptions, Renewals & WhatsApp Reminders

## 1. Objectives
Create the Membership Plans catalog, subscription assignment engine, automated expiry tracking (expiring in 7 days, 30 days, expired), seamless renewal flow preserving historical subscriptions, and one-click WhatsApp reminder integration with editable prefilled messages.

---

## 2. Detailed Deliverables & Checklist

### 2.1 Membership Plans Catalog
- [ ] **Plan Model (`app/Models/MembershipPlan.php`):**
  - Name, duration type (`months` or `days`), duration value, default price (`DECIMAL(12,2)`), active status.
- [ ] **Plan Controller (`app/Controllers/PlanController.php`):**
  - CRUD operations for gym owners.
  - Deactivating a plan hides it from new assignments while preserving existing memberships.
- [ ] **Views (`app/Views/plans/`):**
  - Grid of modern futuristic plan cards displaying price, duration badge, active member count, and action buttons.

### 2.2 Subscriptions & Membership Lifecycle Engine
- [ ] **Membership Model & Service (`app/Services/MembershipService.php`):**
  - Calculates start date and exact expiry date:
    - Default start date: today, or custom selected date.
    - If member renews an active plan early: option to start immediately or append after existing expiry date.
  - Price snapshotting: preserves original plan price, discount given, and final `agreed_price`.
  - Membership status evaluation:
    - `active`: Current date falls between start_date and expiry_date.
    - `expiring_soon`: Within 7 days or 30 days of expiry.
    - `expired`: Current date > expiry_date.
- [ ] **Renewal Flow (`app/Controllers/MembershipController.php`):**
  - One-click renewal modal from member profile or renewals list.
  - Creates a new `memberships` row linked via `renewal_of_id`, marking previous as `renewed` without mutating historic price data.

### 2.3 Expiry Tracking & Due Reminders
- [ ] **Renewals Center (`app/Controllers/RenewalController.php`):**
  - Tab 1: **Expiring in 7 Days** (urgent attention)
  - Tab 2: **Expiring in 30 Days** (upcoming cycle)
  - Tab 3: **Already Expired** (win-back candidates)
  - Tab 4: **Pending Balance Dues**
  - Configurable renewal warning window from gym settings.

### 2.4 WhatsApp Click-to-Chat Integration
- [ ] **WhatsApp Helper (`app/Services/WhatsAppReminder.php`):**
  - Generates direct URL: `https://wa.me/{phone}?text={encoded_message}`.
  - Template 1 (Expiring Soon): *"Hi {Name}, your {GymName} membership expires on {Date}. Renew today to continue your fitness streak!"*
  - Template 2 (Due Balance): *"Hi {Name}, friendly reminder that an outstanding balance of {Amount} is pending for your {GymName} membership."*
  - Template 3 (Expired): *"Hi {Name}, we miss you at {GymName}! Renew your membership today to get back on track."*
  - Modal in UI allows staff to preview and customize the message text before opening WhatsApp.

---

## 3. Verification Criteria
- [ ] Assigning a 3-month plan accurately computes the expiry date.
- [ ] Renewing an active membership appends the period smoothly and links to previous contract.
- [ ] WhatsApp button generates valid encoded links with personalized parameters.
- [ ] Historic prices on past memberships remain untouched when a plan's price is updated.
