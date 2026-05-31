<?php
final class Inventory
{
    public static function logMovement(int $productNo, int $qty, string $type = 'IN', string $reason = ''): int
    {
        $userNo = $_SESSION['admin_user_no'] ?? null;
        return Database::insert(
            "INSERT INTO stock_movements (product_no, change_qty, type, reason, user_no)
             VALUES (?, ?, ?, ?, ?)",
            'iissi',
            [$productNo, abs($qty), $type, $reason, $userNo]
        );
    }

    public static function decreaseStock(int $productNo, int $qty): void
    {
        Database::exec("UPDATE products SET stock = stock - ? WHERE product_no = ?", 'ii', [$qty, $productNo]);
    }

    public static function increaseStock(int $productNo, int $qty): void
    {
        Database::exec("UPDATE products SET stock = stock + ? WHERE product_no = ?", 'ii', [$qty, $productNo]);
    }

    public static function recentMovements(int $limit = 50): array
    {
        return Database::all(
            "SELECT sm.*, p.name AS product_name, u.username AS by_user
             FROM stock_movements sm
             JOIN products p ON sm.product_no = p.product_no
             LEFT JOIN staff_users u ON sm.user_no = u.user_no
             ORDER BY sm.movement_no DESC
             LIMIT " . (int)$limit
        );
    }
}
