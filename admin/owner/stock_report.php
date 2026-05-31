<?php
require_once __DIR__ . '/../../partials/auth_check.php';

$period     = $_GET['period']     ?? '7days';
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date']   ?? '';
[$start, $end, $periodDisplay] = Report::dateRange($period, $start_date, $end_date);

$products       = Report::stockOverview();
$totalItems     = 0;
$totalValue     = 0.0;
$lowStock       = [];
$catSummary     = [];
foreach ($products as $p) {
    $totalItems += (int)$p['stock'];
    $totalValue += (float)$p['value'];
    if ($p['stock'] < $p['reorder_level']) $lowStock[] = $p;
    $cat = $p['category'];
    if (!isset($catSummary[$cat])) $catSummary[$cat] = ['count' => 0, 'value' => 0.0];
    $catSummary[$cat]['count']++;
    $catSummary[$cat]['value'] += (float)$p['value'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Stock Report - Agro City</title>
    <link rel="stylesheet" href="/fertilizer-shop/assets/css/style.css">
    <style>
        body { background: white; padding: 2rem; }
        .report-header { text-align: center; margin-bottom: 2rem; }
        .summary { display: flex; gap: 1rem; justify-content: center; margin: 2rem 0; }
        .summary-card { background: #f5f5f5; padding: 1rem; border-radius: 8px; text-align: center; min-width: 150px; }
        .category-card { background: #e8f5e9; padding: 0.8rem; border-radius: 8px; text-align: center; flex: 1; margin: 0 0.5rem; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
        th { background: #2e7d32; color: white; }
        .low-stock { color: #d32f2f; font-weight: bold; }
        .reorder { color: #f57c00; font-weight: bold; }
        .button { background: #2e7d32; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 1rem; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>Agro City - Stock Report</h1>
        <div class="company-details">
            <p>Epaladeniya, Kuliyapitiya, Sri Lanka | Tel: 076 115 7794 | Email: info@agrocity.lk</p>
            <p><strong>Period:</strong> <?php echo htmlspecialchars($periodDisplay); ?></p>
            <p><strong>Generated on:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card"><h3>Total Items</h3><p><?php echo number_format($totalItems); ?></p></div>
        <div class="summary-card"><h3>Total Stock Value</h3><p>Rs. <?php echo number_format($totalValue); ?></p></div>
        <div class="summary-card"><h3>Low Stock Items</h3><p><?php echo count($lowStock); ?></p></div>
    </div>

    <h2>Stock by Category</h2>
    <div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
        <?php foreach ($catSummary as $name => $d): ?>
        <div class="category-card">
            <h3><?php echo htmlspecialchars($name); ?></h3>
            <p>Items: <?php echo $d['count']; ?></p>
            <p>Value: Rs. <?php echo number_format($d['value']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <h2>Product Stock Details</h2>
    <table>
        <thead><tr><th>ID</th><th>Product</th><th>Category</th><th>Current Stock</th><th>Reorder Level</th><th>Status</th><th>Sold (<?php echo htmlspecialchars($periodDisplay); ?>)</th></tr></thead>
        <tbody>
            <?php foreach ($products as $p):
                $status = 'OK';
                if ($p['stock'] < $p['reorder_level'])           $status = '<span class="low-stock">Low Stock</span>';
                elseif ($p['stock'] < $p['reorder_level'] * 1.5) $status = '<span class="reorder">Near Reorder</span>';
            ?>
            <tr>
                <td><?php echo htmlspecialchars($p['product_id']); ?></td>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo htmlspecialchars($p['category']); ?></td>
                <td><?php echo (int)$p['stock']; ?></td>
                <td><?php echo (int)$p['reorder_level']; ?></td>
                <td><?php echo $status; ?></td>
                <td><?php echo (int)$p['sold_qty']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Low Stock Alerts</h2>
    <?php if (empty($lowStock)): ?>
        <p>No low stock items at this time.</p>
    <?php else: ?>
     <table>
        <thead><tr><th>Product</th><th>Current Stock</th><th>Reorder Level</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($lowStock as $item): ?>
             <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td class="low-stock"><?php echo (int)$item['stock']; ?></td>
                <td><?php echo (int)$item['reorder_level']; ?></td>
                <td>Below reorder level</td>
             </tr>
            <?php endforeach; ?>
        </tbody>
     </table>
    <?php endif; ?>

    <div class="no-print" style="text-align: center; margin-top: 2rem;">
        <button onclick="window.print()" class="button">Print</button>
        <button onclick="window.location.href='reports.php'" class="button">Close</button>
    </div>
</body>
</html>
