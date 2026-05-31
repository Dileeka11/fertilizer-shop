<?php
require_once __DIR__ . '/config.php';

if (Auth::isStaff()) {
    redirect(BASE_URL . '/admin/' . $_SESSION['admin_role'] . '/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = Auth::loginStaff(trim((string)($_POST['username'] ?? '')), (string)($_POST['password'] ?? ''));
    if ($res['ok']) {
        redirect(BASE_URL . '/admin/' . $res['role'] . '/dashboard.php');
    }
    $error = $res['error'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Agro City</title>
    <style>
        body { background: #f4f7fc; display: flex; justify-content: center; align-items: center; min-height: 100vh; font-family: 'Inter', sans-serif; }
        .login-box { background: white; padding: 2.5rem; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h1 { color: #1b5e20; }
        h1 span { color: #ffb300; }
        .error { background: #ffebee; color: #c62828; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input { width: 100%; padding: 0.8rem; border: 2px solid #e0e0e0; border-radius: 12px; font-family: inherit; }
        .btn-primary { background: #2e7d32; color: white; padding: 0.8rem 2rem; border: none; border-radius: 50px; cursor: pointer; width: 100%; font-weight: 600; }
        small { color: #757575; display: block; margin-top: 0.3rem; }
        a { color: #2e7d32; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-box">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1>Agro<span>City</span></h1>
            <p>Admin Panel Login</p>
        </div>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="owner / cashier / operator">
                <small>Run /fertilizer-shop/setup.php once to create default accounts.</small>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">Login</button>
        </form>
        <p style="text-align: center; margin-top: 1rem;"><a href="/fertilizer-shop/public/login.php">Customer Login</a></p>
    </div>
</body>
</html>
