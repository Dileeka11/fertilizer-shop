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

    /* Modernized POS surfaces */
    .product-panel .pos-intro-card { background: linear-gradient(135deg, #f1f8f1, #e8f5e9); border: 1px dashed #9ccc9c; border-radius: 16px; padding: 2rem; margin-top: 1.2rem; text-align: center; color: #4a5d4c; }
    .product-panel .pos-intro-card i { font-size: 2.4rem; color: #2e7d32; margin-bottom: 0.6rem; }
    .pos-add-btn { box-shadow: 0 4px 14px rgba(46,125,50,0.25); transition: transform 0.12s, background 0.15s; }
    .pos-add-btn:hover { transform: translateY(-1px); }
    .cart-panel { border: 1px solid #eef2ee; }
    .cart-panel h3 { color: #1b5e20; border-bottom: 2px solid #e8f5e9; padding-bottom: 0.6rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .cart-table th { background: transparent; color: #6b7280; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.4px; }
    .cart-table tbody button { width: 26px; height: 26px; border: none; border-radius: 50%; background: #e8f5e9; color: #1b5e20; cursor: pointer; font-weight: 700; line-height: 1; transition: background 0.15s; }
    .cart-table tbody button:hover { background: #c8e6c9; }
    #cartTotal { font-size: 1.2rem; color: #1b5e20; border-top: 2px dashed #e0e8e0; padding-top: 0.8rem; }

    /* ---- Customer live search ---- */
    .customer-section { margin-bottom: 0.8rem; }
    .customer-search-wrap { position: relative; }
    .customer-results { display: none; position: absolute; left: 0; right: 0; top: calc(100% + 2px); z-index: 50; background: #fff; border: 1px solid #d6e0d6; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); max-height: 260px; overflow-y: auto; }
    .customer-results.show { display: block; }
    .customer-results .cust-item { padding: 0.55rem 0.8rem; cursor: pointer; border-bottom: 1px solid #f0f3f0; }
    .customer-results .cust-item:last-child { border-bottom: none; }
    .customer-results .cust-item:hover, .customer-results .cust-item.active { background: #e8f5e9; }
    .customer-results .cust-name { font-weight: 600; color: #1b5e20; }
    .customer-results .cust-meta { font-size: 0.78rem; color: #757575; margin-top: 0.1rem; }
    .customer-results .cust-empty { padding: 0.7rem 0.8rem; color: #757575; font-size: 0.85rem; }
    .customer-selected { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-top: 0.4rem; padding: 0.45rem 0.7rem; background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 8px; color: #1b5e20; font-weight: 600; font-size: 0.9rem; }
    .customer-selected button { width: 24px; height: 24px; border: none; border-radius: 50%; background: #c8e6c9; color: #1b5e20; cursor: pointer; font-weight: 700; line-height: 1; flex-shrink: 0; }
    .customer-selected button:hover { background: #a5d6a7; }
</style>

<div class="section-header">
    <h1><i class="fas fa-cash-register"></i> Point of Sale</h1>
</div>

<div class="pos-container">
    <div class="product-panel">
        <button id="openPickerBtn" class="pos-add-btn"><i class="fas fa-plus"></i>&nbsp; Add Product to Sale</button>
        <div class="pos-intro-card">
            <i class="fas fa-basket-shopping"></i>
            <p>Click <strong>Add Product to Sale</strong> to open the product picker. Selected items appear in the Current Sale panel.</p>
        </div>
    </div>

    <div class="cart-panel">
        <h3><i class="fas fa-receipt"></i> Current Sale</h3>
        <div class="customer-section">
            <div class="customer-search-wrap">
                <input type="text" id="customerName" autocomplete="off" placeholder="Search customer by name / phone / email (optional)" style="width:100%; padding:0.5rem; border:1px solid #ccc; border-radius:8px;">
                <div id="customerResults" class="customer-results"></div>
            </div>
            <div id="customerSelected" class="customer-selected" style="display:none;">
                <span id="customerSelectedLabel"></span>
                <button type="button" id="customerClear" title="Clear selected customer">&times;</button>
            </div>
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
let selectedCustomer = null; // { customer_no, name, ... } when picked from search

const CUSTOMER_SEARCH_URL = '<?php echo BASE_URL; ?>/ajax/php/customer_search.php';

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

// ---- Customer live search ----
const customerInput     = document.getElementById('customerName');
const customerResults   = document.getElementById('customerResults');
const customerSelected  = document.getElementById('customerSelected');
const customerSelLabel  = document.getElementById('customerSelectedLabel');
let custSearchTimer = null;
let custActiveIdx   = -1;
let custLastResults = [];

function hideCustomerResults() { customerResults.classList.remove('show'); customerResults.innerHTML = ''; custActiveIdx = -1; }

function renderCustomerResults(rows) {
    custLastResults = rows;
    custActiveIdx = -1;
    if (!rows.length) {
        customerResults.innerHTML = '<div class="cust-empty">No matching customers. Type a name to create a new one on sale.</div>';
        customerResults.classList.add('show');
        return;
    }
    customerResults.innerHTML = rows.map((c, i) => {
        const meta = [c.phone, c.email].filter(Boolean).join(' &middot; ');
        return '<div class="cust-item" data-idx="' + i + '">' +
                   '<div class="cust-name">' + escapeHtml(c.name) + '</div>' +
                   (meta ? '<div class="cust-meta">' + escapeHtml(meta).replace('&amp;middot;', '&middot;') + '</div>' : '') +
               '</div>';
    }).join('');
    customerResults.querySelectorAll('.cust-item').forEach(el => {
        el.addEventListener('click', () => pickCustomer(custLastResults[+el.dataset.idx]));
    });
    customerResults.classList.add('show');
}

function pickCustomer(c) {
    selectedCustomer = c;
    customerSelLabel.textContent = c.name + (c.phone ? ' (' + c.phone + ')' : '');
    customerSelected.style.display = 'flex';
    customerInput.value = '';
    customerInput.style.display = 'none';
    hideCustomerResults();
}

function clearSelectedCustomer() {
    selectedCustomer = null;
    customerSelected.style.display = 'none';
    customerInput.style.display = '';
    customerInput.value = '';
    customerInput.focus();
}

function searchCustomers(term) {
    fetch(CUSTOMER_SEARCH_URL + '?action=search&q=' + encodeURIComponent(term), { credentials: 'same-origin' })
        .then(r => r.json())
        .then(d => { if (d.ok) renderCustomerResults(d.data || []); })
        .catch(() => hideCustomerResults());
}

customerInput.addEventListener('input', e => {
    const term = e.target.value.trim();
    clearTimeout(custSearchTimer);
    if (term.length < 2) { hideCustomerResults(); return; }
    custSearchTimer = setTimeout(() => searchCustomers(term), 250);
});

customerInput.addEventListener('keydown', e => {
    const items = customerResults.querySelectorAll('.cust-item');
    if (!customerResults.classList.contains('show') || !items.length) return;
    if (e.key === 'ArrowDown')      { e.preventDefault(); custActiveIdx = Math.min(custActiveIdx + 1, items.length - 1); }
    else if (e.key === 'ArrowUp')   { e.preventDefault(); custActiveIdx = Math.max(custActiveIdx - 1, 0); }
    else if (e.key === 'Enter')     { if (custActiveIdx >= 0) { e.preventDefault(); pickCustomer(custLastResults[custActiveIdx]); } return; }
    else if (e.key === 'Escape')    { hideCustomerResults(); return; }
    else return;
    items.forEach((el, i) => el.classList.toggle('active', i === custActiveIdx));
});

document.getElementById('customerClear').addEventListener('click', clearSelectedCustomer);
document.addEventListener('click', e => {
    if (!e.target.closest('.customer-search-wrap')) hideCustomerResults();
});

document.getElementById('openPickerBtn').addEventListener('click', openProductModal);
modalSearch.addEventListener('input', e => renderProductList(e.target.value));
document.getElementById('clearCartBtn').addEventListener('click', () => { cart = []; clearSelectedCustomer(); renderCart(); });
document.getElementById('checkoutBtn').addEventListener('click', () => {
    if (cart.length === 0) { alert('Cart empty'); return; }
    paymentMdl.classList.add('show');
});
document.getElementById('completeSaleBtn').addEventListener('click', () => {
    const paymentMethod = document.getElementById('paymentMethod').value;
    const payload = { items: cart, payment_method: paymentMethod };
    if (selectedCustomer) {
        payload.customer_no = selectedCustomer.customer_no;
    } else {
        payload.customer_name = customerInput.value.trim();
    }
    fetch('process_sale.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
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
