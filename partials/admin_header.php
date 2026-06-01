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
        .stat-card { background: #fff; border-radius: 16px; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 5px solid #2e7d32; transition: transform 0.15s ease, box-shadow 0.15s ease; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(27,94,32,0.14); }
        .stat-card h3 { font-size: 0.95rem; color: #6b7280; font-weight: 600; margin-bottom: 0.25rem; }
        .stat-card .stat-icon { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #fff; background: #2e7d32; }
        .stat-number { font-size: 2rem; font-weight: 700; color: #2e7d32; }
        /* Accent variants */
        .stat-card.accent-green  { border-left-color: #2e7d32; } .stat-card.accent-green  .stat-icon { background: linear-gradient(135deg,#2e7d32,#43a047); }
        .stat-card.accent-amber  { border-left-color: #ff8f00; } .stat-card.accent-amber  .stat-icon { background: linear-gradient(135deg,#ff8f00,#ffb300); } .stat-card.accent-amber .stat-number { color: #ef6c00; }
        .stat-card.accent-blue   { border-left-color: #1976d2; } .stat-card.accent-blue   .stat-icon { background: linear-gradient(135deg,#1565c0,#1e88e5); } .stat-card.accent-blue .stat-number { color: #1565c0; }
        .stat-card.accent-purple { border-left-color: #7b1fa2; } .stat-card.accent-purple .stat-icon { background: linear-gradient(135deg,#6a1b9a,#8e24aa); } .stat-card.accent-purple .stat-number { color: #6a1b9a; }
        .stat-card.accent-red    { border-left-color: #c62828; } .stat-card.accent-red    .stat-icon { background: linear-gradient(135deg,#b71c1c,#e53935); } .stat-card.accent-red .stat-number { color: #c62828; }

        /* Section headings */
        .admin-content h2 { color: #1b5e20; margin-bottom: 1rem; }

        /* Chart layout */
        .chart-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
        .chart-card { background: #fff; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .chart-card h3 { color: #1b5e20; font-size: 1.05rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .chart-card canvas { max-height: 320px; }
        @media (max-width: 1024px) { .chart-grid { grid-template-columns: 1fr; } }
        .btn-primary { background: #2e7d32; color: white; padding: 0.8rem 2rem; border: none; border-radius: 50px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-outline { background: transparent; border: 2px solid #2e7d32; color: #2e7d32; padding: 0.6rem 1.5rem; border-radius: 50px; cursor: pointer; text-decoration: none; display: inline-block; }
        .table-container { overflow-x: auto; background: white; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eef2ee; }
        th { background: #2e7d32; color: white; font-weight: 600; font-size: 0.85rem; letter-spacing: 0.4px; text-transform: uppercase; }
        .table-container table thead th:first-child { border-top-left-radius: 16px; }
        .table-container table thead th:last-child  { border-top-right-radius: 16px; }
        .table-container tbody tr { transition: background 0.12s; }
        .table-container tbody tr:nth-child(even) { background: #fafdfa; }
        .table-container tbody tr:hover { background: #eef7ef; }
        .table-container tbody tr:last-child td { border-bottom: none; }

        /* ---- Shared modern components (same colors, richer styling) ---- */
        .section-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
        .section-header h1, .section-header h2 { color: #1b5e20; margin: 0; }

        .btn-primary, .btn-outline { transition: background 0.15s, color 0.15s, transform 0.12s, box-shadow 0.15s; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { box-shadow: 0 4px 12px rgba(46,125,50,0.25); }
        .btn-primary:hover { background: #1b5e20; transform: translateY(-1px); }
        .btn-outline:hover { background: #2e7d32; color: #fff !important; }

        .action-btn { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.42rem 0.9rem; border-radius: 50px; font-size: 0.82rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: transform 0.12s, filter 0.15s; }
        .action-btn:hover { transform: translateY(-1px); filter: brightness(0.96); text-decoration: none; }
        .edit-btn { background: #fff3e0; color: #ef6c00; }
        .delete-btn { background: #ffebee; color: #c62828; }

        .status-badge { display: inline-block; padding: 0.22rem 0.85rem; border-radius: 50px; font-size: 0.76rem; font-weight: 700; letter-spacing: 0.3px; }

        /* Forms inside the admin content area */
        .admin-content input[type="text"], .admin-content input[type="email"], .admin-content input[type="tel"],
        .admin-content input[type="number"], .admin-content input[type="date"], .admin-content input[type="password"],
        .admin-content select, .admin-content textarea {
            width: 100%; padding: 0.65rem 0.85rem; border: 1px solid #d4ddd4; border-radius: 10px;
            font-family: inherit; font-size: 0.95rem; background: #fff; transition: border-color 0.15s, box-shadow 0.15s;
        }
        .admin-content input:focus, .admin-content select:focus, .admin-content textarea:focus {
            outline: none; border-color: #2e7d32; box-shadow: 0 0 0 3px rgba(46,125,50,0.12);
        }
        .form-group { margin-bottom: 1.1rem; }
        .form-group label { display: block; margin-bottom: 0.35rem; font-weight: 600; color: #1b5e20; }

        /* Card surface used by forms/panels */
        .admin-card { background: #fff; border-radius: 18px; box-shadow: 0 6px 20px rgba(27,94,32,0.07); border: 1px solid #eef2ee; }

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
