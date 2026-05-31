<?php
require_once __DIR__ . '/../config.php';
if (Cart::isEmpty()) {
    redirect(BASE_URL . '/public/cart.php');
}
require_once __DIR__ . '/../partials/public_header.php';
$total = Cart::total();

// Pre-fill from logged-in customer if available
$prefill = ['name' => '', 'email' => '', 'phone' => '', 'address' => ''];
if (Auth::isCustomer()) {
    $c = Customer::find((int)$_SESSION['customer_no']);
    if ($c) {
        $prefill['name']    = trim($c['first_name'] . ' ' . $c['last_name']);
        $prefill['email']   = $c['email'];
        $prefill['phone']   = $c['phone'];
        $prefill['address'] = $c['address'];
    }
}
?>
<style>
    .checkout-form { max-width: 600px; margin: 0 auto; }
    .form-group { margin-bottom: 1rem; }
    label { display: block; font-weight: bold; }
    input, select, textarea { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 8px; }
    .btn-primary { background: #2e7d32; color: white; padding: 0.8rem; border: none; border-radius: 50px; cursor: pointer; width: 100%; }
</style>
<div class="container">
    <h1>Checkout</h1>
    <div class="checkout-form">
        <form method="post" action="place_order.php">
            <div class="form-group"><label>Full Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($prefill['name']); ?>" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($prefill['email']); ?>" required></div>
            <div class="form-group"><label>Phone</label><input type="tel" name="phone" value="<?php echo htmlspecialchars($prefill['phone']); ?>" required></div>
            <div class="form-group"><label>Address</label><textarea name="address" rows="3" required><?php echo htmlspecialchars($prefill['address']); ?></textarea></div>
            <div class="form-group"><label>Payment Method</label>
                <select name="payment_method">
                    <option value="Cash on Delivery">Cash on Delivery</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>
            </div>
            <p><strong>Total: Rs. <?php echo number_format($total,2); ?></strong></p>
            <button type="submit" class="btn-primary">Place Order</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/public_footer.php'; ?>
