<?php
require_once __DIR__ . '/../../partials/auth_check.php';

// Update discount (POST)
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_discount'])) {
    Product::setDiscount((int)$_POST['product_no'], (float)$_POST['discount']);
    $success = "Discount updated for product #" . htmlspecialchars((string)$_POST['product_no']);
}

include __DIR__ . '/../../partials/admin_header.php';

$products      = Product::all(['active_only' => true]);
$counts        = Product::counts();
$today         = Sale::todayStats();
$today_sales   = $today['sales'];
$today_count   = $today['count'];
$low_stock_count = $counts['low_stock'];
?>

<style>
    .stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { background: white; border-radius: 16px; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .stat-number { font-size: 2rem; font-weight: 700; color: #2e7d32; }
    .discount-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    .discount-table th, .discount-table td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
    .discount-table th { background: #f2f2f2; font-weight: bold; color: black; }
    .discount-input { width: 80px; padding: 0.3rem; }
    .btn-small { background: #2e7d32; color: white; border: none; padding: 0.3rem 0.8rem; border-radius: 4px; cursor: pointer; }
</style>

<h1>Cashier Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-info"><h3>Today's Sales</h3><div class="stat-number">Rs. <?php echo number_format($today_sales); ?></div></div><div class="stat-icon"><i class="fas fa-rupee-sign"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>Today's Transactions</h3><div class="stat-number"><?php echo $today_count; ?></div></div><div class="stat-icon"><i class="fas fa-receipt"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>Low Stock Items</h3><div class="stat-number"><?php echo $low_stock_count; ?></div></div><div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>Total Products</h3><div class="stat-number"><?php echo count($products); ?></div></div><div class="stat-icon"><i class="fas fa-box"></i></div></div>
</div>

<h2>Product Discount Management</h2>
<?php if ($success): ?>
    <div style="background:#c8e6c9; padding:0.5rem; border-radius:4px; margin-bottom:1rem;"><?php echo $success; ?></div>
<?php endif; ?>

<table class="discount-table">
    <thead>
        <tr><th>ID</th><th>Name</th><th>Current Discount (%)</th><th>New Discount (%)</th><th>Action</th></tr>
    </thead>
    <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['product_id']); ?></td>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo number_format((float)$p['discount'], 2); ?>%</td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="number" name="discount" class="discount-input" step="0.01" value="<?php echo (float)$p['discount']; ?>">
                        <input type="hidden" name="product_no" value="<?php echo (int)$p['product_no']; ?>">
                </td>
                <td>
                        <button type="submit" name="update_discount" value="1" class="btn-small">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div style="margin-top: 2rem;">
    <a href="pos.php" class="btn-primary">Go to POS</a>
    <a href="transactions.php" class="btn-outline">View Transactions</a>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
