<?php
require_once __DIR__ . '/../../partials/auth_check.php';
include __DIR__ . '/../../partials/admin_header.php';

$suppliers  = Supplier::all();
$categories = Category::all();
?>

<div class="section-header">
    <h1><i class="fas fa-truck"></i> Suppliers</h1>
    <button id="addSupplierBtn" class="btn-primary"><i class="fas fa-plus"></i> Add New Supplier</button>
</div>

<div class="table-container">
    <table id="suppliersTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact Person</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Products Supplied</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="suppliersTableBody">
            <?php foreach ($suppliers as $s): ?>
            <tr data-id="<?php echo (int)$s['supplier_no']; ?>">
                <td><?php echo htmlspecialchars($s['supplier_id']); ?></td>
                <td><?php echo htmlspecialchars($s['company_name']); ?></td>
                <td><?php echo htmlspecialchars($s['contact_person']); ?></td>
                <td><?php echo htmlspecialchars($s['phone']); ?></td>
                <td><?php echo htmlspecialchars($s['email']); ?></td>
                <td><?php echo htmlspecialchars($s['products_supplied']); ?></td>
                <td><?php echo htmlspecialchars($s['address']); ?></td>
                <td>
                    <button class="action-btn edit-btn" onclick="editSupplier(<?php echo (int)$s['supplier_no']; ?>)"><i class="fas fa-edit"></i> Edit</button>
                    <button class="action-btn delete-btn" onclick="deleteSupplier(<?php echo (int)$s['supplier_no']; ?>)"><i class="fas fa-trash"></i> Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Add/Edit Supplier -->
<div id="supplierModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Supplier</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="supplierForm">
                <div class="form-group">
                    <label>Supplier Name</label>
                    <input type="text" id="supplierName" required>
                </div>
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" id="contactPerson" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" id="phone" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="email" required>
                </div>
                <div class="form-group">
                    <label>Products Supplied</label>
                    <select id="products" multiple size="<?php echo max(3, min(6, count($categories))); ?>">
                        <?php foreach ($categories as $c): ?>
                        <option value="<?php echo htmlspecialchars($c['category_name']); ?>"><?php echo htmlspecialchars($c['category_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#777;">Hold Ctrl (Cmd on Mac) to select multiple categories.</small>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea id="address" rows="2" placeholder="Supplier address"></textarea>
                </div>
                <input type="hidden" id="supplierId" value="">
                <div class="form-buttons">
                    <button type="submit" class="btn-primary">Save Supplier</button>
                    <button type="button" class="btn-outline" id="cancelBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.55); z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-content { background: white; border-radius: 18px; width: 90%; max-width: 600px; max-height: 90vh; overflow: hidden; box-shadow: 0 16px 48px rgba(0,0,0,0.3); animation: modalPop 0.18s ease; }
@keyframes modalPop { from { opacity: 0; transform: translateY(12px) scale(0.98); } to { opacity: 1; transform: none; } }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.1rem 1.5rem; background: linear-gradient(135deg, #1b5e20, #2e7d32); color: #fff; }
.modal-header h2 { margin: 0; color: #fff; font-size: 1.2rem; }
.close { font-size: 1.6rem; cursor: pointer; color: #fff; opacity: 0.85; line-height: 1; }
.close:hover { opacity: 1; }
.modal-body { padding: 1.5rem; max-height: 70vh; overflow-y: auto; }
.form-buttons { display: flex; gap: 1rem; margin-top: 1.2rem; }
</style>

<script src="/fertilizer-shop/ajax/js/suppliers.js"></script>
<script>
const modal = document.getElementById('supplierModal');
const modalTitle = document.getElementById('modalTitle');
const supplierForm = document.getElementById('supplierForm');
const supplierIdField = document.getElementById('supplierId');
const supplierNameField = document.getElementById('supplierName');
const contactPersonField = document.getElementById('contactPerson');
const phoneField = document.getElementById('phone');
const emailField = document.getElementById('email');
const productsField = document.getElementById('products');
const addressField = document.getElementById('address');
let editMode = false;

// Read the multi-select as a comma-separated string.
function getSelectedProducts() {
    return Array.from(productsField.selectedOptions).map(function(o) { return o.value; }).join(', ');
}
// Pre-select options that match the stored comma-separated string.
function setSelectedProducts(value) {
    const chosen = (value || '').split(',').map(function(s) { return s.trim().toLowerCase(); });
    Array.from(productsField.options).forEach(function(o) {
        o.selected = chosen.indexOf(o.value.trim().toLowerCase()) !== -1;
    });
}

document.getElementById('addSupplierBtn').onclick = function() {
    editMode = false;
    modalTitle.innerText = 'Add New Supplier';
    supplierForm.reset();
    supplierIdField.value = '';
    modal.style.display = 'flex';
};
function closeModal() { modal.style.display = 'none'; }
document.querySelector('.close').onclick = closeModal;
document.getElementById('cancelBtn').onclick = closeModal;
window.onclick = function(event) { if (event.target == modal) closeModal(); };

supplierForm.onsubmit = function(e) {
    e.preventDefault();
    const data = {
        company_name:      supplierNameField.value.trim(),
        contact_person:    contactPersonField.value.trim(),
        phone:             phoneField.value.trim(),
        email:             emailField.value.trim(),
        products_supplied: getSelectedProducts(),
        address:           addressField.value.trim(),
        status:            'Active'
    };
    const promise = editMode
        ? AgroSuppliers.update(parseInt(supplierIdField.value, 10), data)
        : AgroSuppliers.create(data);
    promise.then(function(res) {
        if (!res.ok) { alert('Error: ' + (res.error || 'unknown')); return; }
        location.reload();
    });
};

window.editSupplier = function(id) {
    AgroSuppliers.get(id).then(function(res) {
        if (!res.ok || !res.data) return;
        const s = res.data;
        editMode = true;
        supplierIdField.value   = s.supplier_no;
        supplierNameField.value = s.company_name || '';
        contactPersonField.value= s.contact_person || '';
        phoneField.value        = s.phone || '';
        emailField.value        = s.email || '';
        setSelectedProducts(s.products_supplied || '');
        addressField.value      = s.address || '';
        modalTitle.innerText    = 'Edit Supplier';
        modal.style.display = 'flex';
    });
};

window.deleteSupplier = function(id) {
    if (!confirm('Are you sure you want to delete this supplier?')) return;
    AgroSuppliers.remove(id).then(function(res) {
        if (!res.ok) { alert('Error: ' + (res.error || 'unknown')); return; }
        const row = document.querySelector('tr[data-id="' + id + '"]');
        if (row) row.remove();
    });
};
</script>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
