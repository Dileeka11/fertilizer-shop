<?php
require_once __DIR__ . '/../../partials/auth_check.php';
include __DIR__ . '/../../partials/admin_header.php';

$categories = Category::all();
?>

<div class="section-header">
    <h1><i class="fas fa-tags"></i> Categories</h1>
    <button id="addCategoryBtn" class="btn-primary"><i class="fas fa-plus"></i> Add New Category</button>
</div>

<div class="table-container">
    <table id="categoriesTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="categoriesTableBody">
            <?php foreach ($categories as $c): ?>
            <tr data-id="<?php echo (int)$c['category_id']; ?>">
                <td><?php echo (int)$c['category_id']; ?></td>
                <td><?php echo htmlspecialchars($c['category_name']); ?></td>
                <td><?php echo htmlspecialchars($c['slug']); ?></td>
                <td>
                    <button class="action-btn edit-btn" onclick="editCategory(<?php echo (int)$c['category_id']; ?>)"><i class="fas fa-edit"></i> Edit</button>
                    <button class="action-btn delete-btn" onclick="deleteCategory(<?php echo (int)$c['category_id']; ?>)"><i class="fas fa-trash"></i> Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$categories): ?>
            <tr id="noCategoriesRow"><td colspan="4" style="text-align:center; color:#777;">No categories yet. Add one to get started.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Add/Edit Category -->
<div id="categoryModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Category</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="categoryForm">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" id="categoryName" placeholder="e.g., Fertilizers" required>
                </div>
                <input type="hidden" id="categoryId" value="">
                <div class="form-buttons">
                    <button type="submit" class="btn-primary">Save Category</button>
                    <button type="button" class="btn-outline" id="cancelBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.55); z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-content { background: white; border-radius: 18px; width: 90%; max-width: 500px; max-height: 90vh; overflow: hidden; box-shadow: 0 16px 48px rgba(0,0,0,0.3); animation: modalPop 0.18s ease; }
@keyframes modalPop { from { opacity: 0; transform: translateY(12px) scale(0.98); } to { opacity: 1; transform: none; } }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.1rem 1.5rem; background: linear-gradient(135deg, #1b5e20, #2e7d32); color: #fff; }
.modal-header h2 { margin: 0; color: #fff; font-size: 1.2rem; }
.close { font-size: 1.6rem; cursor: pointer; color: #fff; opacity: 0.85; line-height: 1; }
.close:hover { opacity: 1; }
.modal-body { padding: 1.5rem; max-height: 70vh; overflow-y: auto; }
.form-buttons { display: flex; gap: 1rem; margin-top: 1.2rem; }
</style>

<script src="/fertilizer-shop/ajax/js/categories.js"></script>
<script>
const modal = document.getElementById('categoryModal');
const modalTitle = document.getElementById('modalTitle');
const categoryForm = document.getElementById('categoryForm');
const categoryIdField = document.getElementById('categoryId');
const categoryNameField = document.getElementById('categoryName');
let editMode = false;

document.getElementById('addCategoryBtn').onclick = function() {
    editMode = false;
    modalTitle.innerText = 'Add New Category';
    categoryForm.reset();
    categoryIdField.value = '';
    modal.style.display = 'flex';
};
function closeModal() { modal.style.display = 'none'; }
document.querySelector('.close').onclick = closeModal;
document.getElementById('cancelBtn').onclick = closeModal;
window.onclick = function(event) { if (event.target == modal) closeModal(); };

categoryForm.onsubmit = function(e) {
    e.preventDefault();
    const data = { category_name: categoryNameField.value.trim() };
    const promise = editMode
        ? AgroCategories.update(parseInt(categoryIdField.value, 10), data)
        : AgroCategories.create(data);
    promise.then(function(res) {
        if (!res.ok) { alert('Error: ' + (res.error || 'unknown')); return; }
        location.reload();
    });
};

window.editCategory = function(id) {
    AgroCategories.get(id).then(function(res) {
        if (!res.ok || !res.data) return;
        const c = res.data;
        editMode = true;
        categoryIdField.value   = c.category_id;
        categoryNameField.value = c.category_name || '';
        modalTitle.innerText    = 'Edit Category';
        modal.style.display = 'flex';
    });
};

window.deleteCategory = function(id) {
    if (!confirm('Are you sure you want to delete this category?')) return;
    AgroCategories.remove(id).then(function(res) {
        if (!res.ok) { alert('Error: ' + (res.error || 'unknown')); return; }
        const row = document.querySelector('tr[data-id="' + id + '"]');
        if (row) row.remove();
    });
};
</script>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
