<?php
require_once __DIR__ . '/../../config.php';
Auth::requireCustomer();
require_once __DIR__ . '/../../partials/public_header.php';

$orders = Sale::list(['customer_no' => (int)$_SESSION['customer_no'], 'type' => 'ONLINE']);
?>
<div class="container">
    <h1 style="color: #2e7d32;">My Orders</h1>
    <table class="table-container">
        <thead>
            <tr><th>Order ID</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><?php echo htmlspecialchars($o['sale_id']); ?></td>
                <td><?php echo htmlspecialchars($o['sale_date']); ?></td>
                <td>Rs. <?php echo number_format($o['total'],2); ?></td>
                <td><?php echo htmlspecialchars($o['status']); ?></td>
                <td><a href="/fertilizer-shop/admin/cashier/invoice.php?sale_id=<?php echo urlencode($o['sale_id']); ?>" target="_blank">View</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../../partials/public_footer.php'; ?>
