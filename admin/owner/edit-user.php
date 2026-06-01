<?php
require_once __DIR__ . '/../../partials/auth_check.php';
Auth::requireStaff('owner');

$id   = (int)($_GET['id'] ?? 0);
$user = User::find($id);
if (!$user) {
    redirect(BASE_URL . '/admin/owner/users.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        User::update($id, [
            'full_name' => (string)$_POST['name'],
            'email'     => (string)$_POST['email'],
            'phone'     => (string)($_POST['phone'] ?? ''),
            'role'      => $user['role'] === 'owner' ? 'owner' : (string)$_POST['role'],
            'status'    => (string)$_POST['status'],
            'password'  => (string)($_POST['password'] ?? ''),
        ]);
        redirect(BASE_URL . '/admin/owner/users.php');
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
    $user = User::find($id); // refresh
}
include __DIR__ . '/../../partials/admin_header.php';
?>
<div class="section-header">
    <h1>Edit Staff</h1>
    <a href="users.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<?php if ($error): ?><div style="background:#ffebee;color:#c62828;padding:1rem;border-radius:8px;margin-bottom:1rem;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="admin-card" style="max-width: 600px; margin:0 auto; padding:2rem;">
    <form method="post">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role" <?php echo $user['role'] == 'owner' ? 'disabled' : ''; ?>>
                <option value="cashier"  <?php echo $user['role'] == 'cashier'  ? 'selected' : ''; ?>>Cashier</option>
                <option value="operator" <?php echo $user['role'] == 'operator' ? 'selected' : ''; ?>>Operator</option>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="Active"   <?php echo $user['status'] == 'Active'   ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo $user['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="form-group">
            <label>New Password <small>(leave blank to keep current)</small></label>
            <input type="password" name="password">
        </div>
        <button type="submit" class="btn-primary">Update Staff</button>
    </form>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
