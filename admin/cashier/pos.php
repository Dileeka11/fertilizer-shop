<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config.php';
include '../includes/admin_header.php';

// Fetch products for POS
$products = $conn->query("SELECT product_no, name, price FROM products ORDER BY name");
$products = $products->fetch_all(MYSQLI_ASSOC);
?>

<style>
    .pos-container { display: flex; gap: 2rem; flex-wrap: wrap; }
    .product-panel { flex: 2; }
    .cart-panel { flex: 1.2; background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); position: sticky; top: 20px; }
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px,1fr)); gap: 1rem; margin-top: 1rem; }
    .product-card { background: white; border: 1px solid #eee; border-radius: 12px; padding: 0.8rem; cursor: pointer; text-align: center; transition: 0.2s; }
    .product-card:hover { background: #f0f0f0; transform: translateY(-2px); }
    .cart-table { width: 100%; border-collapse: collapse; }
    .cart-table th, .cart-table td { padding: 0.5rem; border-bottom: 1px solid #eee; }
    .qty-control button { background: #f0f0f0; border: none; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; }
    .btn-primary, .btn-outline { width: 100%; margin-top: 0.5rem; padding: 0.7rem; border-radius: 50px; text-align: center; display: inline-block; cursor: pointer; }
    .btn-primary { background: #2e7d32; color: white; border: none; }
    .btn-outline { background: transparent; border: 2px solid #2e7d32; color: #2e7d32; }
    .modal-overlay { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index:1000; }
    .modal-content { background: white; padding: 2rem; border-radius: 16px; width: 300px; }
    .search-input { width: 100%; padding: 0.8rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 8px; }
</style>

<h1>Point of Sale</h1>
<div class="pos-container">
    <div class="product-panel">
        <input type="text" id="search" class="search-input" placeholder="Search products...">
        <div id="productGrid" class="product-grid"></div>
    </div>
    <div class="cart-panel">
        <h3>Current Sale</h3>
        <div class="customer-section">
            <input type="text" id="customerName" placeholder="Customer Name (optional)" style="width:100%; margin-bottom:0.5rem; padding:0.5rem;">
        </div>
        <table class="cart-table">
            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr></thead>
            <tbody id="cartBody"></tbody>
        </table>
        <div id="cartTotal" style="text-align:right; font-weight:bold; margin-top:1rem;">Total: Rs. 0</div>
        <button id="checkoutBtn" class="btn-primary">Proceed to Billing</button>
        <button id="clearCartBtn" class="btn-outline">Cancel Sale</button>
    </div>
</div>

<div id="paymentModal" class="modal-overlay">
    <div class="modal-content">
        <h3>Payment</h3>
        <select id="paymentMethod" style="width:100%; padding:0.5rem; margin:1rem 0;">
            <option value="Cash">Cash</option>
            <option value="Card">Card</option>
        </select>
        <button id="completeSaleBtn" class="btn-primary">Complete Sale</button>
        <button id="closeModalBtn" class="btn-outline">Cancel</button>
    </div>
</div>

<script>
const products = <?php echo json_encode($products); ?>;
let cart = [];

function renderProducts() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    let filtered = products;
    if (searchTerm) filtered = products.filter(p => p.name.toLowerCase().includes(searchTerm));
    const grid = document.getElementById('productGrid');
    grid.innerHTML = '';
    filtered.forEach(p => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.onclick = () => addToCart(p);
        card.innerHTML = `<div><strong>${p.name}</strong></div><div>Rs. ${p.price.toFixed(2)}</div>`;
        grid.appendChild(card);
    });
}
function addToCart(product) {
    const existing = cart.find(item => item.product_no === product.product_no);
    if (existing) existing.qty++;
    else cart.push({ ...product, qty: 1 });
    renderCart();
}
function updateQty(product_no, delta) {
    const idx = cart.findIndex(i => i.product_no === product_no);
    if (idx !== -1) {
        cart[idx].qty += delta;
        if (cart[idx].qty <= 0) cart.splice(idx, 1);
        renderCart();
    }
}
function removeItem(product_no) {
    cart = cart.filter(i => i.product_no !== product_no);
    renderCart();
}
function renderCart() {
    let tbody = document.getElementById('cartBody');
    let total = 0;
    tbody.innerHTML = '';
    cart.forEach(item => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        tbody.innerHTML += `<tr>
            <td>${item.name}</td>
            <td><button onclick="updateQty(${item.product_no}, -1)">-</button> ${item.qty} <button onclick="updateQty(${item.product_no}, 1)">+</button></td>
            <td>Rs. ${item.price.toFixed(2)}</td>
            <td>Rs. ${subtotal.toFixed(2)}</td>
            <td><button onclick="removeItem(${item.product_no})">X</button></td>
        </tr>`;
    });
    document.getElementById('cartTotal').innerText = `Total: Rs. ${total.toFixed(2)}`;
}
function clearCart() { cart = []; renderCart(); }
document.getElementById('search').addEventListener('input', renderProducts);
document.getElementById('clearCartBtn').addEventListener('click', clearCart);
document.getElementById('checkoutBtn').addEventListener('click', () => {
    if (cart.length === 0) { alert('Cart empty'); return; }
    document.getElementById('paymentModal').style.display = 'flex';
});
document.getElementById('closeModalBtn').addEventListener('click', () => {
    document.getElementById('paymentModal').style.display = 'none';
});
document.getElementById('completeSaleBtn').addEventListener('click', () => {
    const customerName = document.getElementById('customerName').value.trim();
    const paymentMethod = document.getElementById('paymentMethod').value;
    fetch('process_sale.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ items: cart, customer_name: customerName, payment_method: paymentMethod })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) window.location.href = 'invoice.php?sale_id=' + data.sale_id;
        else alert('Error: ' + data.message);
    })
    .catch(err => alert('Server error'));
});
renderProducts();
renderCart();
</script>

<?php include '../includes/admin_footer.php'; ?>