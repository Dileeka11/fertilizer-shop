<?php
require_once '../includes/auth_check.php';
include '../includes/admin_header.php';

$period = $_GET['period'] ?? '7days';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
?>

<h1>Reports Dashboard</h1>

<!-- Period Selector -->
<div style="background: white; padding: 1.5rem; border-radius: 16px; margin-bottom: 2rem; box-shadow: var(--shadow);">
    <form method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <label for="period">Quick Period:</label>
        <select name="period" id="period" style="padding: 0.5rem; border-radius: 8px; border: 1px solid #ccc;">
            <option value="7days" <?php echo $period == '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
            <option value="1month" <?php echo $period == '1month' ? 'selected' : ''; ?>>Last Month</option>
            <option value="1year" <?php echo $period == '1year' ? 'selected' : ''; ?>>Last Year</option>
        </select>

        <label for="start_date">Or Custom Range:</label>
        <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date); ?>" style="padding: 0.5rem; border-radius: 8px; border: 1px solid #ccc;">
        <span>to</span>
        <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($end_date); ?>" style="padding: 0.5rem; border-radius: 8px; border: 1px solid #ccc;">

        <button type="submit" class="btn-primary">Apply Period</button>
    </form>
</div>

<!-- Report Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
    <a href="sales_report.php?period=<?php echo urlencode($period); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" style="text-decoration: none;">
        <div class="stat-card" style="cursor: pointer;">
            <div class="stat-info">
                <h3>Sales Report</h3>
                <p>Sales trends, top products, and revenue summary.</p>
            </div>
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
        </div>
    </a>
    <a href="stock_report.php?period=<?php echo urlencode($period); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" style="text-decoration: none;">
        <div class="stat-card" style="cursor: pointer;">
            <div class="stat-info">
                <h3>Stock Report</h3>
                <p>Current stock levels, reorder alerts, and sold quantities.</p>
            </div>
            <div class="stat-icon"><i class="fas fa-boxes"></i></div>
        </div>
    </a>
    <a href="orders_report.php?period=<?php echo urlencode($period); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" style="text-decoration: none;">
        <div class="stat-card" style="cursor: pointer;">
            <div class="stat-info">
                <h3>Customer Orders</h3>
                <p>Online orders, top customers, and most sold products.</p>
            </div>
            <div class="stat-icon"><i class="fas fa-users"></i></div>
        </div>
    </a>
    <a href="revenue_summary.php?period=<?php echo urlencode($period); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" style="text-decoration: none;">
        <div class="stat-card" style="cursor: pointer;">
            <div class="stat-info">
                <h3>Revenue Summary</h3>
                <p>Revenue by period, payment method, and product.</p>
            </div>
            <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
        </div>
    </a>
</div>

<?php include '../includes/admin_footer.php'; ?>