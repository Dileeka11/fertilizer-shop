<?php
require_once __DIR__ . '/../../config.php';
Auth::requireCustomer();
require_once __DIR__ . '/../../partials/public_header.php';

$customer = Customer::find((int)$_SESSION['customer_no']);
$orders   = Sale::list(['customer_no' => (int)$_SESSION['customer_no'], 'type' => 'ONLINE']);
?>
<style>
    .dashboard { display: flex; gap: 2rem; flex-wrap: wrap; }
    .profile-card { background: white; padding: 1rem; border-radius: 12px; flex: 1; }
    .orders-card { background: white; padding: 1rem; border-radius: 12px; flex: 2; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 0.5rem; text-align: left; border-bottom: 1px solid #ddd; }
</style>
<div class="container">
    <h1>My Account</h1>
    <div class="dashboard">
        <div class="profile-card">
            <h3>Profile</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars((string)$customer['phone']); ?></p>
            <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars((string)$customer['address'])); ?></p>
            <p style="margin-top:1rem;"><a href="/fertilizer-shop/public/account/logout.php">Logout</a></p>
        </div>
        <div class="orders-card">
            <h3>Order History</h3>
            <?php if (empty($orders)): ?>
                <p>No orders yet.</p>
            <?php else: ?>
                <table>
                    <thead><tr><th>Invoice</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($o['sale_id']); ?></td>
                            <td><?php echo htmlspecialchars($o['sale_date']); ?></td>
                            <td>Rs. <?php echo number_format($o['total'],2); ?></td>
                            <td><?php echo htmlspecialchars($o['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../partials/public_footer.php'; ?>
