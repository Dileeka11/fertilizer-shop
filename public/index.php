<?php
require_once __DIR__ . '/../partials/public_header.php';

$featured = Product::all(['active_only' => true, 'limit' => 4, 'order_by' => 'p.product_no', 'order_dir' => 'DESC']);
?>
<style>
    .hero { background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=1950&q=80') center/cover; color: white; text-align: center; padding: 100px 20px; }
    .hero h1 { font-size: 3rem; }
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px,1fr)); gap: 2rem; margin: 2rem 0; }
    .product-card { background: white; border-radius: 12px; padding: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
    .product-card .price { color: #ff8f00; font-weight: bold; }
    .btn { background: #2e7d32; color: white; padding: 0.5rem 1rem; border-radius: 50px; text-decoration: none; display: inline-block; }
</style>
<div class="hero">
    <h1>Epaladeniya Agro City</h1>
    <p>Your trusted partner for quality agricultural inputs</p>
    <a href="products.php" class="btn">Shop Now</a>
</div>
<div class="container">
    <h2>Featured Products</h2>
    <div class="product-grid">
        <?php foreach ($featured as $p): ?>
        <div class="product-card">
            <h3><?php echo htmlspecialchars($p['name']); ?></h3>
            <p class="price">Rs. <?php echo number_format($p['price'],2); ?></p>
            <p><?php echo htmlspecialchars(substr((string)$p['description'],0,80)); ?>...</p>
            <a href="product.php?id=<?php echo (int)$p['product_no']; ?>" class="btn">View Details</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/public_footer.php'; ?>
