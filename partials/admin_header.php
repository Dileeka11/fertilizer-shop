<?php
// Must be included after partials/auth_check.php (or any page that has called Auth::requireStaff())
$role         = $_SESSION['admin_role'];
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agro City - <?php echo ucfirst($role); ?> Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Global admin styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7fc; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background: linear-gradient(180deg, #1b5e20 0%, #2e7d32 100%); color: #fff; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; overflow-y: auto; }
        .sidebar-header { padding: 1.5rem 1rem; text-align: center; border-bottom: 1px solid #4caf50; }
        .sidebar-header h2 { font-size: 1.8rem; }
        .sidebar-header h2 span { color: #ffb300; }
        .sidebar-nav { flex: 1; padding: 1.5rem 0; }
        .sidebar-nav ul { list-style: none; }
        .sidebar-nav li { margin-bottom: 0.5rem; }
        .sidebar-nav a { display: flex; align-items: center; gap: 1rem; padding: 0.8rem 1.5rem; color: #e0e0e0; text-decoration: none; transition: 0.3s; border-left: 4px solid transparent; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(255,255,255,0.1); color: #fff; border-left-color: #ffb300; }
        .sidebar-nav i { width: 24px; }
        .sidebar-logout { padding: 1.5rem; border-top: 1px solid #4caf50; }
        .sidebar-logout a { display: flex; align-items: center; gap: 1rem; color: #e0e0e0; text-decoration: none; }
        .admin-main { flex: 1; margin-left: 260px; display: flex; flex-direction: column; min-height: 100vh; }
        .admin-topbar { background: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 10; }
        .page-title h1 { font-size: 1.8rem; color: #1b5e20; }
        .topbar-user { display: flex; align-items: center; gap: 1rem; }
        .user-name { font-weight: 600; color: #1b5e20; }
        .user-role { font-size: 0.8rem; color: #757575; }
        .user-avatar { width: 40px; height: 40px; background: #2e7d32; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; }
        .admin-content { padding: 2rem; flex: 1; }
        .stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: #fff; border-radius: 16px; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .stat-number { font-size: 2rem; font-weight: 700; color: #2e7d32; }
        .btn-primary { background: #2e7d32; color: white; padding: 0.8rem 2rem; border: none; border-radius: 50px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-outline { background: transparent; border: 2px solid #2e7d32; color: #2e7d32; padding: 0.6rem 1.5rem; border-radius: 50px; cursor: pointer; text-decoration: none; display: inline-block; }
        .table-container { overflow-x: auto; background: white; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #2e7d32; color: white; font-weight: bold; }
        @media (max-width: 768px) {
            .admin-sidebar { width: 70px; }
            .sidebar-header h2 span, .sidebar-nav a span, .sidebar-logout a span { display: none; }
            .sidebar-nav a { justify-content: center; padding: 1rem; }
            .admin-main { margin-left: 70px; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="sidebar-header"><h2>Agro<span>City</span></h2></div>
        <nav class="sidebar-nav">
            <ul>
                <?php if ($role == 'owner'): ?>
                    <li><a href="/fertilizer-shop/admin/owner/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                    <li><a href="/fertilizer-shop/admin/operator/inventory.php" class="<?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>"><i class="fas fa-boxes"></i> <span>Inventory</span></a></li>
                    <li><a href="/fertilizer-shop/admin/owner/suppliers.php" class="<?php echo $current_page == 'suppliers.php' ? 'active' : ''; ?>"><i class="fas fa-truck"></i> <span>Suppliers</span></a></li>
                    <li><a href="/fertilizer-shop/admin/owner/users.php" class="<?php echo in_array($current_page, ['users.php','add-user.php','edit-user.php']) ? 'active' : ''; ?>"><i class="fas fa-user-tie"></i> <span>Staff</span></a></li>
                    <li><a href="/fertilizer-shop/admin/cashier/pos.php" class="<?php echo $current_page == 'pos.php' ? 'active' : ''; ?>"><i class="fas fa-cash-register"></i> <span>POS</span></a></li>
                    <li><a href="/fertilizer-shop/admin/cashier/transactions.php" class="<?php echo $current_page == 'transactions.php' ? 'active' : ''; ?>"><i class="fas fa-history"></i> <span>Transactions</span></a></li>
                    <li><a href="/fertilizer-shop/admin/owner/reports.php" class="<?php echo in_array($current_page, ['reports.php','sales_report.php','stock_report.php','orders_report.php','revenue_summary.php']) ? 'active' : ''; ?>"><i class="fas fa-chart-bar"></i> <span>Reports</span></a></li>
                <?php elseif ($role == 'cashier'): ?>
                    <li><a href="/fertilizer-shop/admin/cashier/pos.php" class="<?php echo $current_page == 'pos.php' ? 'active' : ''; ?>"><i class="fas fa-cash-register"></i> <span>POS</span></a></li>
                    <li><a href="/fertilizer-shop/admin/cashier/transactions.php" class="<?php echo $current_page == 'transactions.php' ? 'active' : ''; ?>"><i class="fas fa-history"></i> <span>Transactions</span></a></li>
                <?php elseif ($role == 'operator'): ?>
                    <li><a href="/fertilizer-shop/admin/operator/inventory.php" class="<?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>"><i class="fas fa-edit"></i> <span>Manage Inventory</span></a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="sidebar-logout"><a href="/fertilizer-shop/admin/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></div>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar">
            <div class="page-title"><h1><?php echo ucfirst($role); ?> Dashboard</h1></div>
            <div class="topbar-user">
                <div class="user-info"><div class="user-name"><?php echo htmlspecialchars($_SESSION['admin_user']); ?></div><div class="user-role"><?php echo ucfirst($role); ?></div></div>
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_user'], 0, 1)); ?></div>
            </div>
        </header>
        <div class="admin-content">
