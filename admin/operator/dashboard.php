<?php
require_once __DIR__ . '/../../partials/auth_check.php';
include __DIR__ . '/../../partials/admin_header.php';

$counts            = Product::counts();
$total_products    = $counts['total'];
$total_stock_value = $counts['stock_value'];
$low_stock_count   = $counts['low_stock'];

$low_stock_products = Product::lowStock();

// ---- Inventory chart data ----
$stockByProduct  = Report::stockByProduct(10);       // lowest-stock products
$stockByCategory = Report::stockValueByCategory();    // stock value per category
$movementTrend   = Report::stockMovementTrend(14);    // IN/OUT flow, last 14 days

// Bar chart: current stock vs reorder level
$barLabels  = array_map(fn($r) => $r['name'], $stockByProduct);
$barStock   = array_map(fn($r) => (int)$r['stock'], $stockByProduct);
$barReorder = array_map(fn($r) => (int)$r['reorder_level'], $stockByProduct);

// Bar chart: stock value per category
$catLabels  = array_map(fn($r) => $r['category'], $stockByCategory);
$catValues  = array_map(fn($r) => round((float)$r['value'], 2), $stockByCategory);

// Line chart: stock movement trend
$lineLabels = array_map(fn($r) => $r['label'], $movementTrend);
$lineIn     = array_map(fn($r) => (int)$r['in_qty'], $movementTrend);
$lineOut    = array_map(fn($r) => (int)$r['out_qty'], $movementTrend);
?>

<style>
    .view-desc-btn { background: #2196f3; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; transition: background 0.2s; }
    .view-desc-btn:hover { background: #0b7dda; }
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center; }
    .modal-content { background: #fff; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden; }
    .modal-header { background: #2e7d32; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { margin: 0; font-size: 1.2rem; }
    .modal-close { font-size: 1.5rem; cursor: pointer; font-weight: bold; }
    .modal-close:hover { color: #ffb300; }
    .modal-body { padding: 20px; max-height: 60vh; overflow-y: auto; }
    .modal-body p { margin: 10px 0; line-height: 1.5; }
    .modal-body strong { color: #2e7d32; display: inline-block; width: 110px; }
    .modal-footer { background: #f5f5f5; padding: 12px 20px; text-align: right; border-top: 1px solid #eee; }
    .modal-close-btn { background: #757575; color: white; border: none; padding: 6px 18px; border-radius: 4px; cursor: pointer; }
    .modal-close-btn:hover { background: #5d5d5d; }
    .low-stock { color: #d32f2f; font-weight: bold; }
</style>

<h1>Operator Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-info"><h3>Total Products</h3><div class="stat-number"><?php echo $total_products; ?></div></div><div class="stat-icon"><i class="fas fa-box"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>Total Stock Value</h3><div class="stat-number">Rs. <?php echo number_format($total_stock_value); ?></div></div><div class="stat-icon"><i class="fas fa-rupee-sign"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>Low Stock Items</h3><div class="stat-number <?php echo $low_stock_count > 0 ? 'stat-warning' : ''; ?>"><?php echo $low_stock_count; ?></div></div><div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div></div>
</div>

<!-- ===== Inventory analytics ===== -->
<div class="chart-grid">
    <div class="chart-card">
        <h3><i class="fas fa-boxes"></i> Stock Levels &amp; Reorder Points (lowest 10)</h3>
        <canvas id="stockBar"></canvas>
    </div>
    <div class="chart-card">
        <h3><i class="fas fa-layer-group"></i> Stock Value by Category</h3>
        <canvas id="categoryBar"></canvas>
    </div>
</div>

<div class="chart-grid" style="grid-template-columns: 1fr;">
    <div class="chart-card">
        <h3><i class="fas fa-chart-line"></i> Inventory Movement — Stock In vs Out (last 14 days)</h3>
        <canvas id="movementLine" style="max-height: 280px;"></canvas>
    </div>
</div>

<h2>Low Stock Alerts</h2>
<div class="table-container">
    <table>
        <thead>
            <tr><th>ID</th><th>Product</th><th>Category</th><th>Stock</th><th>Reorder Level</th><th>Details</th><th>Description</th></tr>
        </thead>
        <tbody>
            <?php if (empty($low_stock_products)): ?>
                <tr><td colspan="7">No low stock items</td></tr>
            <?php else: foreach ($low_stock_products as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                    <td class="low-stock"><?php echo (int)$p['stock']; ?></td>
                    <td><?php echo (int)$p['reorder_level']; ?></td>
                    <td><?php echo 'Brand: ' . htmlspecialchars($p['brand'] ?? '-'); ?></td>
                    <td>
                        <button class="view-desc-btn" onclick="showProductDetails(<?php echo (int)$p['product_no']; ?>)">View</button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="productModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Product Details</h3>
            <span class="modal-close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p><strong>ID:</strong> <span id="modal-id"></span></p>
            <p><strong>Name:</strong> <span id="modal-name"></span></p>
            <p><strong>Category:</strong> <span id="modal-category"></span></p>
            <div id="modal-extra"></div>
            <p><strong>Stock:</strong> <span id="modal-stock"></span></p>
            <p><strong>Reorder Level:</strong> <span id="modal-reorder"></span></p>
            <p><strong>Price:</strong> <span id="modal-price"></span></p>
            <p><strong>Description:</strong> <span id="modal-description"></span></p>
        </div>
        <div class="modal-footer">
            <button class="modal-close-btn" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<script>
function showProductDetails(productNo) {
    const url = '/fertilizer-shop/ajax/php/products.php?action=get&product_no=' + productNo;
    fetch(url, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(res => {
            if (!res.ok || !res.data) { alert('Failed to load product'); return; }
            const product = res.data;
            document.getElementById('modal-id').innerText = product.product_id;
            document.getElementById('modal-name').innerText = product.name;
            document.getElementById('modal-category').innerText = product.category_name;
            document.getElementById('modal-stock').innerText = product.stock;
            document.getElementById('modal-reorder').innerText = product.reorder_level;
            document.getElementById('modal-price').innerText = 'Rs. ' + Number(product.price).toLocaleString();
            document.getElementById('modal-description').innerText = product.description || '';
            let extraHtml = '';
            const d = product.details || {};
            if (product.brand) extraHtml += '<p><strong>Brand:</strong> ' + product.brand + '</p>';
            for (const k of ['npk_ratio','variety','package_size','form','active_ingredient','disease_control','material']) {
                if (d[k]) extraHtml += '<p><strong>' + k.replace('_',' ') + ':</strong> ' + d[k] + '</p>';
            }
            document.getElementById('modal-extra').innerHTML = extraHtml;
            document.getElementById('productModal').style.display = 'flex';
        });
}
function closeModal() { document.getElementById('productModal').style.display = 'none'; }
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const GREEN = '#2e7d32', AMBER = '#ff8f00', RED = '#c62828';
Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
Chart.defaults.color = '#555';

// Bar — current stock vs reorder level
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

// Bar — stock value by category
new Chart(document.getElementById('categoryBar'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($catLabels); ?>,
        datasets: [{
            label: 'Stock Value (Rs.)',
            data: <?php echo json_encode($catValues); ?>,
            backgroundColor: ['#2e7d32','#43a047','#ff8f00','#1976d2','#8e24aa','#00897b','#c62828','#5d4037'],
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: c => 'Rs. ' + Number(c.raw).toLocaleString() } }
        },
        scales: { y: { beginAtZero: true, title: { display: true, text: 'Rs.' } } }
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

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
