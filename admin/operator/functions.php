<?php
// Legacy shim — JSON-file product helpers replaced by the Product class.
// Stubs kept for any old code still calling these names.
require_once __DIR__ . '/../../config.php';

if (!function_exists('loadProducts')) {
    function loadProducts(): array {
        $rows = Product::all();
        // map DB rows to the older flat structure used by legacy callers
        return array_map(function ($p) {
            return [
                'id'       => $p['product_id'],
                'name'     => $p['name'],
                'category' => $p['category_name'],
                'stock'    => (int)$p['stock'],
                'reorder'  => (int)$p['reorder_level'],
                'price'    => (float)$p['price'],
                'brand'    => $p['brand'],
                'image'    => $p['image'],
                'description' => $p['description'],
            ];
        }, $rows);
    }
}
if (!function_exists('saveProducts')) {
    function saveProducts(array $products): void { /* no-op: writes happen via Product class */ }
}
if (!function_exists('sendLowStockAlert')) {
    function sendLowStockAlert(array $product): void {
        // Hook point — implement actual mail() send here using EmailConfig::renderAlert($product)
    }
}
if (!function_exists('loadEmailConfig')) {
    function loadEmailConfig(): array { return EmailConfig::load(); }
}
if (!function_exists('saveEmailConfig')) {
    function saveEmailConfig(array $d): void { EmailConfig::save($d); }
}
if (!function_exists('getCategoryDetails')) {
    function getCategoryDetails($conn, int $product_no, int $category_id): ?array {
        $d = Product::details($product_no, $category_id);
        return $d ?: null;
    }
}
if (!function_exists('insertCategoryDetails')) {
    function insertCategoryDetails($conn, int $product_no, int $category_id, array $data): void {
        // Use Product::update with merged details so the right detail row is rewritten.
        $p = Product::find($product_no);
        if (!$p) return;
        Product::update($product_no, array_merge($p, $data, ['category_id' => $category_id]));
    }
}
if (!function_exists('updateCategoryDetails')) {
    function updateCategoryDetails($conn, int $product_no, int $category_id, array $data): void {
        insertCategoryDetails($conn, $product_no, $category_id, $data);
    }
}
