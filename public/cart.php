<?php
require_once __DIR__ . '/../config.php';

// Remove item via ?remove=PRODUCT_NO
if (isset($_GET['remove'])) {
    Cart::remove((int)$_GET['remove']);
    redirect(BASE_URL . '/public/cart.php');
}

// Quick-add another product from the cart page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $addNo  = (int)($_POST['add_product'] ?? 0);
    $addQty = max(1, (int)($_POST['add_qty'] ?? 1));
    if ($addNo > 0) {
        Cart::add($addNo, $addQty);
    }
    redirect(BASE_URL . '/public/cart.php');
}

// Update qty via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    foreach ((array)($_POST['qty'] ?? []) as $no => $q) {
        Cart::update((int)$no, (int)$q);
    }
    redirect(BASE_URL . '/public/cart.php');
}

require_once __DIR__ . '/../partials/public_header.php';
$cart  = Cart::all();
$total = Cart::total();

// Products available to quick-add (exclude what is already in the cart)
$inCart   = array_map(static fn($i) => (int)$i['product_no'], $cart);
$allProds = Product::all(['active_only' => true, 'order_by' => 'p.name']);
$addable  = array_filter($allProds, static fn($p) => !in_array((int)$p['product_no'], $inCart, true));

// Shared resolver handles empty / legacy-corrupt values and missing files.
$placeholder = productImageUrl('');
?>
<style>
    .cart-wrap { max-width: 1200px; margin: 2.5rem auto; padding: 0 1rem; }
    .cart-head { display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem; }
    .cart-head h1 { margin: 0; font-size: 2rem; color: #1b5e20; }
    .cart-head .count-badge { color: #6b7c6e; font-size: 0.95rem; }

    /* Two-column layout: items + summary */
    .cart-layout { display: grid; grid-template-columns: 1fr 340px; gap: 2rem; align-items: start; }
    @media (max-width: 900px) { .cart-layout { grid-template-columns: 1fr; } }

    /* Item cards */
    .cart-items { display: flex; flex-direction: column; gap: 1rem; }
    .cart-item {
        display: grid;
        grid-template-columns: 88px 1fr auto;
        gap: 1.1rem;
        align-items: center;
        background: #fff;
        border: 1px solid #e6ece6;
        border-radius: 16px;
        padding: 1rem 1.2rem;
        box-shadow: 0 4px 14px rgba(27,94,32,0.06);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .cart-item:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(27,94,32,0.12); }
    .cart-item .thumb {
        width: 88px; height: 88px; border-radius: 12px; object-fit: cover;
        background: #f1f6f1; border: 1px solid #e6ece6;
    }
    .cart-item .info .name { font-weight: 700; font-size: 1.08rem; color: #1f2d20; margin: 0 0 0.2rem; }
    .cart-item .info .unit { color: #ff8f00; font-weight: 600; font-size: 0.95rem; }
    .cart-item .info .sub  { color: #6b7c6e; font-size: 0.85rem; margin-top: 0.25rem; }
    .cart-item .controls { display: flex; flex-direction: column; align-items: flex-end; gap: 0.6rem; }

    /* Quantity stepper */
    .qty-stepper { display: inline-flex; align-items: center; border: 1px solid #cfe0cf; border-radius: 50px; overflow: hidden; background: #f7faf7; }
    .qty-stepper button { width: 32px; height: 34px; border: none; background: transparent; font-size: 1.1rem; color: #2e7d32; cursor: pointer; line-height: 1; }
    .qty-stepper button:hover { background: #e3f0e3; }
    .qty-stepper input { width: 44px; height: 34px; text-align: center; border: none; background: transparent; font-weight: 600; font-size: 1rem; -moz-appearance: textfield; }
    .qty-stepper input::-webkit-outer-spin-button, .qty-stepper input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

    .line-total { font-weight: 700; color: #1b5e20; font-size: 1.05rem; }
    .remove-link { color: #c62828; font-size: 0.85rem; }
    .remove-link:hover { text-decoration: underline; }

    /* Summary panel */
    .cart-summary {
        position: sticky; top: 1.5rem;
        background: #fff; border: 1px solid #e6ece6; border-radius: 16px;
        padding: 1.5rem; box-shadow: 0 4px 14px rgba(27,94,32,0.06);
    }
    .cart-summary h2 { margin: 0 0 1rem; font-size: 1.2rem; color: #1b5e20; }
    .summary-row { display: flex; justify-content: space-between; padding: 0.5rem 0; color: #444; }
    .summary-row.grand { border-top: 2px dashed #e0e8e0; margin-top: 0.5rem; padding-top: 0.9rem; font-size: 1.25rem; font-weight: 800; color: #1b5e20; }
    .summary-actions { display: flex; flex-direction: column; gap: 0.7rem; margin-top: 1.2rem; }
    .summary-actions .btn { text-align: center; }
    .btn-block { width: 100%; }

    /* Quick-add panel */
    .quick-add {
        background: linear-gradient(135deg, #f1f8f1 0%, #e8f5e9 100%);
        border: 1px dashed #9ccc9c; border-radius: 16px;
        padding: 1.2rem 1.4rem; margin-bottom: 1.5rem;
    }
    .quick-add h3 { margin: 0 0 0.8rem; color: #1b5e20; display: flex; align-items: center; gap: 0.5rem; font-size: 1.05rem; }
    .quick-add-form { display: flex; gap: 0.7rem; flex-wrap: wrap; align-items: center; }
    .quick-add-form select { flex: 1 1 260px; padding: 0.6rem 0.8rem; border-radius: 10px; border: 1px solid #b6d4b6; background: #fff; }
    .quick-add-form input[type="number"] { width: 80px; padding: 0.6rem; border-radius: 10px; border: 1px solid #b6d4b6; background: #fff; }

    /* Empty state */
    .cart-empty { text-align: center; background: #fff; border: 1px solid #e6ece6; border-radius: 16px; padding: 3.5rem 1.5rem; box-shadow: 0 4px 14px rgba(27,94,32,0.06); }
    .cart-empty i { font-size: 3.5rem; color: #bcd6bc; }
    .cart-empty h2 { color: #1b5e20; margin: 1rem 0 0.4rem; }
    .cart-empty p { color: #6b7c6e; margin-bottom: 1.4rem; }
</style>

<div class="cart-wrap">
    <div class="cart-head">
        <h1><i class="fas fa-shopping-basket"></i> Your Cart</h1>
        <span class="count-badge"><?php echo Cart::count(); ?> item(s) in your cart</span>
    </div>

    <?php if (empty($cart)): ?>
        <div class="cart-empty">
            <i class="fas fa-shopping-cart"></i>
            <h2>Your cart is empty</h2>
            <p>Browse our fertilizers, seeds and tools to get started.</p>
            <a href="products.php" class="btn"><i class="fas fa-store"></i> Continue Shopping</a>
        </div>
    <?php else: ?>

        <?php if (!empty($addable)): ?>
        <div class="quick-add">
            <h3><i class="fas fa-plus-circle"></i> Add another product</h3>
            <form method="post" class="quick-add-form">
                <select name="add_product" required>
                    <option value="" disabled selected>Choose a product to add…</option>
                    <?php foreach ($addable as $p): ?>
                        <option value="<?php echo (int)$p['product_no']; ?>">
                            <?php echo htmlspecialchars($p['name']); ?> — Rs. <?php echo number_format($p['price'], 2); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="add_qty" value="1" min="1" aria-label="Quantity">
                <button type="submit" class="btn"><i class="fas fa-cart-plus"></i> Add to Cart</button>
            </form>
        </div>
        <?php endif; ?>

        <form method="post">
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($cart as $item):
                    $subtotal = (float)$item['price'] * (int)$item['qty'];
                    $no = (int)$item['product_no'];
                ?>
                <div class="cart-item">
                    <img class="thumb" src="<?php echo productImageUrl($item['image'] ?? ''); ?>"
                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                         onerror="this.src='<?php echo $placeholder; ?>'">
                    <div class="info">
                        <p class="name"><?php echo htmlspecialchars($item['name']); ?></p>
                        <span class="unit">Rs. <?php echo number_format($item['price'], 2); ?> each</span>
                        <div class="sub">Subtotal: Rs. <?php echo number_format($subtotal, 2); ?></div>
                    </div>
                    <div class="controls">
                        <div class="qty-stepper">
                            <button type="button" aria-label="Decrease" onclick="stepQty(this,-1)">&minus;</button>
                            <input type="number" name="qty[<?php echo $no; ?>]" value="<?php echo (int)$item['qty']; ?>" min="1" onchange="document.getElementById('cart-form-submit') && null">
                            <button type="button" aria-label="Increase" onclick="stepQty(this,1)">&plus;</button>
                        </div>
                        <span class="line-total">Rs. <?php echo number_format($subtotal, 2); ?></span>
                        <a href="cart.php?remove=<?php echo $no; ?>" class="remove-link"><i class="fas fa-trash-alt"></i> Remove</a>
                    </div>
                </div>
                <?php endforeach; ?>

                <div style="margin-top:0.5rem;">
                    <button type="submit" name="update" value="1" class="btn-outline" style="padding:0.6rem 1.4rem; border-radius:50px;"><i class="fas fa-sync-alt"></i> Update Quantities</button>
                </div>
            </div>

            <aside class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-row"><span>Items</span><span><?php echo Cart::count(); ?></span></div>
                <div class="summary-row"><span>Subtotal</span><span>Rs. <?php echo number_format($total, 2); ?></span></div>
                <div class="summary-row"><span>Delivery</span><span>Calculated at checkout</span></div>
                <div class="summary-row grand"><span>Total</span><span>Rs. <?php echo number_format($total, 2); ?></span></div>
                <div class="summary-actions">
                    <a href="checkout.php" class="btn btn-block"><i class="fas fa-lock"></i> Proceed to Checkout</a>
                    <a href="products.php" class="btn-outline btn-block" style="text-align:center; border-radius:50px;"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                </div>
            </aside>
        </div>
        </form>
    <?php endif; ?>
</div>

<script id="cart-qty-stepper">
function stepQty(btn, delta) {
    const input = btn.parentElement.querySelector('input[type="number"]');
    const next  = Math.max(1, (parseInt(input.value, 10) || 1) + delta);
    input.value = next;
}
</script>

<?php require_once __DIR__ . '/../partials/public_footer.php'; ?>
