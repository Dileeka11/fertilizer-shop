<?php
require_once '../includes/auth_check.php';
require_once 'functions.php';
include '../includes/admin_header.php';

$config = loadEmailConfig();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config['to_email'] = $_POST['to_email'];
    $config['from_email'] = $_POST['from_email'];
    $config['subject'] = $_POST['subject'];
    $config['message'] = $_POST['message'];
    saveEmailConfig($config);
    $success = "Email settings updated successfully!";
    $config = loadEmailConfig();
}
?>

<h1>Email Alert Settings</h1>

<?php if (isset($success)): ?>
    <div class="alert alert-success" style="background: #c8e6c9; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<div style="max-width: 800px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 20px; box-shadow: var(--shadow);">
    <form method="POST">
        <div class="form-group">
            <label>Recipient Email (Owner)</label>
            <input type="email" name="to_email" value="<?php echo htmlspecialchars($config['to_email']); ?>" required>
            <small>Email address where low stock alerts will be sent.</small>
        </div>
        <div class="form-group">
            <label>Sender Email</label>
            <input type="email" name="from_email" value="<?php echo htmlspecialchars($config['from_email']); ?>" required>
            <small>Email address that appears as the sender.</small>
        </div>
        <div class="form-group">
            <label>Email Subject</label>
            <input type="text" name="subject" value="<?php echo htmlspecialchars($config['subject']); ?>" required>
            <small>Use <code>{product_name}</code> to insert the product name.</small>
        </div>
        <div class="form-group">
            <label>Email Message (Plain Text)</label>
            <textarea name="message" rows="10" required><?php echo htmlspecialchars($config['message']); ?></textarea>
            <small>
                Available placeholders:<br>
                <code>{id}</code> - Product ID<br>
                <code>{name}</code> - Product Name<br>
                <code>{category}</code> - Category<br>
                <code>{extra_fields}</code> - Extra details (brand, variety, package, form)<br>
                <code>{stock}</code> - Current Stock<br>
                <code>{reorder}</code> - Reorder Level
            </small>
        </div>
        <button type="submit" class="btn-primary">Save Settings</button>
        <a href="inventory.php" class="btn-outline">Back to Inventory</a>
    </form>

    <hr style="margin: 2rem 0;">

    <h3>Example Email (Low Stock Alert)</h3>
    <div style="background: #f5f5f5; padding: 1rem; border-radius: 8px;">
        <?php
        $example = [
            'id' => 'S001',
            'name' => 'Tomato Seeds',
            'category' => 'Seed',
            'brand' => 'GreenHarvest',
            'variety' => 'Hybrid',
            'package_size' => '100g',
            'stock' => 12000,
            'reorder' => 5000
        ];
        $extra = "Brand: {$example['brand']}\nVariety: {$example['variety']}\nPackage Size: {$example['package_size']}\n";
        $subject_example = str_replace('{product_name}', $example['name'], $config['subject']);
        $message_example = str_replace(
            ['{id}', '{name}', '{category}', '{extra_fields}', '{stock}', '{reorder}'],
            [$example['id'], $example['name'], $example['category'], $extra, $example['stock'], $example['reorder']],
            $config['message']
        );
        ?>
        <p><strong>Subject:</strong> <?php echo htmlspecialchars($subject_example); ?></p>
        <pre style="white-space: pre-wrap; font-family: monospace;"><?php echo htmlspecialchars($message_example); ?></pre>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>