<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config.php';
require_once 'functions.php';
include '../includes/admin_header.php';

// Fetch categories and suppliers for dropdowns
$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
$suppliers = $conn->query("SELECT supplier_no, company_name FROM suppliers")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category_id = (int)$_POST['category_id'];
    $supplier_no = (int)$_POST['supplier_no'];
    $brand = $_POST['brand'];
    $stock = (int)$_POST['stock'];
    $reorder_level = (int)$_POST['reorder_level'];
    $price = (float)$_POST['price'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO products (category_id, supplier_no, name, brand, stock, reorder_level, price, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissiids", $category_id, $supplier_no, $name, $brand, $stock, $reorder_level, $price, $description);
    $stmt->execute();
    $product_no = $conn->insert_id;

    // Insert category-specific details
    $details = [];
    if ($category_id == 1) $details = ['npk_ratio' => $_POST['npk_ratio']];
    elseif ($category_id == 2) $details = ['form' => $_POST['form'], 'active_ingredient' => $_POST['active_ingredient']];
    elseif ($category_id == 3) $details = ['form' => $_POST['form']];
    elseif ($category_id == 4) $details = ['disease_control' => $_POST['disease_control']];
    elseif ($category_id == 5) $details = ['variety' => $_POST['variety']];
    elseif ($category_id == 6) $details = ['material' => $_POST['material']];
    if (!empty($details)) insertCategoryDetails($conn, $product_no, $category_id, $details);

    // Log initial stock as IN movement
    $stmt = $conn->prepare("INSERT INTO stock_movements (product_no, change_qty, type) VALUES (?, ?, 'IN')");
    $stmt->bind_param("ii", $product_no, $stock);
    $stmt->execute();

    header("Location: inventory.php");
    exit;
}
?>
<style>
    .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border-radius: 16px; }
    .form-group { margin-bottom: 1rem; }
    label { display: block; margin-bottom: 0.3rem; font-weight: bold; }
    input, select, textarea { width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 8px; }
    button { background: #2e7d32; color: white; padding: 0.8rem; border: none; border-radius: 50px; cursor: pointer; width: 100%; }
    .dynamic-fields { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee; }
</style>
<h1>Add Product</h1>
<div class="form-container">
    <form method="post" id="productForm">
        <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Category</label><select name="category_id" id="category_id" required>
            <option value="">Select</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option>
            <?php endforeach; ?>
        </select></div>
        <div class="form-group"><label>Supplier</label><select name="supplier_no" required>
            <?php foreach ($suppliers as $sup): ?>
            <option value="<?php echo $sup['supplier_no']; ?>"><?php echo $sup['company_name']; ?></option>
            <?php endforeach; ?>
        </select></div>
        <div class="form-group"><label>Brand</label><input type="text" name="brand"></div>
        <div class="form-group"><label>Stock</label><input type="number" name="stock" required></div>
        <div class="form-group"><label>Reorder Level</label><input type="number" name="reorder_level" required></div>
        <div class="form-group"><label>Price</label><input type="number" step="0.01" name="price" required></div>
        <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
        <div id="dynamicFields" class="dynamic-fields"></div>
        <button type="submit">Save Product</button>
    </form>
</div>
<script>
const categoryFields = {
    1: '<label>NPK Ratio</label><input type="text" name="npk_ratio">',
    2: '<label>Form</label><input type="text" name="form"><label>Active Ingredient</label><input type="text" name="active_ingredient">',
    3: '<label>Form</label><input type="text" name="form">',
    4: '<label>Disease Control</label><input type="text" name="disease_control">',
    5: '<label>Variety</label><input type="text" name="variety">',
    6: '<label>Material</label><input type="text" name="material">'
};
document.getElementById('category_id').addEventListener('change', function() {
    const val = this.value;
    const container = document.getElementById('dynamicFields');
    if (categoryFields[val]) container.innerHTML = categoryFields[val];
    else container.innerHTML = '';
});
</script>
<?php include '../includes/admin_footer.php'; ?>