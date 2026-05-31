<?php
require_once __DIR__ . '/../../partials/auth_check.php';

$period     = $_GET['period']     ?? '7days';
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date']   ?? '';
[$start, $end, $periodDisplay] = Report::dateRange($period, $start_date, $end_date);

$summary       = Report::salesSummary($start, $end);
$byPeriod      = Report::salesByPeriod($period, $start, $end);
$byCategory    = Report::revenueByCategory($start, $end);
$byPayment     = Report::revenueByPayment($start, $end);
$byProduct     = Report::topProducts($start, $end, 50);

$tableHeader = $period === '1year' ? 'Months' : ($period === '1month' ? 'Weeks' : 'Days');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Revenue Summary - Agro City</title>
    <link rel="stylesheet" href="/fertilizer-shop/assets/css/style.css">
    <style>
        body { background: white; padding: 2rem; }
        .report-header { text-align: center; margin-bottom: 2rem; }
        .company-details { margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; border-bottom: 1px solid #ddd; }
        th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
        th { background: #2e7d32; color: white; font-weight: bold; }
        .summary { display: flex; gap: 1rem; justify-content: space-around; margin: 2rem 0; }
        .summary-card { background: #f5f5f5; padding: 1rem; border-radius: 8px; text-align: center; flex: 1; }
        .category-card { background: #e8f5e9; padding: 0.8rem; border-radius: 8px; text-align: center; flex: 1; margin: 0 0.5rem; }
        .button { padding: 0.5rem 1rem; background: #2e7d32; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        @media print { .no-print { display: none; } body { background: white; color: black; } th { background: #ccc !important; color: black !important; } .summary-card, .category-card { background: none !important; border: 1px solid #ccc; } .button { display: none; } }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>Agro City - Revenue Summary</h1>
        <div class="company-details">
            <p>Epaladeniya, Kuliyapitiya, Sri Lanka | Tel: 076 115 7794 | Email: info@agrocity.lk</p>
            <p><strong>Period:</strong> <?php echo htmlspecialchars($periodDisplay); ?></p>
            <p><strong>Generated on:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card"><h3>Total Revenue</h3><p>Rs. <?php echo number_format($summary['revenue']); ?></p></div>
    </div>

    <h2>Revenue by Category</h2>
    <div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
        <?php foreach ($byCategory as $cat): ?>
        <div class="category-card">
            <h3><?php echo htmlspecialchars($cat['category']); ?></h3>
            <p>Rs. <?php echo number_format($cat['revenue']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <h2>Revenue by <?php echo $tableHeader; ?></h2>
    <table>
        <thead><tr><th><?php echo $tableHeader; ?></th><th>Revenue (Rs.)</th></tr></thead>
        <tbody>
            <?php foreach ($byPeriod as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['label']); ?></td>
                <td>Rs. <?php echo number_format($row['total']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Revenue by Payment Method</h2>
    <table>
        <thead><tr><th>Method</th><th>Amount (Rs.)</th></tr></thead>
        <tbody>
            <?php foreach ($byPayment as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['method']); ?></td>
                <td>Rs. <?php echo number_format($p['amount']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Revenue by Product</h2>
    <table>
        <thead><tr><th>Product</th><th>Category</th><th>Revenue (Rs.)</th></tr></thead>
        <tbody>
            <?php foreach ($byProduct as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo htmlspecialchars($p['category_name']); ?></td>
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
