<?php
// Legacy shim — forwards to the new root config.php so functions like
// formatPrice(), redirect(), escape(), isAdminLoggedIn() remain available.
require_once __DIR__ . '/../config.php';

// Legacy DB-helper functions kept for any old code still referencing them.
if (!function_exists('getCustomerByEmail')) {
    function getCustomerByEmail($conn, $email) {
        return Customer::findByEmail((string)$email);
    }
}
if (!function_exists('getProductByNo')) {
    function getProductByNo($conn, $product_no) {
        return Product::find((int)$product_no);
    }
}
if (!function_exists('getCartItems')) {
    function getCartItems($conn, $cart_no) {
        return Cart::all(); // session-based cart; $cart_no ignored
    }
}
