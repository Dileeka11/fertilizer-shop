<?php
final class Sale
{
    /**
     * Create a sale + items + payment in one transaction.
     * Decrements stock and logs OUT movements.
     *
     * @param array $items  [[product_no, qty, price], ...]
     * @return int sale_no
     */
    public static function create(array $items, int $customerNo, string $type, string $paymentMethod, ?int $cashierNo = null): int
    {
        if (empty($items)) {
            throw new InvalidArgumentException('No items in sale');
        }

        $subtotal = 0.0;
        foreach ($items as $i) {
            $subtotal += (float)$i['price'] * (int)$i['qty'];
        }
        $total = $subtotal;

        Database::beginTransaction();
        try {
            $sid = 'INV' . date('Ymd') . str_pad((string)((int)Database::scalar("SELECT IFNULL(MAX(sale_no),0)+1 FROM sales")), 4, '0', STR_PAD_LEFT);

            $saleNo = Database::insert(
                "INSERT INTO sales (sale_id, customer_no, cashier_no, sale_type, subtotal, total, status)
                 VALUES (?, ?, ?, ?, ?, ?, 'Paid')",
                'siisdd',
                [$sid, $customerNo, $cashierNo, $type, $subtotal, $total]
            );

            foreach ($items as $it) {
                Database::insert(
                    "INSERT INTO sale_items (sale_no, product_no, quantity, price) VALUES (?, ?, ?, ?)",
                    'iiid',
                    [$saleNo, (int)$it['product_no'], (int)$it['qty'], (float)$it['price']]
                );
                Inventory::decreaseStock((int)$it['product_no'], (int)$it['qty']);
                Inventory::logMovement((int)$it['product_no'], (int)$it['qty'], 'OUT', $type === 'POS' ? 'POS sale' : 'Online order');
            }

            Database::insert(
                "INSERT INTO payments (sale_no, amount, payment_method) VALUES (?, ?, ?)",
                'ids',
                [$saleNo, $total, $paymentMethod]
            );

            Database::commit();
            return $saleNo;
        } catch (Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public static function find(int $saleNo): ?array
    {
        return Database::one(
            "SELECT s.*, CONCAT(c.first_name,' ',IFNULL(c.last_name,'')) AS customer_name,
                    c.phone AS customer_phone, c.email AS customer_email, c.address AS customer_address,
                    u.full_name AS cashier_name
             FROM sales s
             LEFT JOIN customers   c ON s.customer_no = c.customer_no
             LEFT JOIN staff_users u ON s.cashier_no  = u.user_no
             WHERE s.sale_no = ?",
            'i', [$saleNo]
        );
    }

    public static function findById(string $saleId): ?array
    {
        return Database::one(
            "SELECT s.*, CONCAT(c.first_name,' ',IFNULL(c.last_name,'')) AS customer_name,
                    c.phone AS customer_phone, c.email AS customer_email, c.address AS customer_address,
                    u.full_name AS cashier_name
             FROM sales s
             LEFT JOIN customers   c ON s.customer_no = c.customer_no
             LEFT JOIN staff_users u ON s.cashier_no  = u.user_no
             WHERE s.sale_id = ?",
            's', [$saleId]
        );
    }

    public static function items(int $saleNo): array
    {
        return Database::all(
            "SELECT si.*, p.name, p.product_id
             FROM sale_items si
             JOIN products p ON si.product_no = p.product_no
             WHERE si.sale_no = ?",
            'i', [$saleNo]
        );
    }

    public static function payment(int $saleNo): ?array
    {
        return Database::one("SELECT * FROM payments WHERE sale_no = ? ORDER BY payment_no DESC LIMIT 1", 'i', [$saleNo]);
    }

    public static function list(array $filter = []): array
    {
        $sql = "SELECT s.sale_no, s.sale_id, s.sale_date, s.sale_type, s.total, s.status,
                       CONCAT(IFNULL(c.first_name,''),' ',IFNULL(c.last_name,'')) AS customer,
                       p.payment_method
                FROM sales s
                LEFT JOIN customers c ON s.customer_no = c.customer_no
                LEFT JOIN payments  p ON p.sale_no    = s.sale_no
                WHERE 1=1";
        $types = ''; $params = [];

        if (!empty($filter['date'])) {
            $sql .= " AND DATE(s.sale_date) = ?";
            $types .= 's'; $params[] = $filter['date'];
        }
        if (!empty($filter['type'])) {
            $sql .= " AND s.sale_type = ?";
            $types .= 's'; $params[] = $filter['type'];
        }
        if (!empty($filter['customer_no'])) {
            $sql .= " AND s.customer_no = ?";
            $types .= 'i'; $params[] = (int)$filter['customer_no'];
        }
        $sql .= " ORDER BY s.sale_no DESC";
        if (!empty($filter['limit'])) {
            $sql .= " LIMIT " . (int)$filter['limit'];
        }
        return Database::all($sql, $types, $params);
    }

    public static function todayStats(): array
    {
        return [
            'sales' => (float)Database::scalar("SELECT IFNULL(SUM(total),0) FROM sales WHERE DATE(sale_date)=CURDATE() AND status='Paid'"),
            'count' => (int)Database::scalar("SELECT COUNT(*) FROM sales WHERE DATE(sale_date)=CURDATE() AND status='Paid'"),
        ];
    }
}
