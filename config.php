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
