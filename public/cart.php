<?php
require_once __DIR__ . '/../config.php';

// Remove item via ?remove=PRODUCT_NO
if (isset($_GET['remove'])) {
    Cart::remove((int)$_GET['remove']);
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
?>
<style>
    .cart-table { width: 100%; border-collapse: collapse; }
    .cart-table th, .cart-table td { padding: 0.5rem; border-bottom: 1px solid #ddd; }
    .btn { background: #2e7d32; color: white; padding: 0.5rem 1rem; border: none; border-radius: 50px; cursor: pointer; text-decoration: none; display: inline-block; }
</style>
<div class="container">
    <h1>Your Cart</h1>
    <?php if (empty($cart)): ?>
        <p>Cart is empty. <a href="products.php">Continue shopping</a></p>
    <?php else: ?>
        <form method="post">
        <table class="cart-table">
            <thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($cart as $item):
                    $subtotal = (float)$item['price'] * (int)$item['qty'];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><input type="number" name="qty[<?php echo (int)$item['product_no']; ?>]" value="<?php echo (int)$item['qty']; ?>" min="1" style="width:60px;"></td>
                    <td>Rs. <?php echo number_format($item['price'],2); ?></td>
                    <td>Rs. <?php echo number_format($subtotal,2); ?></td>
                    <td><a href="cart.php?remove=<?php echo (int)$item['product_no']; ?>" class="btn">Remove</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot><tr><td colspan="3"><strong>Total</strong></td><td><strong>Rs. <?php echo number_format($total,2); ?></strong></td><td></td></tr></tfoot>
        </table>
        <div style="margin-top:1rem; display:flex; gap:1rem;">
            <button type="submit" name="update" value="1" class="btn">Update</button>
            <a href="checkout.php" class="btn">Proceed to Checkout</a>
        </div>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../partials/public_footer.php'; ?>
