# Phase 4: Payments, Multi-Method Billing, Balances, Receipts & Audited Reversals

## 1. Objectives
Implement robust, transaction-safe payment recording supporting Cash, UPI, Card, and Bank Transfer. Support full and partial payments with automatic dues tracking. Generate clean, printable receipts (thermal 80mm & standard A4), and implement owner-only audited payment reversals with reasons.

---

## 2. Detailed Deliverables & Checklist

### 2.1 Multi-Method Payment Engine
- [ ] **Payment Model (`app/Models/Payment.php`):**
  - Tenant-bound queries, sequential receipt numbers: `REC-{YEAR}-{0001}`.
  - Payment methods: `cash`, `upi`, `card`, `bank_transfer`, `other`.
  - Transaction fields: `amount`, `payment_date`, `reference_number` (e.g. UPI UTR or Card Auth code), `notes`, `status` (`paid`, `voided`).
  - Recorded by authenticated user (`user_id`).
- [ ] **Financial Calculation Service (`app/Services/BillingService.php`):**
  - Uses strictly `DECIMAL(12,2)` arithmetic (via PHP `bcmath` or formatted float calculations).
  - Formulas:
    - `total_paid = SUM(payments.amount WHERE status = 'paid')`
    - `outstanding_balance = agreed_price - total_paid`
  - Rejects payments where amount exceeds outstanding balance unless explicit credit is handled.
  - Supports installment payments (e.g., ₹2,500 advance on a ₹6,000 plan, balance ₹3,500 due).

### 2.2 Payment Controller & Views
- [ ] **PaymentController (`app/Controllers/PaymentController.php`):**
  - `index()`: Paginated payment transactions with date range filters, payment method filters, and search.
  - `create()` & `store()`: Atomic transaction:
    1. Validates membership ownership and amount.
    2. Inserts payment record.
    3. Generates unique receipt number within gym tenant.
    4. Logs action in `audit_logs`.
    5. Returns receipt view or redirects with success flash.
  - `showReceipt()`: Printable receipt modal and standalone receipt view.

### 2.3 Printable Receipts System
- [ ] **Receipt Component (`app/Views/components/receipt_modal.php`):**
  - Professional layout formatted with gym name, address, GST/phone, member name, member number, plan name, amount paid, payment method, reference, and balance remaining.
  - CSS `@media print` rules for:
    - Compact 80mm POS Thermal Slip format
    - Clean standard A4 invoice format
  - Quick print button (`window.print()`).

### 2.4 Audited Payment Reversals / Voids
- [ ] **Reversal Service & Controller (`app/Controllers/PaymentReversalController.php`):**
  - Restricted to `owner` role via `RoleMiddleware`. Staff cannot void payments.
  - Never deletes rows from `payments` table.
  - Execution steps inside PDO transaction:
    1. Verify payment belongs to tenant and status is currently `paid`.
    2. Change payment status to `voided`.
    3. Insert entry into `payment_reversals` (`authorized_by_user_id`, `amount`, `reason`, `reversed_at`).
    4. Recalculate member's outstanding balance automatically.
    5. Write high-priority entry into `audit_logs`.
  - Confirmation modal in UI requires entering a mandatory reason (e.g., "Entered wrong amount by mistake").

---

## 3. Verification Criteria
- [ ] Partial payment of ₹3,000 on ₹6,000 plan correctly updates remaining balance to ₹3,000.
- [ ] Recording a subsequent ₹3,000 payment clears the balance to ₹0.00.
- [ ] Voided payments are immediately subtracted from collected revenue and restored to outstanding dues.
- [ ] Staff user attempting to call reversal endpoint receives 403 Forbidden.
- [ ] Unique receipt number constraint prevents duplicate receipts in same gym.
