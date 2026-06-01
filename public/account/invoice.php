<?php
require_once __DIR__ . '/../../config.php';
Auth::requireCustomer();

$sale_id = (string)($_GET['sale_id'] ?? '');
$sale    = Sale::findById($sale_id);

// Security: the order must exist AND belong to the logged-in customer.
if (!$sale || (int)$sale['customer_no'] !== (int)$_SESSION['customer_no']) {
    http_response_code(404);
    die('Invoice not found.');
}

$items   = Sale::items((int)$sale['sale_no']);
$payment = Sale::payment((int)$sale['sale_no']);
$subtotal = 0.0;
foreach ($items as $it) { $subtotal += (float)$it['price'] * (int)$it['quantity']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo htmlspecialchars($sale['sale_id']); ?> — AgroCity</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #eef2ee; color: #212121; padding: 2rem 1rem; }
        .invoice-box { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); overflow: hidden; }
        .invoice-header { background: linear-gradient(135deg, #1b5e20, #2e7d32); color: #fff; padding: 2rem 2.5rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; }
        .invoice-header .brand { font-size: 1.9rem; font-weight: 800; }
        .invoice-header .brand span { color: #ffb300; }
        .invoice-header .shop-meta { font-size: 0.85rem; opacity: 0.9; margin-top: 0.4rem; line-height: 1.6; }
        .invoice-header .doc-title { text-align: right; }
        .invoice-header .doc-title h1 { font-size: 1.6rem; letter-spacing: 2px; }
        .invoice-header .doc-title .inv-no { font-size: 0.95rem; opacity: 0.95; margin-top: 0.3rem; }
        .status-pill { display: inline-block; margin-top: 0.5rem; padding: 0.25rem 0.9rem; border-radius: 50px; font-size: 0.8rem; font-weight: 700; background: #ffb300; color: #1b3d1b; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; padding: 1.8rem 2.5rem; }
        .meta-grid h4 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #2e7d32; margin-bottom: 0.4rem; }
        .meta-grid p { font-size: 0.95rem; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #2e7d32; color: #fff; text-align: left; padding: 0.8rem 2.5rem; font-size: 0.85rem; }
        thead th.r, tbody td.r { text-align: right; }
        tbody td { padding: 0.8rem 2.5rem; border-bottom: 1px solid #eee; font-size: 0.95rem; }
        tbody tr:nth-child(even) { background: #f7faf7; }
        .totals { padding: 1.2rem 2.5rem 2rem; display: flex; justify-content: flex-end; }
        .totals table { width: auto; min-width: 280px; }
        .totals td { padding: 0.4rem 0; }
        .totals td.r { text-align: right; font-weight: 600; }
        .totals .grand td { border-top: 2px solid #2e7d32; padding-top: 0.7rem; font-size: 1.25rem; font-weight: 800; color: #1b5e20; }
        .invoice-footer { text-align: center; padding: 1.5rem 2.5rem 2.5rem; color: #6b7c6e; font-size: 0.9rem; }
        .actions { max-width: 800px; margin: 1.2rem auto 0; display: flex; gap: 0.8rem; justify-content: center; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.6rem; border-radius: 50px; border: none; cursor: pointer; font-weight: 600; font-size: 0.95rem; text-decoration: none; }
        .btn-print { background: #2e7d32; color: #fff; }
        .btn-back { background: #fff; color: #2e7d32; border: 2px solid #2e7d32; }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-box { box-shadow: none; border-radius: 0; }
            .actions, .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="invoice-header">
            <div>
                <div class="brand">Agro<span>City</span></div>
                <div class="shop-meta">
                    Epaladeniya, Kuliyapitiya, Sri Lanka<br>
                    Tel: 076 115 7794 &nbsp;|&nbsp; info@agrocity.lk
                </div>
            </div>
            <div class="doc-title">
                <h1>INVOICE</h1>
                <div class="inv-no"><?php echo htmlspecialchars($sale['sale_id']); ?></div>
                <div class="status-pill"><?php echo htmlspecialchars($sale['status']); ?></div>
            </div>
        </div>

        <div class="meta-grid">
            <div>
                <h4>Billed To</h4>
                <p>
                    <strong><?php echo htmlspecialchars(trim((string)$sale['customer_name']) ?: 'Customer'); ?></strong><br>
                    <?php if (!empty($sale['customer_address'])): ?><?php echo nl2br(htmlspecialchars((string)$sale['customer_address'])); ?><br><?php endif; ?>
                    <?php if (!empty($sale['customer_phone'])): ?>Tel: <?php echo htmlspecialchars((string)$sale['customer_phone']); ?><br><?php endif; ?>
                    <?php if (!empty($sale['customer_email'])): ?><?php echo htmlspecialchars((string)$sale['customer_email']); ?><?php endif; ?>
                </p>
            </div>
            <div style="text-align:right;">
                <h4>Invoice Details</h4>
                <p>
                    <strong>Date:</strong> <?php echo htmlspecialchars($sale['sale_date']); ?><br>
                    <strong>Order Type:</strong> <?php echo htmlspecialchars($sale['sale_type']); ?><br>
                    <strong>Payment:</strong> <?php echo htmlspecialchars($payment['payment_method'] ?? 'N/A'); ?>
                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr><th>Item</th><th class="r">Qty</th><th class="r">Unit Price</th><th class="r">Amount</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it):
                    $line = (float)$it['price'] * (int)$it['quantity']; ?>
                <tr>
                    <td><?php echo htmlspecialchars($it['name']); ?></td>
                    <td class="r"><?php echo (int)$it['quantity']; ?></td>
                    <td class="r">Rs. <?php echo number_format($it['price'], 2); ?></td>
                    <td class="r">Rs. <?php echo number_format($line, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr><td>Subtotal</td><td class="r">Rs. <?php echo number_format($subtotal, 2); ?></td></tr>
                <tr class="grand"><td>Total</td><td class="r">Rs. <?php echo number_format($sale['total'], 2); ?></td></tr>
            </table>
        </div>

        <div class="invoice-footer">
            <i class="fas fa-seedling"></i> Thank you for shopping with AgroCity — growing together.
        </div>
    </div>

    <div class="actions no-print">
        <button class="btn btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print / Save as PDF</button>
        <a class="btn btn-back" href="/fertilizer-shop/public/account/dashboard.php"><i class="fas fa-arrow-left"></i> Back to My Account</a>
    </div>
</body>
</html>
