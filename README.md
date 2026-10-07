# Gym Management & Automation Platform

A multi-branch, configurable, high-performance **Gym Management & Automation Platform** built with PHP, MySQL, Vanilla JavaScript, and Bootstrap 5.

---

## 🚀 Key Highlights

1. **Coder Control Center & Feature Manager**: Enable or disable any feature across the entire platform or per branch dynamically without touching code.
2. **Comprehensive Member Lifecycle**: Registration, unique Member ID, dynamic QR code, status engine (Active, Expiring Soon, Expired, Frozen, Cancelled), freeze management, and renewal history.
3. **Multi-Method Attendance Engine**:
   - Dynamic QR Scan (Instant validation & expiry blocking)
   - Self-Service Kiosk Mode (Fullscreen entry terminal)
   - Member Portal In-App Check-in
   - Member ID + PIN Verification
   - Staff Fast Search & Check-in
   - Mobile Number Check-in
4. **Automated Expiry & Expiry Blocking**: Zero tolerance server-side validation rejecting expired membership check-ins before any record is created.
5. **Granular RBAC (Role-Based Access Control)**: Platform Owner (Super Admin) ➔ Gym Owner ➔ Branch Manager ➔ Staff ➔ Trainer ➔ Member.
6. **Financial & Payment Tracking**: Payment recording (Cash, UPI, Card, NetBanking), instant PDF/printable receipt generation, partial payments, due tracking, revenue analytics.
7. **Before & After Transformation Suite**:
   - Multi-checkpoint timeline (Initial, 30d, 60d, 90d, 6m, 1y)
   - Categorized photos (Front, Back, Left, Right, Flexed)
   - Granular privacy controls (Only Me, Me+Trainer, Me+Staff, Gym Community)
   - Interactive Before/After comparison slider
   - Body measurement tracker (Weight, Chest, Waist, Arms, Body Fat) with custom metric builder
8. **Automated Notifications & Reports**: Multi-channel alerts (Email, WhatsApp template ready, SMS hook) for registration, daily check-ins, renewal reminders, birthday greetings, and automated daily owner summary reports.
9. **Multi-Branch Ready**: Seamless branch switching, cross-branch access privileges, and branch-isolated reporting.

---

## 🛠️ System Architecture

```text
                        GYM PLATFORM
                             │
              ┌──────────────┼──────────────┐
              │              │              │
         SUPER ADMIN     GYM OWNER        MEMBER
       (Coder Center)  (Gym Controls) (My Portal)
              │              │              │
              └──────────────┼──────────────┘
                             │
                      FEATURE MANAGER
                             │
     ┌───────────┬───────────┼───────────┬───────────┐
     │           │           │           │           │
  Members    Attendance   Payments   Progress     Reports
     │           │           │           │           │
     └───────────┴───────────┼───────────┴───────────┘
                             │
                    NOTIFICATION ENGINE
                 (Email / WhatsApp / SMS)
```

---

## 📁 Project Structure

- `core/` - Application bootstrapper, PDO singleton, RBAC, Router, CSRF, Audit Logger, Feature Manager.
- `modules/` - Modular domain business services (Members, Membership, Attendance, Payments, Progress, Reports).
- `controllers/` - HTTP request handlers (Admin, Member, Staff, Api, Kiosk).
- `views/` - Clean responsive layouts and partials.
- `database/` - MySQL schema DDL & initial seeders.
- `assets/` - CSS design tokens, modern UI components, vanilla JS helpers.
- `uploads/` - Secure storage for member photos and progress media.
- `docs/` - System architecture, database schema, and feature matrix documentation.
- `Agentimplementation.md` - Master phase implementation tracker.

---

## 🚦 Getting Started

1. **Database Setup**:
   - Create a MySQL database (e.g. `gym_master`).
   - Import `database/schema.sql` and `database/seeds.sql`.
2. **Configuration**:
   - Copy `config/database.example.php` to `config/database.php` and set DB credentials.
   - Adjust `config/app.php` for base URL and timezone.
3. **Launch in Local Server**:
   - Serve via Laragon / Apache / PHP built-in server (`php -S localhost:8000`).
   - Log in using default credentials generated in `database/seeds.sql`.

---

## 📌 Implementation Status
See [Agentimplementation.md](file:///d:/Bala/laragon/www/Projects%20All/per/gym/Agentimplementation.md) for full phase-by-phase development progress.

---

## 🔮 Future Roadmap (Post-Version 1)
The following advanced modules are scheduled for subsequent milestone releases after core Version 1 operations are certified:

- [ ] **POS Supplement & Merchandise Store**: Product catalog, barcode scanner, inventory stock alerts, cart & checkout, invoice printing.
- [ ] **Locker & Smart Access Control**: Digital locker assignment, IoT turnstile gate webhooks, and RFID hardware reader integration.
- [ ] **Class Schedules & PT Booking**: Group fitness class timetables, trainer slot booking, capacity caps, and attendance roster.
- [ ] **Diet & Workout Plan Builder**: Macro calculators, custom exercise workout logs, trainer-assigned weekly workout programs.
- [ ] **Financial Payroll & Expense Manager**: Staff salary calculations, trainer commissions per session, gym utility expense tracking.
- [ ] **In-App Community & Chat**: Direct messaging between trainers and members, workout challenge leaderboards, and gym announcements feed.
- [ ] **DEXA & Biometric Body Scan Sync**: InBody / DEXA scanner data import, 3D body fat visualization, and automated PDF health reports.
- [ ] **Referral & Loyalty Rewards Engine**: Member invite codes, reward points redemption, and coupon campaign manager.

*Note: Template references and scraped UI components are stored in `D:\web-scrap\dreamsgym.dreamstechnologies.com\` for future design reference.*