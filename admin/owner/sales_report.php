<?php
require_once __DIR__ . '/../../partials/auth_check.php';
include __DIR__ . '/../../partials/admin_header.php';

$period     = $_GET['period']     ?? '7days';
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date']   ?? '';
[$start, $end, $periodDisplay] = Report::dateRange($period, $start_date, $end_date);

$summary      = Report::salesSummary($start, $end);
$byPeriod     = Report::salesByPeriod($period, $start, $end);
$topProducts  = Report::topProducts($start, $end, 5);
$periodHeader = $period === '1year' ? 'Months' : ($period === '1month' ? 'Weeks' : 'Days');
?>

<style>
    body { background: white; padding: 2rem; }
    .report-header { text-align: center; margin-bottom: 2rem; }
    .summary { display: flex; gap: 1rem; justify-content: center; margin: 2rem 0; }
    .summary-card { background: #f5f5f5; padding: 1rem; border-radius: 8px; text-align: center; min-width: 150px; }
    table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
    th { background: #2e7d32; color: white; }
    .button { background: #2e7d32; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 1rem; }
    @media print { .no-print { display: none; } }
</style>

<div class="report-header">
    <h1>Agro City - Sales Report</h1>
    <p>Epaladeniya, Kuliyapitiya, Sri Lanka | Tel: 076 115 7794</p>
    <p><strong>Period:</strong> <?php echo htmlspecialchars($periodDisplay); ?></p>
    <p><strong>Generated:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
</div>

<div class="summary">
    <div class="summary-card"><h3>Total Revenue</h3><p>Rs. <?php echo number_format($summary['revenue'], 2); ?></p></div>
    <div class="summary-card"><h3>Total Orders</h3><p><?php echo (int)$summary['orders']; ?></p></div>
    <div class="summary-card"><h3>Avg Order</h3><p>Rs. <?php echo number_format($summary['avg'], 2); ?></p></div>
</div>

<h2>Sales by <?php echo $periodHeader; ?></h2>
<table>
    <thead><tr><th><?php echo $periodHeader; ?></th><th>Sales (Rs.)</th></tr></thead>
    <tbody>
        <?php foreach ($byPeriod as $row): ?>
        <tr><td><?php echo htmlspecialchars($row['label']); ?></td><td>Rs. <?php echo number_format($row['total'], 2); ?></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Top Selling Products</h2>
<table>
    <thead><tr><th>Product</th><th>Quantity Sold</th><th>Revenue</th></tr></thead>
    <tbody>
        <?php foreach ($topProducts as $p): ?>
        <tr><td><?php echo htmlspecialchars($p['name']); ?></td><td><?php echo (int)$p['qty']; ?></td><td>Rs. <?php echo number_format($p['revenue'], 2); ?></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="no-print">
    <button onclick="window.print()" class="button">Print</button>
    <button onclick="window.location.href='dashboard.php'" class="button">Back</button>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
