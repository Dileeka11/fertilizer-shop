<?php
final class Customer
{
    public static function all(): array
    {
        return Database::all(
            "SELECT customer_no, customer_id, first_name, last_name, email, phone, address, type, created_at
             FROM customers WHERE type = 'online' ORDER BY customer_no DESC"
        );
    }

    public static function find(int $no): ?array
    {
        return Database::one("SELECT * FROM customers WHERE customer_no = ?", 'i', [$no]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::one("SELECT * FROM customers WHERE email = ?", 's', [$email]);
    }

    public static function findOrCreateGuest(string $name, string $email, string $phone, string $address): int
    {
        $found = self::findByEmail($email);
        if ($found) return (int)$found['customer_no'];

        $parts = explode(' ', trim($name), 2);
        $cid   = 'C' . str_pad((string)((int)Database::scalar("SELECT IFNULL(MAX(customer_no),0)+1 FROM customers")), 4, '0', STR_PAD_LEFT);
        return Database::insert(
            "INSERT INTO customers (customer_id, first_name, last_name, email, phone, address, type)
             VALUES (?, ?, ?, ?, ?, ?, 'online')",
            'ssssss',
            [$cid, $parts[0] ?? '', $parts[1] ?? '', $email, $phone, $address]
        );
    }

    public static function update(int $no, array $d): int
    {
        return Database::exec(
            "UPDATE customers
             SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?
             WHERE customer_no = ?",
            'sssssi',
            [
                (string)($d['first_name'] ?? ''),
                (string)($d['last_name']  ?? ''),
                (string)($d['email']      ?? ''),
                (string)($d['phone']      ?? ''),
                (string)($d['address']    ?? ''),
                $no,
            ]
        );
    }

    public static function topCustomers(int $limit = 10, ?string $start = null, ?string $end = null): array
    {
        $where = "WHERE s.status != 'Cancelled'";
        $types = '';
        $params = [];
        if ($start && $end) {
            $where .= " AND s.sale_date BETWEEN ? AND ?";
            $types .= 'ss';
            $params[] = $start . ' 00:00:00';
            $params[] = $end   . ' 23:59:59';
        }
        return Database::all(
            "SELECT c.customer_no, CONCAT(c.first_name,' ',IFNULL(c.last_name,'')) AS name,
                    COUNT(s.sale_no) AS orders, IFNULL(SUM(s.total),0) AS total
             FROM customers c
             JOIN sales s ON s.customer_no = c.customer_no
             $where AND s.sale_type = 'ONLINE'
             GROUP BY c.customer_no
             ORDER BY total DESC
             LIMIT " . (int)$limit,
            $types, $params
        );
    }
}
