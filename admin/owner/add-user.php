<?php
require_once __DIR__ . '/../../partials/auth_check.php';
Auth::requireStaff('owner');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = strtolower(preg_replace('/[^a-z0-9_.]/i', '', explode(' ', trim((string)$_POST['name']))[0]));
        User::create([
            'username'  => $username !== '' ? $username : ('user' . time()),
            'password'  => (string)$_POST['password'],
            'full_name' => (string)$_POST['name'],
            'email'     => (string)$_POST['email'],
            'role'      => (string)$_POST['role'],
            'status'    => (string)($_POST['status'] ?? 'Active'),
        ]);
        redirect(BASE_URL . '/admin/owner/users.php');
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
include __DIR__ . '/../../partials/admin_header.php';
?>
<div class="section-header">
    <h1>Add New Staff</h1>
    <a href="users.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<?php if ($error): ?><div style="background:#ffebee;color:#c62828;padding:1rem;border-radius:8px;margin-bottom:1rem;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div style="max-width: 600px; margin:0 auto; background:#fff; padding:2rem; border-radius:20px;">
    <form action="add-user.php" method="post">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="cashier">Cashier</option>
                <option value="operator">Operator</option>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn-primary">Create Staff</button>
    </form>
</div>
<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
