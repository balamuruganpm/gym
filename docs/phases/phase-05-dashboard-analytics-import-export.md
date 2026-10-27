# Phase 5: Dashboard Analytics, Interactive Charts, Financial Reports & CSV/Excel Import/Export

## 1. Objectives
Build the futuristic, real-time KPI Dashboard matching the user-provided design mockup (metrics cards, interactive Chart.js collection trend graph, upcoming expiries list, recent payments table, quick actions). Implement comprehensive financial and operational reports with CSV exports, plus a production-grade spreadsheet import engine with template download, validation preview, and row-level error reporting.

---

## 2. Detailed Deliverables & Checklist

### 2.1 Real-Time KPI Dashboard
- [ ] **Dashboard Metrics Service (`app/Services/DashboardService.php`):**
  - All numbers calculated dynamically from `gym-premium` database records:
    1. **Total Members:** All non-archived members in gym.
    2. **Active Members:** Members holding currently active memberships.
    3. **Expiring Soon (30d):** Members whose membership expires within next 30 days.
    4. **Expired Members:** Members whose latest membership is past expiry date.
    5. **Today's Collections:** Sum of valid payments received today (`payment_date = CURRENT_DATE()`).
    6. **This Month's Collections:** Sum of valid payments received in current month.
    7. **Outstanding Dues:** Total agreed prices minus valid payments across active & pending memberships.
  - Percentage changes compared to prior month.
- [ ] **Interactive Collections Chart (Chart.js):**
  - Line graph with gradient fill displaying daily collections for selected timeframe (This Month, Last 30 Days, This Year).
  - Clean tooltips, currency formatting (`₹`), and responsive resizing.
- [ ] **Quick Action Tiles:**
  - "Add Member", "Record Payment", "Create Plan", "Import Members".
- [ ] **Recent Payments & Expiring Members Tables:**
  - Compact tables with avatars, status pills, and direct detail links.

### 2.2 Operational & Financial Reports
- [ ] **ReportController (`app/Controllers/ReportController.php`):**
  - **Daily Collections Report:** Date-by-date breakdown with payment method breakdown (Cash vs UPI vs Card vs Bank Transfer).
  - **Monthly Collections Report:** High-level revenue progression.
  - **Outstanding Dues Report:** List of all members with pending balances, days overdue, and quick WhatsApp reminder button.
  - **Membership Renewal Report:** Members renewing within custom date range.
  - **Membership Plan Distribution:** Breakdown of popular plans.
  - One-click CSV export for any generated report.

### 2.3 Spreadsheet Import/Export Module
- [ ] **ImportController (`app/Controllers/ImportController.php`):**
  - **Template Download:** Generates sample CSV / XLSX file with column headers:
    `first_name, last_name, phone, email, gender, dob, emergency_contact, address, plan_name, start_date`.
  - **Upload & Security Validation:**
    - Size check (< 5MB), MIME verification (CSV, XLSX).
    - Stores file outside public root in `storage/imports/`.
    - No formula execution / macro sanitization.
  - **Validation Preview Step:**
    - Parses first 100 rows, checks mandatory fields, validates phone number formats, checks for duplicates against existing members.
    - Shows green for valid rows, red highlight for errors.
  - **Atomic Batch Import:**
    - Only imports confirmed rows.
    - Generates sequential member numbers.
    - Automatically enrolls in matching plans if specified.
    - Generates downloadable import error summary for rejected rows.
  - **Export Engine:**
    - Export active members, expired members, or payment logs to CSV or Excel.

---

## 3. Verification Criteria
- [ ] Dashboard metrics reflect exact database state with zero dummy hardcoded numbers.
- [ ] Chart displays actual day-by-day collections accurately.
- [ ] Uploading a CSV with duplicate phone numbers flags duplicates in the preview modal.
- [ ] Valid CSV rows import cleanly and appear in members table with auto-generated IDs.
- [ ] CSV reports download with correct headers, UTF-8 BOM, and accurate financial totals.
