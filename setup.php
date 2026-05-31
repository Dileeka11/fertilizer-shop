<?php
/**
 * One-time setup helper.
 *  - Creates the default owner account (username: owner / password: owner123)
 *    if no owner row exists.
 *  - Creates uploads/ directory if missing.
 *
 * Visit:  http://localhost/fertilizer-shop/setup.php
 * Delete this file after running it in production.
 */
require_once __DIR__ . '/config.php';

$msgs = [];

// uploads dir
$uploadsDir = __DIR__ . '/uploads/products';
if (!is_dir($uploadsDir)) {
    @mkdir($uploadsDir, 0777, true);
    $msgs[] = 'Created uploads/products directory.';
}

// default owner
$exists = Database::scalar("SELECT COUNT(*) FROM staff_users WHERE role='owner'");
if ((int)$exists === 0) {
    User::create([
        'username'  => 'owner',
        'password'  => 'owner123',
        'full_name' => 'Default Owner',
        'email'     => 'owner@agrocity.lk',
        'phone'     => '0761157794',
        'role'      => 'owner',
        'status'    => 'Active',
    ]);
    $msgs[] = "Created default owner — username: <b>owner</b>, password: <b>owner123</b>. Change this immediately.";
}

// default cashier + operator (only if none exist)
if ((int)Database::scalar("SELECT COUNT(*) FROM staff_users WHERE role='cashier'") === 0) {
    User::create([
        'username'  => 'cashier', 'password' => 'cashier123',
        'full_name' => 'Default Cashier', 'email' => 'cashier@agrocity.lk',
        'role'      => 'cashier', 'status' => 'Active',
    ]);
    $msgs[] = "Created default cashier — username: <b>cashier</b>, password: <b>cashier123</b>.";
}
if ((int)Database::scalar("SELECT COUNT(*) FROM staff_users WHERE role='operator'") === 0) {
    User::create([
        'username'  => 'operator', 'password' => 'operator123',
        'full_name' => 'Default Operator', 'email' => 'operator@agrocity.lk',
        'role'      => 'operator', 'status' => 'Active',
    ]);
    $msgs[] = "Created default operator — username: <b>operator</b>, password: <b>operator123</b>.";
}

if (!$msgs) $msgs[] = 'Nothing to do — staff users already exist and uploads/ folder is in place.';

?><!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>AgroCity setup</title>
<style>body{font-family:Arial,sans-serif;background:#f4f7fc;padding:2rem;}
.box{max-width:600px;margin:auto;background:#fff;padding:2rem;border-radius:16px;box-shadow:0 4px 12px rgba(0,0,0,.1);}
h1{color:#1b5e20;} li{margin:.5rem 0;} a{color:#2e7d32;}</style></head>
<body><div class="box">
<h1>AgroCity setup</h1>
<ul><?php foreach ($msgs as $m) echo '<li>' . $m . '</li>'; ?></ul>
<p><a href="<?php echo BASE_URL; ?>/login.php">→ Go to Admin Login</a></p>
<p style="color:#c62828;"><b>Important:</b> delete <code>setup.php</code> after first use.</p>
</div></body></html>
