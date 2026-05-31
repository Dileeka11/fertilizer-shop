<?php
require_once __DIR__ . '/../config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = Auth::registerCustomer($_POST);
    if ($res['ok']) {
        redirect(BASE_URL . '/public/login.php?registered=1');
    }
    $error = $res['error'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register - Agro City</title>
    <style>
        body { background: #f4f7fc; font-family: Arial, sans-serif; padding: 2rem 0; }
        .register-container { background: white; padding: 2.5rem; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 500px; margin: 0 auto; }
        h1 { color: #1b5e20; }
        .error { background: #ffebee; color: #c62828; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input, textarea { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; }
        button { background: #2e7d32; color: white; padding: 0.8rem; border: none; border-radius: 50px; cursor: pointer; width: 100%; }
        a { color: #2e7d32; text-decoration: none; }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Create Account</h1>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Phone</label><input type="tel" name="phone"></div>
            <div class="form-group"><label>Address</label><textarea name="address"></textarea></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
