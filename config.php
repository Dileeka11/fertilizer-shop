<?php
/**
 * Epaladeniya Agro City - Application bootstrap
 *  - DB credentials
 *  - PSR-style class autoloader (class/ folder)
 *  - Session start
 *  - Site-wide constants
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------- Database credentials --------------------
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fertilizer_shop_db');

// -------------------- Site constants --------------------
define('BASE_URL',   '/fertilizer-shop');
define('ROOT_PATH',  __DIR__);
define('CLASS_PATH', __DIR__ . '/class');
define('UPLOAD_PATH', __DIR__ . '/uploads');
define('UPLOAD_URL',  BASE_URL . '/uploads');

// -------------------- Class autoloader --------------------
spl_autoload_register(function (string $class): void {
    $file = CLASS_PATH . '/' . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

// -------------------- Global DB handle --------------------
$GLOBALS['conn'] = Database::getConnection();
$conn = $GLOBALS['conn'];

// -------------------- Tiny helpers (kept here for legacy code) --------------------
if (!function_exists('formatPrice')) {
    function formatPrice($price): string {
        return 'Rs. ' . number_format((float)$price, 2);
    }
}
if (!function_exists('productImageUrl')) {
    /**
     * Resolve a product image filename to a usable URL.
     * Treats empty / legacy-corrupt ("0") values and missing files as "no image"
     * and falls back to an inline SVG placeholder.
     */
    function productImageUrl(?string $image): string {
        $image = trim((string)$image);
        if ($image !== '' && $image !== '0' && is_file(UPLOAD_PATH . '/products/' . $image)) {
            return UPLOAD_URL . '/products/' . rawurlencode($image);
        }
        return 'data:image/svg+xml;utf8,' . rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">'
            . '<rect width="120" height="120" rx="12" fill="#f1f6f1"/>'
            . '<path d="M60 30c-16 0-30 12-30 30 16 0 30-12 30-30z" fill="#9ccc9c"/>'
            . '<path d="M60 30c0 18 14 30 30 30 0-16-14-30-30-30z" fill="#bcd6bc"/></svg>'
        );
    }
}
if (!function_exists('redirect')) {
    function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }
}
if (!function_exists('escape')) {
    function escape($conn, $input): string {
        return $conn->real_escape_string(htmlspecialchars(trim((string)$input)));
    }
}
if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn(): bool {
        return isset($_SESSION['admin_user'], $_SESSION['admin_role']);
    }
}
