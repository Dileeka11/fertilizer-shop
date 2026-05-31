<?php
require_once __DIR__ . '/../../partials/auth_check.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $imageFile = '';
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $dir = UPLOAD_PATH . '/products';
            if (!is_dir($dir)) @mkdir($dir, 0777, true);
            $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $tmpName  = 'pending_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . '/' . $tmpName)) {
                $imageFile = $tmpName;
            }
        }
        Product::create(array_merge($_POST, ['image' => $imageFile]));
        redirect(BASE_URL . '/admin/operator/inventory.php');
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$categories = Category::all();
$suppliers  = Supplier::all();

include __DIR__ . '/../../partials/admin_header.php';
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

<?php if ($error): ?><div style="background:#ffebee;color:#c62828;padding:1rem;border-radius:8px;margin-bottom:1rem;max-width:600px;margin:0 auto 1rem;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="form-container">
    <form method="post" id="productForm" enctype="multipart/form-data">
        <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Category</label><select name="category_id" id="category_id" required>
            <option value="">Select</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?php echo (int)$cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
            <?php endforeach; ?>
        </select></div>
        <div class="form-group"><label>Supplier</label><select name="supplier_no">
            <option value="">-- none --</option>
            <?php foreach ($suppliers as $sup): ?>
            <option value="<?php echo (int)$sup['supplier_no']; ?>"><?php echo htmlspecialchars($sup['company_name']); ?></option>
            <?php endforeach; ?>
        </select></div>
        <div class="form-group"><label>Brand</label><input type="text" name="brand"></div>
        <div class="form-group"><label>Stock</label><input type="number" name="stock" required></div>
        <div class="form-group"><label>Reorder Level</label><input type="number" name="reorder_level" required></div>
        <div class="form-group"><label>Price</label><input type="number" step="0.01" name="price" required></div>
        <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
        <div class="form-group"><label>Image</label><input type="file" name="image" accept="image/*"></div>
        <div id="dynamicFields" class="dynamic-fields"></div>
        <button type="submit">Save Product</button>
    </form>
</div>
<script>
const categoryFields = {
    1: '<label>NPK Ratio</label><input type="text" name="npk_ratio"><label>Package Size</label><input type="text" name="package_size">',
    2: '<label>Form</label><input type="text" name="form"><label>Active Ingredient</label><input type="text" name="active_ingredient"><label>Package Size</label><input type="text" name="package_size">',
    3: '<label>Form</label><input type="text" name="form"><label>Package Size</label><input type="text" name="package_size">',
    4: '<label>Disease Control</label><input type="text" name="disease_control"><label>Package Size</label><input type="text" name="package_size">',
    5: '<label>Variety</label><input type="text" name="variety"><label>Package Size</label><input type="text" name="package_size">',
    6: '<label>Material</label><input type="text" name="material">'
};
document.getElementById('category_id').addEventListener('change', function() {
    const val = this.value;
    const container = document.getElementById('dynamicFields');
    container.innerHTML = categoryFields[val] || '';
});
</script>
<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
