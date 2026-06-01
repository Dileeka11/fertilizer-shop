<?php
require_once __DIR__ . '/../../config.php';
Auth::requireCustomer();
require_once __DIR__ . '/../../partials/public_header.php';

$customer = Customer::find((int)$_SESSION['customer_no']);
$orders   = Sale::list(['customer_no' => (int)$_SESSION['customer_no'], 'type' => 'ONLINE']);
?>
<style>
    .acct-wrap { max-width: 1200px; margin: 2.5rem auto; padding: 0 1rem; }
    .acct-wrap > h1 { color: #1b5e20; margin-bottom: 1.5rem; }
    .dashboard { display: grid; grid-template-columns: 320px 1fr; gap: 2rem; align-items: start; }
    @media (max-width: 860px) { .dashboard { grid-template-columns: 1fr; } }

    .profile-card, .orders-card { background: #fff; border: 1px solid #e6ece6; border-radius: 16px; box-shadow: 0 4px 14px rgba(27,94,32,0.06); }
    .profile-card { padding: 1.5rem; }
    .orders-card { padding: 1.5rem; }
    .card-title { color: #1b5e20; font-size: 1.15rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }

    .profile-avatar { width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg,#2e7d32,#43a047); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 700; margin: 0 auto 1rem; }
    .profile-card p { margin: 0.45rem 0; color: #444; font-size: 0.95rem; }
    .profile-card p strong { color: #1b5e20; }
    .logout-link { display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 1.2rem; color: #c62828; font-weight: 600; }

    .orders-table { width: 100%; border-collapse: collapse; }
    .orders-table th { background: #2e7d32; color: #fff; padding: 0.8rem; text-align: left; font-size: 0.85rem; }
    .orders-table th:first-child { border-radius: 10px 0 0 0; }
    .orders-table th:last-child { border-radius: 0 10px 0 0; }
    .orders-table td { padding: 0.8rem; border-bottom: 1px solid #eef2ee; font-size: 0.92rem; }
    .orders-table tbody tr:hover { background: #f7faf7; }
    .badge { display: inline-block; padding: 0.2rem 0.7rem; border-radius: 50px; font-size: 0.75rem; font-weight: 700; }
    .badge-paid { background: #e8f5e9; color: #1b5e20; }
    .badge-cancelled { background: #ffebee; color: #c62828; }
    .badge-other { background: #fff8e1; color: #ef6c00; }
    .inv-btn { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.9rem; border-radius: 50px; background: #2e7d32; color: #fff; font-size: 0.8rem; font-weight: 600; text-decoration: none; }
    .inv-btn:hover { background: #1b5e20; text-decoration: none; }
    .empty-orders { text-align: center; padding: 2.5rem 1rem; color: #6b7c6e; }
    .empty-orders i { font-size: 2.5rem; color: #bcd6bc; display: block; margin-bottom: 0.6rem; }
</style>
<div class="acct-wrap">
    <h1><i class="fas fa-user-circle"></i> My Account</h1>
    <div class="dashboard">
        <div class="profile-card">
            <div class="profile-avatar"><?php echo strtoupper(htmlspecialchars(substr((string)$customer['first_name'], 0, 1) ?: 'U')); ?></div>
            <h3 class="card-title" style="justify-content:center;">Profile</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars((string)$customer['phone']); ?></p>
            <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars((string)$customer['address'])); ?></p>
            <a class="logout-link" href="/fertilizer-shop/public/account/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="orders-card">
            <h3 class="card-title"><i class="fas fa-clock-rotate-left"></i> Past Transactions</h3>
            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <i class="fas fa-receipt"></i>
                    No orders yet. <a href="/fertilizer-shop/public/products.php">Start shopping</a>.
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                <table class="orders-table">
                    <thead><tr><th>Invoice</th><th>Date</th><th>Total</th><th>Status</th><th>Invoice</th></tr></thead>
                    <tbody>
                        <?php foreach ($orders as $o):
                            $status = (string)$o['status'];
                            $badge = $status === 'Paid' ? 'badge-paid' : ($status === 'Cancelled' ? 'badge-cancelled' : 'badge-other');
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($o['sale_id']); ?></td>
                            <td><?php echo htmlspecialchars($o['sale_date']); ?></td>
                            <td>Rs. <?php echo number_format($o['total'],2); ?></td>
                            <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                            <td><a class="inv-btn" href="/fertilizer-shop/public/account/invoice.php?sale_id=<?php echo urlencode($o['sale_id']); ?>" target="_blank"><i class="fas fa-print"></i> View / Print</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../partials/public_footer.php'; ?>
