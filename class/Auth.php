<?php
/**
 * Auth — staff (admin panel) + customer (storefront) authentication.
 * Uses password_hash / password_verify (bcrypt).
 */
final class Auth
{
    // ---------------- Staff (owner / cashier / operator) ----------------

    public static function loginStaff(string $username, string $password): array
    {
        $user = Database::one(
            "SELECT user_no, user_id, username, password, full_name, email, role, status
             FROM staff_users WHERE username = ? LIMIT 1",
            's', [$username]
        );
        if (!$user) {
            return ['ok' => false, 'error' => 'Invalid username or password'];
        }
        if ($user['status'] !== 'Active') {
            return ['ok' => false, 'error' => 'Account is inactive. Contact the owner.'];
        }
        if (!password_verify($password, $user['password'])) {
            return ['ok' => false, 'error' => 'Invalid username or password'];
        }

        // rehash if algorithm changed
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            Database::exec("UPDATE staff_users SET password = ? WHERE user_no = ?", 'si', [$newHash, $user['user_no']]);
        }

        Database::exec("UPDATE staff_users SET last_login = NOW() WHERE user_no = ?", 'i', [$user['user_no']]);

        $_SESSION['admin_user_no']  = (int)$user['user_no'];
        $_SESSION['admin_user']     = $user['username'];
        $_SESSION['admin_user_id']  = $user['user_id'];
        $_SESSION['admin_name']     = $user['full_name'];
        $_SESSION['admin_role']     = $user['role'];

        return ['ok' => true, 'role' => $user['role']];
    }

    public static function logoutStaff(): void
    {
        unset(
            $_SESSION['admin_user_no'], $_SESSION['admin_user'], $_SESSION['admin_user_id'],
            $_SESSION['admin_name'],    $_SESSION['admin_role']
        );
    }

    public static function isStaff(): bool
    {
        return isset($_SESSION['admin_user_no'], $_SESSION['admin_role']);
    }

    public static function requireStaff(?string $role = null): void
    {
        if (!self::isStaff()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
        // Owners can access everything; other roles only their own folder.
        if ($_SESSION['admin_role'] !== 'owner') {
            $myRole = $_SESSION['admin_role'];
            $uri    = $_SERVER['REQUEST_URI'];
            if (strpos($uri, "/admin/$myRole/") === false
                && strpos($uri, '/admin/includes/') === false
                && strpos($uri, '/ajax/') === false
                && strpos($uri, '/partials/') === false) {
                header('Location: ' . BASE_URL . "/admin/$myRole/dashboard.php");
                exit;
            }
            if ($role !== null && $role !== $myRole) {
                header('Location: ' . BASE_URL . "/admin/$myRole/dashboard.php");
                exit;
            }
        }
    }

    // ---------------- Customer (storefront) ----------------

    public static function loginCustomer(string $email, string $password): array
    {
        $cust = Database::one(
            "SELECT customer_no, customer_id, first_name, last_name, email, password
             FROM customers WHERE email = ? AND type = 'online' LIMIT 1",
            's', [$email]
        );
        if (!$cust || !$cust['password'] || !password_verify($password, $cust['password'])) {
            return ['ok' => false, 'error' => 'Invalid email or password'];
        }

        if (password_needs_rehash($cust['password'], PASSWORD_DEFAULT)) {
            $h = password_hash($password, PASSWORD_DEFAULT);
            Database::exec("UPDATE customers SET password = ? WHERE customer_no = ?", 'si', [$h, $cust['customer_no']]);
        }

        $_SESSION['customer_no']  = (int)$cust['customer_no'];
        $_SESSION['customer_id']  = $cust['customer_id'];
        $_SESSION['customer_name']= trim($cust['first_name'] . ' ' . $cust['last_name']);
        $_SESSION['customer_email']= $cust['email'];
        return ['ok' => true];
    }

    public static function registerCustomer(array $d): array
    {
        $email = trim((string)($d['email'] ?? ''));
        $pass  = (string)($d['password'] ?? '');
        if ($email === '' || $pass === '') {
            return ['ok' => false, 'error' => 'Email and password required'];
        }
        if (Database::one("SELECT customer_no FROM customers WHERE email = ?", 's', [$email])) {
            return ['ok' => false, 'error' => 'Email already registered'];
        }
        $cid = 'C' . str_pad((string)(Database::scalar("SELECT IFNULL(MAX(customer_no),0)+1 FROM customers")), 4, '0', STR_PAD_LEFT);
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $newId = Database::insert(
            "INSERT INTO customers (customer_id, first_name, last_name, email, password, phone, address, type)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'online')",
            'sssssss',
            [
                $cid,
                trim((string)($d['first_name'] ?? '')),
                trim((string)($d['last_name']  ?? '')),
                $email, $hash,
                trim((string)($d['phone']   ?? '')),
                trim((string)($d['address'] ?? '')),
            ]
        );
        return ['ok' => true, 'customer_no' => $newId];
    }

    public static function logoutCustomer(): void
    {
        unset($_SESSION['customer_no'], $_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_email']);
    }

    public static function isCustomer(): bool
    {
        return isset($_SESSION['customer_no']);
    }

    public static function requireCustomer(): void
    {
        if (!self::isCustomer()) {
            header('Location: ' . BASE_URL . '/public/login.php');
            exit;
        }
    }
}
