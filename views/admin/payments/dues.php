<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="h3 fw-bold mb-1"><i class="bi bi-clock-history text-warning me-2"></i>Outstanding Dues & Overdue Tracker</h2>
        <p class="text-secondary small mb-0">List of members with pending balances on active/expired plans</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="<?= BASE_URL ?>/admin/payments" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Payments
        </a>
    </div>
</div>

<!-- Dues Summary Badge -->
<div class="fp-card p-3 mb-4 border-start border-warning border-4">
    <div class="row g-3 text-center text-md-start align-items-center">
        <div class="col-md-6">
            <div class="text-secondary small fw-bold text-uppercase">TOTAL OUTSTANDING BALANCE</div>
            <div class="fs-2 fw-bold text-warning">₹<?= number_format($metrics['total_pending_dues'], 2) ?></div>
        </div>
        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end">
            <span class="badge bg-warning bg-opacity-25 text-warning fs-6 px-3 py-2">
                <i class="bi bi-people-fill me-1"></i> <?= count($dues) ?> Member(s) with Pending Dues
            </span>
        </div>
    </div>
</div>

<!-- Dues List Table -->
<div class="fp-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>Member Name</th>
                    <th>Contact</th>
                    <th>Plan / Subscription</th>
                    <th>Net Fee</th>
                    <th>Total Paid</th>
                    <th>Outstanding Due</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dues)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-secondary">
                            <i class="bi bi-check-circle-fill fs-1 text-success d-block mb-2"></i>
                            Great news! There are no outstanding member dues pending.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dues as $d): ?>
                        <tr>
                            <td>
                                <code><?= htmlspecialchars($d['member_code']) ?></code>
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($d['member_name']) ?></div>
                                <div class="text-secondary small"><?= htmlspecialchars($d['branch_name'] ?? 'Main Branch') ?></div>
                            </td>
                            <td>
                                <div><i class="bi bi-telephone text-primary me-1"></i><?= htmlspecialchars($d['member_phone']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-25 text-info"><?= htmlspecialchars($d['plan_name']) ?></span>
                                <div class="text-secondary small">Ends: <?= date('d M Y', strtotime($d['end_date'])) ?></div>
                            </td>
                            <td>₹<?= number_format($d['net_payable'], 2) ?></td>
                            <td><span class="text-success fw-semibold">₹<?= number_format($d['total_paid'], 2) ?></span></td>
                            <td>
                                <span class="fs-6 fw-bold text-danger">₹<?= number_format($d['balance_due'], 2) ?></span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-success" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#collectDueModal"
                                        data-member-id="<?= $d['member_id'] ?>"
                                        data-member-name="<?= htmlspecialchars($d['member_name']) ?>"
                                        data-subscription-id="<?= $d['subscription_id'] ?>"
                                        data-balance="<?= $d['balance_due'] ?>">
                                    <i class="bi bi-cash-stack me-1"></i> Collect Due
                                </button>
                                <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $d['member_phone']) ?>?text=<?= urlencode("Hello " . $d['member_name'] . ", gentle reminder that you have a pending gym fee balance of Rs." . number_format($d['balance_due'], 2) . ". Kindly clear the due at the front desk. Thank you!") ?>" target="_blank" class="btn btn-sm btn-outline-success ms-1" title="WhatsApp Reminder">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Collect Due -->
<div class="modal fade" id="collectDueModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content fp-card border-0">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold"><i class="bi bi-cash-coin text-success me-2"></i>Collect Outstanding Due</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/admin/payments/collect" method="POST">
                <?= CSRF::getField() ?>
                <input type="hidden" name="member_id" id="dueMemberId">
                <input type="hidden" name="subscription_id" id="dueSubId">

                <div class="modal-body">
                    <div class="p-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded mb-3">
                        <div class="text-secondary small">Member:</div>
                        <div class="fw-bold fs-5" id="dueMemberName"></div>
                        <div class="text-secondary small mt-2">Remaining Due Balance:</div>
                        <div class="fw-bold fs-4 text-warning" id="dueBalanceDisplay"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Amount (₹) *</label>
                        <input type="number" step="0.01" name="amount" id="dueAmountInput" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="upi">UPI / GPay / PhonePe / Paytm</option>
                            <option value="cash">Cash</option>
                            <option value="card">Debit / Credit Card (POS)</option>
                            <option value="bank_transfer">Direct Bank Transfer</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Transaction ID / Notes</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="Optional reference / txn ID">
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-fp-primary"><i class="bi bi-check-circle me-1"></i> Confirm & Generate Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var collectModal = document.getElementById('collectDueModal');
    if (collectModal) {
        collectModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var memberId = button.getAttribute('data-member-id');
            var memberName = button.getAttribute('data-member-name');
            var subId = button.getAttribute('data-subscription-id');
            var balance = button.getAttribute('data-balance');

            document.getElementById('dueMemberId').value = memberId;
            document.getElementById('dueSubId').value = subId;
            document.getElementById('dueMemberName').innerText = memberName;
            document.getElementById('dueBalanceDisplay').innerText = '₹' + parseFloat(balance).toFixed(2);
            document.getElementById('dueAmountInput').value = balance;
        });
    }
});
</script>
