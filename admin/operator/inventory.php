<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config.php';
include '../includes/admin_header.php';

$sql = "SELECT p.product_no, p.product_id, p.name, p.brand, p.image, p.stock, p.reorder_level, p.price,
               c.category_name, s.company_name as supplier
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN suppliers s ON p.supplier_no = s.supplier_no
        ORDER BY p.product_no";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<style>
    .low-stock { color: #d32f2f; font-weight: 700; }
    .stock-pill { display: inline-block; min-width: 38px; text-align: center; padding: 0.2rem 0.6rem; border-radius: 50px; font-weight: 700; font-size: 0.85rem; background: #e8f5e9; color: #1b5e20; }
    .stock-pill.low { background: #ffebee; color: #c62828; }
    .product-thumb { width: 52px; height: 52px; object-fit: cover; border-radius: 10px; border: 1px solid #e6ece6; background: #f1f6f1; }
</style>

<div class="inventory-container">
    <div class="section-header">
        <h1>Inventory Management</h1>
        <a href="add_product.php" class="btn-primary"><i class="fas fa-plus"></i> Add New Product</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Image</th><th>Product ID</th><th>Name</th><th>Category</th><th>Brand</th><th>Stock</th><th>Reorder Level</th><th>Price</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><img class="product-thumb" src="<?php echo productImageUrl($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"></td>
                    <td><?php echo htmlspecialchars($p['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['brand']); ?></td>
                    <td><span class="stock-pill <?php echo $p['stock'] <= $p['reorder_level'] ? 'low' : ''; ?>"><?php echo $p['stock']; ?></span></td>
                    <td><?php echo $p['reorder_level']; ?></td>
                    <td>Rs. <?php echo number_format($p['price'], 2); ?></td>
                    <td>
                        <a href="edit_product.php?product_no=<?php echo $p['product_no']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</a>
                        <a href="delete_product.php?product_no=<?php echo $p['product_no']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>