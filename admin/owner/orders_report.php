<?php
require_once __DIR__ . '/../../partials/auth_check.php';

$period     = $_GET['period']     ?? '7days';
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date']   ?? '';
[$start, $end, $periodDisplay] = Report::dateRange($period, $start_date, $end_date);

$stats         = Report::onlineOrdersStats($start, $end);
$topCustomers  = Customer::topCustomers(10, substr($start, 0, 10), substr($end, 0, 10));
$onlineProducts = Database::all(
    "SELECT p.name, c.category_name AS category, SUM(si.quantity) AS qty, SUM(si.quantity*si.price) AS revenue
     FROM sale_items si
     JOIN sales s    ON si.sale_no    = s.sale_no
     JOIN products p ON si.product_no = p.product_no
     JOIN categories c ON p.category_id = c.category_id
     WHERE s.sale_type='ONLINE' AND s.status='Paid' AND s.sale_date BETWEEN ? AND ?
     GROUP BY p.product_no
     ORDER BY revenue DESC",
    'ss', [$start, $end]
);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Online Customer Orders - Agro City</title>
    <link rel="stylesheet" href="/fertilizer-shop/assets/css/style.css">
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
</head>
<body>
    <div class="report-header">
        <h1>Agro City - Online Customer Orders Report</h1>
        <div class="company-details">
            <p>Epaladeniya, Kuliyapitiya, Sri Lanka | Tel: 076 115 7794 | Email: info@agrocity.lk</p>
            <p><strong>Period:</strong> <?php echo htmlspecialchars($periodDisplay); ?></p>
            <p><strong>Generated on:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card"><h3>Total Orders</h3><p><?php echo (int)$stats['orders']; ?></p></div>
        <div class="summary-card"><h3>Unique Customers</h3><p><?php echo (int)$stats['customers']; ?></p></div>
        <div class="summary-card"><h3>Avg Order Value</h3><p>Rs. <?php echo number_format($stats['avg']); ?></p></div>
    </div>

    <h2>Top 10 Customers</h2>
    <table>
        <thead><tr><th>Customer</th><th>Orders</th><th>Total Spent (Rs.)</th></tr></thead>
        <tbody>
            <?php foreach ($topCustomers as $c): ?>
            <tr>
                <td><?php echo htmlspecialchars(trim($c['name'])); ?></td>
                <td><?php echo (int)$c['orders']; ?></td>
                <td>Rs. <?php echo number_format($c['total']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Most Sold Products (Online)</h2>
    <table>
        <thead><tr><th>Product</th><th>Category</th><th>Quantity Sold</th><th>Total Revenue (Rs.)</th></tr></thead>
        <tbody>
            <?php foreach ($onlineProducts as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo htmlspecialchars($p['category']); ?></td>
                <td><?php echo (int)$p['qty']; ?></td>
                <td>Rs. <?php echo number_format($p['revenue']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="no-print" style="text-align: center; margin-top: 2rem;">
        <button onclick="window.print()" class="button">Print</button>
        <button onclick="window.location.href='reports.php'" class="button">Close</button>
    </div>
</body>
</html>
