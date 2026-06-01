<?php
require_once __DIR__ . '/../partials/public_header.php';

$product_no = (int)($_GET['id'] ?? 0);
$product    = Product::find($product_no);
if (!$product) {
    // fallback to product_id (legacy URLs)
    $product = Product::findById((string)($_GET['id'] ?? ''));
}
if (!$product) die('Product not found.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    Cart::add((int)$product['product_no'], $qty);
    redirect(BASE_URL . '/public/cart.php');
}
?>
<style>
    .product-detail { display: flex; gap: 2rem; flex-wrap: wrap; }
    .product-media { flex: 1; min-width: 280px; }
    .product-media img { width: 100%; max-width: 420px; border-radius: 16px; box-shadow: 0 4px 14px rgba(27,94,32,0.12); background: #f1f6f1; object-fit: cover; }
    .product-info { flex: 1; min-width: 280px; }
    .product-price { font-size: 2rem; color: #ff8f00; }
    .btn-primary { background: #2e7d32; color: white; padding: 0.8rem 2rem; border: none; border-radius: 50px; cursor: pointer; }
</style>
<div class="container">
    <div class="product-detail">
        <div class="product-media">
            <img src="<?php echo productImageUrl($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
            <p><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand'] ?? ''); ?></p>
            <p class="product-price">Rs. <?php echo number_format($product['price'],2); ?></p>
            <p><?php echo nl2br(htmlspecialchars($product['description'] ?? '')); ?></p>
            <form method="post">
                <label>Quantity:</label>
                <input type="number" name="qty" value="1" min="1" max="<?php echo (int)$product['stock']; ?>" style="width: 80px; margin-right: 1rem;">
                <button type="submit" class="btn-primary">Add to Cart</button>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/public_footer.php'; ?>
