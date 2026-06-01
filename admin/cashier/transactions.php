<?php
require_once __DIR__ . '/../../partials/auth_check.php';
include __DIR__ . '/../../partials/admin_header.php';

$filter = $_GET['date'] ?? '';
$transactions = Sale::list(['date' => $filter]);
?>
<style>
    .filter-bar { background: white; padding: 1.1rem 1.4rem; border-radius: 16px; margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(27,94,32,0.06); border: 1px solid #eef2ee; }
    .filter-bar label { font-weight: 600; color: #1b5e20; }
    .filter-bar input[type="date"] { width: auto; }
    .btn-small { display: inline-flex; align-items: center; gap: 0.3rem; background: #e8f5e9; color: #1b5e20; padding: 0.42rem 0.9rem; border-radius: 50px; text-decoration: none; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; transition: transform 0.12s, filter 0.15s; }
    .btn-small:hover { transform: translateY(-1px); filter: brightness(0.97); text-decoration: none; }
    .btn-cancel { background: #ffebee; color: #c62828; }
    .status-badge { padding: 0.22rem 0.85rem; border-radius: 50px; font-size: 0.76rem; font-weight: 700; }
    .status-Paid       { background: #c8e6c9; color: #1b5e20; }
    .status-Cancelled  { background: #ffcdd2; color: #c62828; }
    .status-Pending    { background: #fff3cd; color: #856404; }
    .status-Refunded   { background: #e1bee7; color: #6a1b9a; }
</style>
<div class="section-header">
    <h1><i class="fas fa-history"></i> Transaction History</h1>
</div>
<div class="filter-bar">
    <label>Filter by Date:</label>
    <input type="date" id="dateFilter" value="<?php echo htmlspecialchars($filter); ?>">
    <button class="btn-primary" id="applyFilter"><i class="fas fa-filter"></i> Apply</button>
    <a href="transactions.php" class="btn-outline">Clear</a>
</div>
<div class="table-container">
    <table>
        <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($transactions as $t): ?>
            <tr data-sale="<?php echo htmlspecialchars($t['sale_id']); ?>">
                <td><?php echo htmlspecialchars($t['sale_id']); ?></td>
                <td><?php echo htmlspecialchars($t['sale_date']); ?></td>
                <td><?php echo htmlspecialchars(trim((string)$t['customer'])); ?></td>
                <td>Rs. <?php echo number_format($t['total'],2); ?></td>
                <td><?php echo htmlspecialchars((string)$t['payment_method']); ?></td>
                <td><span class="status-badge status-<?php echo htmlspecialchars($t['status']); ?>"><?php echo htmlspecialchars($t['status']); ?></span></td>
                <td>
                    <a href="invoice.php?sale_id=<?php echo urlencode($t['sale_id']); ?>" class="btn-small"><i class="fas fa-eye"></i> View</a>
                    <?php if ($t['status'] === 'Paid'): ?>
                        <button class="btn-small btn-cancel" onclick="cancelSale('<?php echo htmlspecialchars($t['sale_id']); ?>')"><i class="fas fa-ban"></i> Cancel</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
document.getElementById('applyFilter').addEventListener('click', function() {
    const d = document.getElementById('dateFilter').value;
    window.location.href = d ? ('transactions.php?date=' + d) : 'transactions.php';
});

function cancelSale(saleId) {
    const reason = prompt('Reason for cancelling sale ' + saleId + '? (optional)') ;
    if (reason === null) return; // user pressed Cancel
    const body = new URLSearchParams({ action: 'cancel', sale_id: saleId, reason: reason });
    fetch('/fertilizer-shop/ajax/php/sales.php', { method: 'POST', credentials: 'same-origin', body: body })
        .then(r => r.json())
        .then(res => {
            if (!res.ok) { alert('Error: ' + (res.error || res.message || 'unknown')); return; }
            alert('Sale ' + saleId + ' cancelled. Stock restored.');
            location.reload();
        })
        .catch(() => alert('Server error'));
}
</script>
<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
