<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config.php';
include '../includes/admin_header.php';

$sql = "SELECT p.product_no, p.product_id, p.name, p.brand, p.stock, p.reorder_level, p.price,
               c.category_name, s.company_name as supplier
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN suppliers s ON p.supplier_no = s.supplier_no
        ORDER BY p.product_no";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<style>
    .inventory-container { padding: 20px; }
    .table-container { overflow-x: auto; background: white; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #2e7d32; color: white; font-weight: bold; }
    .low-stock { color: #d32f2f; font-weight: bold; }
    .btn-primary { background: #2e7d32; color: white; padding: 8px 20px; border-radius: 50px; text-decoration: none; display: inline-block; margin-bottom: 20px; }
    .action-btn { padding: 5px 12px; border-radius: 20px; background: #ffb74d; color: #212121; text-decoration: none; display: inline-block; font-size: 0.9rem; }
    .delete-btn { background: #e57373; color: white; }
</style>

<div class="inventory-container">
    <h1>Inventory Management</h1>
    <a href="add_product.php" class="btn-primary"><i class="fas fa-plus"></i> Add New Product</a>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Product ID</th><th>Name</th><th>Category</th><th>Brand</th><th>Stock</th><th>Reorder Level</th><th>Price</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['brand']); ?></td>
                    <td class="<?php echo $p['stock'] <= $p['reorder_level'] ? 'low-stock' : ''; ?>"><?php echo $p['stock']; ?></td>
                    <td><?php echo $p['reorder_level']; ?></td>
                    <td>Rs. <?php echo number_format($p['price'], 2); ?></td>
                    <td>
                        <a href="edit_product.php?product_no=<?php echo $p['product_no']; ?>" class="action-btn">Edit</a>
                        <a href="delete_product.php?product_no=<?php echo $p['product_no']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>