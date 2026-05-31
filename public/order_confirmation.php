<?php
require_once __DIR__ . '/../partials/public_header.php';
$saleId = $_GET['sale_id'] ?? '';
?>
<div class="container" style="text-align: center;">
    <h1 style="color: #2e7d32;">Thank You for Your Order!</h1>
    <p>Your order has been placed successfully. You will receive a confirmation shortly.</p>
    <?php if ($saleId): ?>
        <p>Reference: <strong><?php echo htmlspecialchars($saleId); ?></strong></p>
    <?php endif; ?>
    <a href="products.php" class="btn-primary" style="background:#2e7d32;color:white;padding:.5rem 1rem;border-radius:50px;text-decoration:none;">Continue Shopping</a>
</div>
<?php require_once __DIR__ . '/../partials/public_footer.php'; ?>
