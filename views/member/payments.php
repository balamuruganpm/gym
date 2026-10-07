<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-receipt text-warning me-2"></i>My Payment Receipts</h4>
        <p class="text-secondary small mb-0">Official membership fee and service invoices</p>
    </div>
</div>

<div class="fp-card rounded-4 p-3">
    <?php if (empty($payments)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-wallet-fill fs-1 d-block mb-2"></i>
            No payment records found.
        </div>
    <?php else: ?>
        <div class="list-group list-group-flush">
            <?php foreach ($payments as $p): ?>
                <div class="list-group-item bg-transparent px-0 py-3 border-secondary border-opacity-25">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-primary bg-opacity-25 text-primary font-monospace"><?= htmlspecialchars($p['receipt_number'] ?? '') ?></span>
                            <h6 class="fw-bold mb-0 mt-1"><?= htmlspecialchars($p['plan_name'] ?? 'Membership Fee') ?></h6>
                            <div class="text-muted small"><?= !empty($p['created_at']) ? date('d M Y, h:i A', strtotime($p['created_at'])) : '' ?></div>
                        </div>
                        <div class="text-end">
                            <span class="fs-5 fw-bold text-success">₹<?= number_format($p['amount'] ?? 0, 2) ?></span>
                            <div class="text-muted small text-uppercase"><?= htmlspecialchars($p['payment_method'] ?? 'cash') ?></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-25">
                        <span class="badge bg-success small"><i class="bi bi-check-circle me-1"></i>PAID</span>
                        <a href="<?= BASE_URL ?>/admin/payments/receipt?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-printer me-1"></i> View Receipt
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
