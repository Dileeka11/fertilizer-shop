<?php
require_once __DIR__ . '/../../partials/auth_check.php';
Auth::requireStaff('owner');
include __DIR__ . '/../../partials/admin_header.php';

$staff = User::all();
?>

<div class="section-header">
    <h1><i class="fas fa-user-tie"></i> Staff Management</h1>
    <a href="add-user.php" class="btn-primary"><i class="fas fa-plus"></i> Add New Staff</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($staff as $user):
                $statusClass = $user['status'] == 'Active' ? 'status-active' : 'status-inactive';
            ?>
            <tr>
                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo ucfirst($user['role']); ?></td>
                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $user['status']; ?></span></td>
                <td>
                    <a href="edit-user.php?id=<?php echo (int)$user['user_no']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</a>
                    <?php if ($user['role'] !== 'owner'): ?>
                    <button class="action-btn delete-btn" data-id="<?php echo (int)$user['user_no']; ?>" data-status="<?php echo $user['status']; ?>" onclick="toggleStatus(this)">
                        <i class="fas fa-power-off"></i> <?php echo $user['status'] === 'Active' ? 'Disable' : 'Enable'; ?>
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.status-badge { padding: 0.2rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
.status-active { background: #c8e6c9; color: #2e7d32; }
.status-inactive { background: #ffcdd2; color: #c62828; }
</style>

<script>
function toggleStatus(btn) {
    const id = btn.dataset.id;
    const newStatus = btn.dataset.status === 'Active' ? 'Inactive' : 'Active';
    if (!confirm('Set this user to ' + newStatus + '?')) return;
    const body = new URLSearchParams({ action: 'set_status', user_no: id, status: newStatus });
    fetch('/fertilizer-shop/ajax/php/users.php', { method: 'POST', credentials: 'same-origin', body: body })
        .then(r => r.json())
        .then(res => { if (res.ok) location.reload(); else alert('Error: ' + res.error); });
}
</script>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
