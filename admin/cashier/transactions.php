<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config.php';
include '../includes/admin_header.php';

$filter = $_GET['date'] ?? '';
$query = "SELECT s.sale_id, s.sale_date, CONCAT(c.first_name,' ',c.last_name) as customer, s.total, p.payment_method
          FROM sales s
          LEFT JOIN customers c ON s.customer_no = c.customer_no
          LEFT JOIN payments p ON s.sale_no = p.sale_no
          ORDER BY s.sale_no DESC";
if ($filter) {
    $query = "SELECT s.sale_id, s.sale_date, CONCAT(c.first_name,' ',c.last_name) as customer, s.total, p.payment_method
              FROM sales s
              LEFT JOIN customers c ON s.customer_no = c.customer_no
              LEFT JOIN payments p ON s.sale_no = p.sale_no
              WHERE DATE(s.sale_date) = '$filter'
              ORDER BY s.sale_no DESC";
}
$result = $conn->query($query);
$transactions = $result->fetch_all(MYSQLI_ASSOC);
?>
<style>
    .filter-bar { background: white; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: center; }
    .btn-small { background: #2196f3; color: white; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; }
</style>
<h1>Transaction History</h1>
<div class="filter-bar">
    <label>Filter by Date:</label>
    <input type="date" id="dateFilter" value="<?php echo htmlspecialchars($filter); ?>">
    <button class="btn-primary" id="applyFilter">Apply</button>
    <a href="transactions.php" class="btn-outline">Clear</a>
</div>
<div class="table-container">
    <table>
        <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Total</th><th>Payment</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($transactions as $t): ?>
            <tr><td><?php echo $t['sale_id']; ?></td><td><?php echo $t['sale_date']; ?></td><td><?php echo htmlspecialchars($t['customer']); ?></td><td>Rs. <?php echo number_format($t['total'],2); ?></td><td><?php echo $t['payment_method']; ?></td><td><a href="invoice.php?sale_id=<?php echo $t['sale_id']; ?>" class="btn-small">View</a></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
document.getElementById('applyFilter').addEventListener('click', function() {
    let date = document.getElementById('dateFilter').value;
    if (date) window.location.href = 'transactions.php?date=' + date;
    else window.location.href = 'transactions.php';
});
</script>
<?php include '../includes/admin_footer.php'; ?>