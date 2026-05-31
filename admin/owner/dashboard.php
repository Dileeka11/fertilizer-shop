<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config.php';
include '../includes/admin_header.php';

// Fetch dashboard stats
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$low_stock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock <= reorder_level")->fetch_assoc()['count'];
$today_sales = $conn->query("SELECT IFNULL(SUM(total),0) as total FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch_assoc()['total'];
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'];
?>

<div class="stats-grid">
    <div class="stat-card"><div><h3>Products</h3><div class="stat-number"><?php echo $total_products; ?></div></div><i class="fas fa-box fa-2x"></i></div>
    <div class="stat-card"><div><h3>Low Stock</h3><div class="stat-number"><?php echo $low_stock; ?></div></div><i class="fas fa-exclamation-triangle fa-2x"></i></div>
    <div class="stat-card"><div><h3>Today's Sales</h3><div class="stat-number">Rs. <?php echo number_format($today_sales); ?></div></div><i class="fas fa-rupee-sign fa-2x"></i></div>
    <div class="stat-card"><div><h3>Customers</h3><div class="stat-number"><?php echo $total_customers; ?></div></div><i class="fas fa-users fa-2x"></i></div>
</div>

<div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
    <a href="/fertilizer-shop/admin/operator/inventory.php" class="btn-primary">Manage Inventory</a>
    <a href="/fertilizer-shop/admin/cashier/pos.php" class="btn-primary">Point of Sale</a>
    <a href="sales_report.php" class="btn-outline">View Reports</a>
</div>

<h2>Recent Transactions</h2>
<div class="table-container">
    <table>
        <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Total</th></tr></thead>
        <tbody>
            <?php
            $recent_sales = $conn->query("
                SELECT s.sale_id, s.sale_date, CONCAT(c.first_name,' ',c.last_name) as customer, s.total
                FROM sales s
                LEFT JOIN customers c ON s.customer_no = c.customer_no
                ORDER BY s.sale_no DESC LIMIT 5
            ");
            while ($row = $recent_sales->fetch_assoc()):
            ?>
            <tr><td><?php echo $row['sale_id']; ?></td><td><?php echo $row['sale_date']; ?></td><td><?php echo $row['customer']; ?></td><td>Rs. <?php echo number_format($row['total'],2); ?></td></tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/admin_footer.php'; ?>