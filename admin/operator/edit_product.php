<?php
require_once __DIR__ . '/../../partials/auth_check.php';

$product_no = (int)($_GET['product_no'] ?? $_GET['id'] ?? 0);
$product    = Product::find($product_no);
if (!$product) redirect(BASE_URL . '/admin/operator/inventory.php');

$details    = Product::details($product_no, (int)$product['category_id']);
$categories = Category::all();
$suppliers  = Supplier::all();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $imageFile = $product['image'];
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $dir = UPLOAD_PATH . '/products';
            if (!is_dir($dir)) @mkdir($dir, 0777, true);
            $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newName  = $product['product_id'] . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . '/' . $newName)) {
                $imageFile = $newName;
            }
        }
        Product::update($product_no, array_merge($_POST, ['image' => $imageFile]));
        redirect(BASE_URL . '/admin/operator/inventory.php');
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
include __DIR__ . '/../../partials/admin_header.php';
?>
<h1>Edit Product</h1>

<?php if ($error): ?><div style="background:#ffebee;color:#c62828;padding:1rem;border-radius:8px;margin-bottom:1rem;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 20px;">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product ID</label>
            <input type="text" value="<?php echo htmlspecialchars($product['product_id']); ?>" disabled>
        </div>
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" id="category_id" required onchange="toggleExtraFields()">
                <?php foreach ($categories as $c): ?>
                <option value="<?php echo (int)$c['category_id']; ?>" <?php echo (int)$product['category_id'] === (int)$c['category_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['category_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Supplier</label>
            <select name="supplier_no">
                <option value="">-- none --</option>
                <?php foreach ($suppliers as $s): ?>
                <option value="<?php echo (int)$s['supplier_no']; ?>" <?php echo (int)$product['supplier_no'] === (int)$s['supplier_no'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($s['company_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Brand</label><input type="text" name="brand" value="<?php echo htmlspecialchars($product['brand'] ?? ''); ?>"></div>

        <div id="fertilizer_fields" class="category-fields" style="display:none;">
            <div class="form-group"><label>NPK Ratio</label><input type="text" name="npk_ratio" value="<?php echo htmlspecialchars($details['npk_ratio'] ?? ''); ?>"></div>
            <div class="form-group"><label>Package Size</label><input type="text" name="package_size" value="<?php echo htmlspecialchars($details['package_size'] ?? ''); ?>"></div>
        </div>
        <div id="insecticide_fields" class="category-fields" style="display:none;">
            <div class="form-group"><label>Form</label><input type="text" name="form" value="<?php echo htmlspecialchars($details['form'] ?? ''); ?>"></div>
            <div class="form-group"><label>Active Ingredient</label><input type="text" name="active_ingredient" value="<?php echo htmlspecialchars($details['active_ingredient'] ?? ''); ?>"></div>
            <div class="form-group"><label>Package Size</label><input type="text" name="package_size" value="<?php echo htmlspecialchars($details['package_size'] ?? ''); ?>"></div>
        </div>
        <div id="herbicide_fields" class="category-fields" style="display:none;">
            <div class="form-group"><label>Form</label><input type="text" name="form" value="<?php echo htmlspecialchars($details['form'] ?? ''); ?>"></div>
            <div class="form-group"><label>Package Size</label><input type="text" name="package_size" value="<?php echo htmlspecialchars($details['package_size'] ?? ''); ?>"></div>
        </div>
        <div id="fungicide_fields" class="category-fields" style="display:none;">
            <div class="form-group"><label>Disease Control</label><input type="text" name="disease_control" value="<?php echo htmlspecialchars($details['disease_control'] ?? ''); ?>"></div>
            <div class="form-group"><label>Package Size</label><input type="text" name="package_size" value="<?php echo htmlspecialchars($details['package_size'] ?? ''); ?>"></div>
        </div>
        <div id="seed_fields" class="category-fields" style="display:none;">
            <div class="form-group"><label>Variety</label><input type="text" name="variety" value="<?php echo htmlspecialchars($details['variety'] ?? ''); ?>"></div>
            <div class="form-group"><label>Package Size</label><input type="text" name="package_size" value="<?php echo htmlspecialchars($details['package_size'] ?? ''); ?>"></div>
        </div>
        <div id="tool_fields" class="category-fields" style="display:none;">
            <div class="form-group"><label>Material</label><input type="text" name="material" value="<?php echo htmlspecialchars($details['material'] ?? ''); ?>"></div>
        </div>

        <div class="form-group">
            <label>Current Stock (number of packages)</label>
            <input type="number" name="stock" step="1" value="<?php echo (int)$product['stock']; ?>" required>
        </div>
        <div class="form-group">
            <label>Reorder Level (number of packages)</label>
            <input type="number" name="reorder_level" step="1" value="<?php echo (int)$product['reorder_level']; ?>" required>
        </div>
        <div class="form-group">
            <label>Price (Rs. per package)</label>
            <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label>Current Image</label>
            <?php if (!empty($product['image']) && is_file(UPLOAD_PATH . '/products/' . $product['image'])): ?>
                <div><img src="<?php echo UPLOAD_URL . '/products/' . htmlspecialchars($product['image']); ?>" width="100"></div>
            <?php else: ?>
                <div>No image</div>
            <?php endif; ?>
            <input type="file" name="image" accept="image/*">
            <small>Leave empty to keep current image.</small>
        </div>
        <button type="submit" class="btn-primary">Update Product</button>
        <a href="inventory.php" class="btn-outline">Cancel</a>
    </form>
</div>

<script>
function toggleExtraFields() {
    var sel = document.getElementById('category_id');
    var catId = parseInt(sel.value, 10);
    document.querySelectorAll('.category-fields').forEach(function(el) { el.style.display = 'none'; });
    var map = {1:'fertilizer_fields',2:'insecticide_fields',3:'herbicide_fields',4:'fungicide_fields',5:'seed_fields',6:'tool_fields'};
    if (map[catId]) document.getElementById(map[catId]).style.display = 'block';
}
toggleExtraFields();
</script>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
