<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Slip - <?= htmlspecialchars($payment['receipt_number']) ?></title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 76mm;
            margin: 0 auto;
            padding: 5mm;
            font-size: 12px;
            line-height: 1.3;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .double-divider {
            border-top: 2px solid #000;
            margin: 6px 0;
        }
        .flex-between {
            display: flex;
            justify-content: space-between;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print();">
    <div class="no-print" style="margin-bottom: 10px; text-align: center;">
        <button onclick="window.print();" style="padding: 5px 15px; font-weight: bold; cursor: pointer;">Print Slip</button>
        <button onclick="window.close();" style="padding: 5px 15px; cursor: pointer;">Close</button>
    </div>

    <div class="text-center">
        <div class="fw-bold" style="font-size: 16px;">IRONFORGE GYM</div>
        <div><?= htmlspecialchars($payment['branch_name'] ?? 'Main Branch') ?></div>
        <div>Ph: <?= htmlspecialchars($payment['branch_phone'] ?? '+91 9876543210') ?></div>
    </div>

    <div class="double-divider"></div>

    <div class="flex-between">
        <span>Receipt:</span>
        <span class="fw-bold"><?= htmlspecialchars($payment['receipt_number']) ?></span>
    </div>
    <div class="flex-between">
        <span>Date:</span>
        <span><?= date('d/m/Y h:i A', strtotime($payment['created_at'])) ?></span>
    </div>

    <div class="divider"></div>

    <div class="flex-between">
        <span>Member:</span>
        <span class="fw-bold"><?= htmlspecialchars($payment['member_name']) ?></span>
    </div>
    <div class="flex-between">
        <span>Member ID:</span>
        <span><?= htmlspecialchars($payment['member_code']) ?></span>
    </div>
    <div class="flex-between">
        <span>Phone:</span>
        <span><?= htmlspecialchars($payment['member_phone']) ?></span>
    </div>

    <div class="divider"></div>

    <div class="flex-between fw-bold">
        <span>Item / Plan</span>
        <span>Amount</span>
    </div>
    <div class="flex-between">
        <span><?= htmlspecialchars(substr($payment['plan_name'] ?? 'Gym Fee', 0, 20)) ?></span>
        <span>₹<?= number_format($payment['amount'], 2) ?></span>
    </div>

    <div class="divider"></div>

    <div class="flex-between fw-bold" style="font-size: 14px;">
        <span>TOTAL PAID:</span>
        <span>₹<?= number_format($payment['amount'], 2) ?></span>
    </div>
    <div class="flex-between">
        <span>Mode:</span>
        <span class="fw-bold"><?= strtoupper($payment['payment_method']) ?></span>
    </div>

    <?php if (floatval($payment['balance_due']) > 0): ?>
        <div class="flex-between" style="color: #000; font-weight: bold;">
            <span>Balance Due:</span>
            <span>₹<?= number_format($payment['balance_due'], 2) ?></span>
        </div>
    <?php endif; ?>

    <div class="double-divider"></div>

    <div class="text-center" style="font-size: 10px;">
        <div>Non-refundable & Non-transferable</div>
        <div>Thank you for training with us!</div>
        <div style="margin-top: 5px;">*** STAY FIT & STRONG ***</div>
    </div>
</body>
</html>
