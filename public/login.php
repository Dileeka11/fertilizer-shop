<?php
require_once __DIR__ . '/../config.php';

if (Auth::isCustomer()) {
    redirect(BASE_URL . '/public/account/dashboard.php');
}

$error = '';
$justRegistered = isset($_GET['registered']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = Auth::loginCustomer(trim((string)($_POST['email'] ?? '')), (string)($_POST['password'] ?? ''));
    if ($res['ok']) redirect(BASE_URL . '/public/account/dashboard.php');
    $error = $res['error'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Customer Login - Agro City</title>
    <style>
        body { background: #f4f7fc; display: flex; justify-content: center; align-items: center; min-height: 100vh; font-family: Arial, sans-serif; }
        .login-container { background: white; padding: 2.5rem; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h1 { color: #1b5e20; }
        .error { background: #ffebee; color: #c62828; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem; }
        .success { background: #c8e6c9; color: #1b5e20; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; text-align: left; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; }
        button { background: #2e7d32; color: white; padding: 0.8rem; border: none; border-radius: 50px; cursor: pointer; width: 100%; font-size: 1rem; }
        .register-link { margin-top: 1rem; }
        a { color: #2e7d32; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Customer Login</h1>
        <?php if ($justRegistered): ?><div class="success">Registration successful — please log in.</div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <button type="submit">Login</button>
        </form>
        <div class="register-link"><a href="register.php">Register</a> | <a href="/fertilizer-shop/login.php">Admin Login</a></div>
    </div>
</body>
</html>
