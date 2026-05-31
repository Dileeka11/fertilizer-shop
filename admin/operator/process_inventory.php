<?php
// Legacy handler — now delegates to Product class.
require_once __DIR__ . '/../../partials/auth_check.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'add') {
        $imageFile = '';
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $dir = UPLOAD_PATH . '/products';
            if (!is_dir($dir)) @mkdir($dir, 0777, true);
            $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $tmpName   = 'pending_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . '/' . $tmpName)) {
                $imageFile = $tmpName;
            }
        }
        Product::create(array_merge($_POST, ['image' => $imageFile]));
        $_SESSION['success'] = 'Product added successfully!';
        redirect(BASE_URL . '/admin/operator/inventory.php');
    }

    if ($action === 'edit') {
        $productNo = (int)($_GET['product_no'] ?? $_GET['id'] ?? 0);
        $product   = Product::find($productNo);
        if (!$product) redirect(BASE_URL . '/admin/operator/inventory.php');

        $imageFile = $product['image'];
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $dir = UPLOAD_PATH . '/products';
            if (!is_dir($dir)) @mkdir($dir, 0777, true);
            $ext     = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newName = $product['product_id'] . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . '/' . $newName)) {
                $imageFile = $newName;
            }
        }
        Product::update($productNo, array_merge($_POST, ['image' => $imageFile]));
        $_SESSION['success'] = 'Product updated successfully!';
        redirect(BASE_URL . '/admin/operator/inventory.php');
    }
} catch (Throwable $e) {
    $_SESSION['error'] = $e->getMessage();
}
redirect(BASE_URL . '/admin/operator/inventory.php');
