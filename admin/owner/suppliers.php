<?php
require_once __DIR__ . '/../../partials/auth_check.php';
include __DIR__ . '/../../partials/admin_header.php';

$suppliers = Supplier::all();
?>

<h1>Suppliers</h1>

<div class="section-header">
    <h2>Manage Suppliers</h2>
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
                    <textarea id="products" rows="2" placeholder="e.g., Fertilizers, Seeds, Tools"></textarea>
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
.modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-content { background: white; border-radius: 16px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #eee; }
.modal-header h2 { margin: 0; color: #2e7d32; }
.close { font-size: 1.5rem; cursor: pointer; color: #666; }
.close:hover { color: #000; }
.modal-body { padding: 1.5rem; }
.form-buttons { display: flex; gap: 1rem; margin-top: 1rem; }
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
        products_supplied: productsField.value.trim(),
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
        productsField.value     = s.products_supplied || '';
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
