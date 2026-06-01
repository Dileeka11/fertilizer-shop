<?php
require_once __DIR__ . '/../partials/public_header.php';

$category = $_GET['category'] ?? '';
$search   = $_GET['search']   ?? '';

$products   = Product::all([
    'category_slug' => $category,
    'search'        => $search,
    'active_only'   => true,
]);
$categories = Category::all();
?>
<style>
    .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px,1fr)); gap: 2rem; }
    .product-card { background: white; border-radius: 12px; padding: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
    .product-thumb { width: 100%; height: 180px; object-fit: cover; border-radius: 10px; background: #f1f6f1; margin-bottom: 0.8rem; }
    .price { color: #ff8f00; font-weight: bold; }
    .btn { background: #2e7d32; color: white; padding: 0.5rem 1rem; border-radius: 50px; text-decoration: none; display: inline-block; }
    .filter-bar { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
    .filter-bar input, .filter-bar select { padding: 0.5rem; border-radius: 8px; border: 1px solid #ccc; }
</style>
<div class="container">
    <h1>All Products</h1>
    <div class="filter-bar">
        <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?php echo htmlspecialchars($c['slug']); ?>" <?php echo $category == $c['slug'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['category_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Filter</button>
        </form>
    </div>
    <div class="product-grid">
        <?php foreach ($products as $p): ?>
        <div class="product-card">
            <a href="product.php?id=<?php echo (int)$p['product_no']; ?>">
                <img class="product-thumb" src="<?php echo productImageUrl($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
            </a>
            <h3><?php echo htmlspecialchars($p['name']); ?></h3>
            <p class="price">Rs. <?php echo number_format($p['price'],2); ?></p>
            <p><?php echo htmlspecialchars(substr((string)$p['description'],0,80)); ?></p>
            <a href="product.php?id=<?php echo (int)$p['product_no']; ?>" class="btn">View Details</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/public_footer.php'; ?>
