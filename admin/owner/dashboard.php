<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config.php';
include '../includes/admin_header.php';

// ---- Summary stats ----
$total_products  = (int)Database::scalar("SELECT COUNT(*) FROM products");
$low_stock       = (int)Database::scalar("SELECT COUNT(*) FROM products WHERE stock <= reorder_level");
$today_sales     = (float)Database::scalar("SELECT IFNULL(SUM(total),0) FROM sales WHERE DATE(sale_date) = CURDATE() AND status='Paid'");
$total_customers = (int)Database::scalar("SELECT COUNT(*) FROM customers");
$stock_value     = (float)Database::scalar("SELECT IFNULL(SUM(stock*price),0) FROM products");

// ---- Inventory chart data ----
$stockByProduct  = Report::stockByProduct(10);          // lowest-stock products
$stockByCategory = Report::stockValueByCategory();       // stock value per category
$movementTrend   = Report::stockMovementTrend(14);       // IN/OUT flow, last 14 days

// Bar chart: stock vs reorder level
$barLabels  = array_map(fn($r) => $r['name'], $stockByProduct);
$barStock   = array_map(fn($r) => (int)$r['stock'], $stockByProduct);
$barReorder = array_map(fn($r) => (int)$r['reorder_level'], $stockByProduct);

// Doughnut: stock value by category
$catLabels  = array_map(fn($r) => $r['category'], $stockByCategory);
$catValues  = array_map(fn($r) => round((float)$r['value'], 2), $stockByCategory);

// Line chart: stock movement trend
$lineLabels = array_map(fn($r) => $r['label'], $movementTrend);
$lineIn     = array_map(fn($r) => (int)$r['in_qty'], $movementTrend);
$lineOut    = array_map(fn($r) => (int)$r['out_qty'], $movementTrend);
?>

<div class="stats-grid">
    <div class="stat-card accent-green">
        <div><h3>Products</h3><div class="stat-number"><?php echo $total_products; ?></div></div>
        <div class="stat-icon"><i class="fas fa-box"></i></div>
    </div>
    <div class="stat-card accent-red">
        <div><h3>Low Stock</h3><div class="stat-number"><?php echo $low_stock; ?></div></div>
        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
    </div>
    <div class="stat-card accent-amber">
        <div><h3>Today's Sales</h3><div class="stat-number">Rs. <?php echo number_format($today_sales); ?></div></div>
        <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
    </div>
    <div class="stat-card accent-blue">
        <div><h3>Customers</h3><div class="stat-number"><?php echo $total_customers; ?></div></div>
        <div class="stat-icon"><i class="fas fa-users"></i></div>
    </div>
</div>

<div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
    <a href="/fertilizer-shop/admin/operator/inventory.php" class="btn-primary"><i class="fas fa-boxes"></i> Manage Inventory</a>
    <a href="/fertilizer-shop/admin/cashier/pos.php" class="btn-primary"><i class="fas fa-cash-register"></i> Point of Sale</a>
    <a href="reports.php" class="btn-outline"><i class="fas fa-chart-bar"></i> View Reports</a>
</div>

<!-- ===== Inventory analytics ===== -->
<div class="chart-grid">
    <div class="chart-card">
        <h3><i class="fas fa-boxes"></i> Stock Levels &amp; Reorder Points (lowest 10)</h3>
        <canvas id="stockBar"></canvas>
    </div>
    <div class="chart-card">
        <h3><i class="fas fa-layer-group"></i> Stock Value by Category</h3>
        <canvas id="categoryDoughnut"></canvas>
    </div>
</div>

<div class="chart-grid" style="grid-template-columns: 1fr;">
    <div class="chart-card">
        <h3><i class="fas fa-chart-line"></i> Inventory Movement — Stock In vs Out (last 14 days)</h3>
        <canvas id="movementLine" style="max-height: 280px;"></canvas>
    </div>
</div>

<h2>Recent Transactions</h2>
<div class="table-container">
    <table>
        <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Total</th></tr></thead>
        <tbody>
            <?php foreach (Sale::list(['limit' => 5]) as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['sale_id']); ?></td>
                <td><?php echo htmlspecialchars($row['sale_date']); ?></td>
                <td><?php echo htmlspecialchars(trim($row['customer']) ?: 'Walk-in'); ?></td>
                <td>Rs. <?php echo number_format($row['total'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const GREEN = '#2e7d32', AMBER = '#ff8f00', RED = '#c62828';
Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
Chart.defaults.color = '#555';

// Bar — stock vs reorder level
new Chart(document.getElementById('stockBar'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($barLabels); ?>,
        datasets: [
            { label: 'Current Stock', data: <?php echo json_encode($barStock); ?>, backgroundColor: GREEN, borderRadius: 6 },
            { label: 'Reorder Level', data: <?php echo json_encode($barReorder); ?>, backgroundColor: AMBER, borderRadius: 6 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, title: { display: true, text: 'Units' } } }
    }
});

// Doughnut — stock value by category
new Chart(document.getElementById('categoryDoughnut'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($catLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($catValues); ?>,
            backgroundColor: ['#2e7d32','#43a047','#ff8f00','#1976d2','#8e24aa','#00897b','#c62828','#5d4037']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: c => c.label + ': Rs. ' + Number(c.raw).toLocaleString() } }
        }
    }
});

// Line — stock movement trend
new Chart(document.getElementById('movementLine'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($lineLabels); ?>,
        datasets: [
            { label: 'Stock In',  data: <?php echo json_encode($lineIn); ?>,  borderColor: GREEN, backgroundColor: 'rgba(46,125,50,0.12)', fill: true, tension: 0.35 },
            { label: 'Stock Out', data: <?php echo json_encode($lineOut); ?>, borderColor: RED,   backgroundColor: 'rgba(198,40,40,0.10)', fill: true, tension: 0.35 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, title: { display: true, text: 'Units moved' } } }
    }
});
</script>

<?php include '../includes/admin_footer.php'; ?>
