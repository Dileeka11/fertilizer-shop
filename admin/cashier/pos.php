<?php
require_once __DIR__ . '/../../partials/auth_check.php';
include __DIR__ . '/../../partials/admin_header.php';

$products = Product::all(['active_only' => true]);
?>

<style>
    .pos-container { display: flex; gap: 2rem; flex-wrap: wrap; }
    .product-panel { flex: 2; min-width: 320px; }
    .cart-panel { flex: 1.2; background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); position: sticky; top: 20px; }
    .cart-table { width: 100%; border-collapse: collapse; }
    .cart-table th, .cart-table td { padding: 0.5rem; border-bottom: 1px solid #eee; }
    .btn-primary, .btn-outline { width: 100%; margin-top: 0.5rem; padding: 0.7rem; border-radius: 50px; text-align: center; display: inline-block; cursor: pointer; border: none; font-weight: 600; }
    .btn-primary { background: #2e7d32; color: white; }
    .btn-outline { background: transparent; border: 2px solid #2e7d32; color: #2e7d32; }
    .pos-add-btn { background: #2e7d32; color: #fff; border: none; padding: 1rem 1.5rem; border-radius: 50px; cursor: pointer; font-size: 1.05rem; font-weight: 600; }
    .pos-add-btn:hover { background: #1b5e20; }

    /* ---- Modal ---- */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); justify-content: center; align-items: center; z-index: 1000; }
    .modal-overlay.show { display: flex; }
    .modal-content { background: white; border-radius: 16px; width: 92%; max-width: 720px; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 12px 40px rgba(0,0,0,0.3); }
    .modal-header { background: #2e7d32; color: #fff; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { margin: 0; }
    .modal-header .close-x { font-size: 1.5rem; cursor: pointer; }
    .modal-body { padding: 1.2rem 1.5rem; overflow-y: auto; }
    .modal-search { width: 100%; padding: 0.7rem 1rem; border: 1px solid #ccc; border-radius: 50px; margin-bottom: 1rem; font-size: 1rem; }
    .product-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.8rem; }
    .product-card { background: #f8faf8; border: 1px solid #e0e0e0; border-radius: 12px; padding: 0.8rem; cursor: pointer; text-align: center; transition: 0.15s; }
    .product-card:hover { background: #e8f5e9; border-color: #2e7d32; transform: translateY(-2px); }
    .product-card.out-of-stock { opacity: 0.5; cursor: not-allowed; }
    .product-card .pname { font-weight: 600; color: #1b5e20; }
    .product-card .pprice { color: #ff8f00; font-weight: 700; margin-top: 0.3rem; }
    .product-card .pstock { font-size: 0.78rem; color: #757575; margin-top: 0.2rem; }
    .modal-empty { text-align: center; padding: 2rem; color: #757575; }
</style>

<h1>Point of Sale</h1>

<div class="pos-container">
    <div class="product-panel">
        <button id="openPickerBtn" class="pos-add-btn"><i class="fas fa-plus"></i>&nbsp; Add Product to Sale</button>
        <p style="margin-top:1rem; color:#757575;">Click the button to open the product picker. Selected items appear in the Current Sale panel.</p>
    </div>

    <div class="cart-panel">
        <h3>Current Sale</h3>
        <div class="customer-section">
            <input type="text" id="customerName" placeholder="Customer Name (optional)" style="width:100%; margin-bottom:0.5rem; padding:0.5rem; border:1px solid #ccc; border-radius:8px;">
        </div>
        <table class="cart-table">
            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr></thead>
            <tbody id="cartBody"></tbody>
        </table>
        <div id="cartTotal" style="text-align:right; font-weight:bold; margin-top:1rem;">Total: Rs. 0.00</div>
        <button id="checkoutBtn" class="btn-primary">Proceed to Billing</button>
        <button id="clearCartBtn" class="btn-outline">Cancel Sale</button>
    </div>
</div>

<!-- Product picker modal -->
<div id="productModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Select a Product</h3>
            <span class="close-x" onclick="closeProductModal()">&times;</span>
        </div>
        <div class="modal-body">
            <input type="text" id="modalSearch" class="modal-search" placeholder="Search products by name or ID...">
            <div id="productList" class="product-list"></div>
        </div>
    </div>
</div>

<!-- Payment modal -->
<div id="paymentModal" class="modal-overlay">
    <div class="modal-content" style="max-width:380px;">
        <div class="modal-header">
            <h3>Payment</h3>
            <span class="close-x" onclick="closePaymentModal()">&times;</span>
        </div>
        <div class="modal-body">
            <label style="font-weight:600;">Payment Method</label>
            <select id="paymentMethod" style="width:100%; padding:0.6rem; margin:0.6rem 0 1rem; border:1px solid #ccc; border-radius:8px;">
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
            </select>
            <button id="completeSaleBtn" class="btn-primary">Complete Sale</button>
            <button class="btn-outline" onclick="closePaymentModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
const products = <?php echo json_encode(array_map(function($p){
    return [
        'product_no' => (int)$p['product_no'],
        'product_id' => $p['product_id'],
        'name'       => $p['name'],
        'price'      => (float)$p['price'],
        'stock'      => (int)$p['stock'],
    ];
}, $products)); ?>;
let cart = [];

const modal       = document.getElementById('productModal');
const paymentMdl  = document.getElementById('paymentModal');
const modalSearch = document.getElementById('modalSearch');
const productList = document.getElementById('productList');

function openProductModal() {
    modalSearch.value = '';
    renderProductList('');
    modal.classList.add('show');
    setTimeout(() => modalSearch.focus(), 50);
}
function closeProductModal()  { modal.classList.remove('show'); }
function closePaymentModal()  { paymentMdl.classList.remove('show'); }

function renderProductList(term) {
    term = (term || '').toLowerCase();
    const filtered = products.filter(p =>
        !term || p.name.toLowerCase().includes(term) || (p.product_id || '').toLowerCase().includes(term)
    );
    productList.innerHTML = '';
    if (filtered.length === 0) {
        productList.innerHTML = '<div class="modal-empty">No products match. Try a different search.</div>';
        return;
    }
    filtered.forEach(p => {
        const card = document.createElement('div');
        const outOfStock = p.stock <= 0;
        card.className = 'product-card' + (outOfStock ? ' out-of-stock' : '');
        card.innerHTML =
            '<div class="pname">' + escapeHtml(p.name) + '</div>' +
            '<div class="pprice">Rs. ' + p.price.toFixed(2) + '</div>' +
            '<div class="pstock">Stock: ' + p.stock + '</div>';
        if (!outOfStock) {
            card.onclick = () => { addToCart(p); closeProductModal(); };
        }
        productList.appendChild(card);
    });
}

function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

function addToCart(product) {
    const existing = cart.find(item => item.product_no === product.product_no);
    const currentQty = existing ? existing.qty : 0;
    if (currentQty + 1 > product.stock) {
        alert('Only ' + product.stock + ' in stock for "' + product.name + '". Cannot add more.');
        return;
    }
    if (existing) existing.qty++;
    else cart.push({ product_no: product.product_no, name: product.name, price: product.price, qty: 1, stock: product.stock });
    renderCart();
}
function updateQty(product_no, delta) {
    const idx = cart.findIndex(i => i.product_no === product_no);
    if (idx !== -1) {
        const next = cart[idx].qty + delta;
        if (next > cart[idx].stock) {
            alert('Only ' + cart[idx].stock + ' in stock for "' + cart[idx].name + '".');
            return;
        }
        cart[idx].qty = next;
        if (cart[idx].qty <= 0) cart.splice(idx, 1);
        renderCart();
    }
}
function removeItem(product_no) { cart = cart.filter(i => i.product_no !== product_no); renderCart(); }

function renderCart() {
    const tbody = document.getElementById('cartBody');
    let total = 0;
    tbody.innerHTML = '';
    cart.forEach(item => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        tbody.innerHTML += '<tr>' +
            '<td>' + escapeHtml(item.name) + '</td>' +
            '<td><button onclick="updateQty(' + item.product_no + ', -1)">-</button> ' + item.qty + ' <button onclick="updateQty(' + item.product_no + ', 1)">+</button></td>' +
            '<td>Rs. ' + item.price.toFixed(2) + '</td>' +
            '<td>Rs. ' + subtotal.toFixed(2) + '</td>' +
            '<td><button onclick="removeItem(' + item.product_no + ')">X</button></td>' +
        '</tr>';
    });
    document.getElementById('cartTotal').innerText = 'Total: Rs. ' + total.toFixed(2);
}

document.getElementById('openPickerBtn').addEventListener('click', openProductModal);
modalSearch.addEventListener('input', e => renderProductList(e.target.value));
document.getElementById('clearCartBtn').addEventListener('click', () => { cart = []; renderCart(); });
document.getElementById('checkoutBtn').addEventListener('click', () => {
    if (cart.length === 0) { alert('Cart empty'); return; }
    paymentMdl.classList.add('show');
});
document.getElementById('completeSaleBtn').addEventListener('click', () => {
    const customerName = document.getElementById('customerName').value.trim();
    const paymentMethod = document.getElementById('paymentMethod').value;
    fetch('process_sale.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ items: cart, customer_name: customerName, payment_method: paymentMethod })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) window.location.href = 'invoice.php?sale_id=' + data.sale_id;
        else alert('Error: ' + (data.message || 'unknown'));
    })
    .catch(() => alert('Server error'));
});

// Click outside modal closes it
[modal, paymentMdl].forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.remove('show'); }));

renderCart();
</script>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
