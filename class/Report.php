<?php
/**
 * Report — analytics queries used by owner/reports.
 * All numbers come from the live DB (sales, sale_items, products, etc.).
 */
final class Report
{
    public static function dateRange(string $period, string $startDate = '', string $endDate = ''): array
    {
        if ($startDate && $endDate) {
            return [$startDate . ' 00:00:00', $endDate . ' 23:59:59', "Custom: $startDate to $endDate"];
        }
        switch ($period) {
            case '1month':
                return [date('Y-m-d 00:00:00', strtotime('-1 month')), date('Y-m-d 23:59:59'), 'Last Month'];
            case '1year':
                return [date('Y-m-d 00:00:00', strtotime('-1 year')),  date('Y-m-d 23:59:59'), 'Last Year'];
            case '7days':
            default:
                return [date('Y-m-d 00:00:00', strtotime('-7 days')),  date('Y-m-d 23:59:59'), 'Last 7 Days'];
        }
    }

    public static function salesSummary(string $start, string $end): array
    {
        $row = Database::one(
            "SELECT IFNULL(SUM(total),0) AS revenue, COUNT(*) AS orders
             FROM sales WHERE sale_date BETWEEN ? AND ? AND status='Paid'",
            'ss', [$start, $end]
        );
        $rev = (float)($row['revenue'] ?? 0);
        $ord = (int)  ($row['orders']  ?? 0);
        return [
            'revenue' => $rev,
            'orders'  => $ord,
            'avg'     => $ord ? $rev / $ord : 0,
        ];
    }

    public static function salesByPeriod(string $period, string $start, string $end): array
    {
        if ($period === '1year') {
            $rows = Database::all(
                "SELECT DATE_FORMAT(sale_date,'%b') AS label, MONTH(sale_date) AS m, SUM(total) AS total
                 FROM sales WHERE sale_date BETWEEN ? AND ? AND status='Paid'
                 GROUP BY MONTH(sale_date) ORDER BY m",
                'ss', [$start, $end]
            );
        } elseif ($period === '1month') {
            $rows = Database::all(
                "SELECT CONCAT('Week ', WEEK(sale_date,1)) AS label, WEEK(sale_date,1) AS w, SUM(total) AS total
                 FROM sales WHERE sale_date BETWEEN ? AND ? AND status='Paid'
                 GROUP BY WEEK(sale_date,1) ORDER BY w",
                'ss', [$start, $end]
            );
        } else { // 7days OR custom
            $rows = Database::all(
                "SELECT DATE_FORMAT(sale_date,'%Y-%m-%d') AS label, DATE(sale_date) AS d, SUM(total) AS total
                 FROM sales WHERE sale_date BETWEEN ? AND ? AND status='Paid'
                 GROUP BY DATE(sale_date) ORDER BY d",
                'ss', [$start, $end]
            );
        }
        return $rows;
    }

    public static function topProducts(string $start, string $end, int $limit = 5): array
    {
        return Database::all(
            "SELECT p.product_id, p.name, c.category_name, SUM(si.quantity) AS qty, SUM(si.quantity*si.price) AS revenue
             FROM sale_items si
             JOIN sales    s ON si.sale_no    = s.sale_no
             JOIN products p ON si.product_no = p.product_no
             JOIN categories c ON p.category_id = c.category_id
             WHERE s.sale_date BETWEEN ? AND ? AND s.status='Paid'
             GROUP BY p.product_no
             ORDER BY revenue DESC
             LIMIT " . (int)$limit,
            'ss', [$start, $end]
        );
    }

    public static function revenueByCategory(string $start, string $end): array
    {
        return Database::all(
            "SELECT c.category_name AS category, IFNULL(SUM(si.quantity*si.price),0) AS revenue
             FROM categories c
             LEFT JOIN products p   ON p.category_id  = c.category_id
             LEFT JOIN sale_items si ON si.product_no = p.product_no
             LEFT JOIN sales s      ON si.sale_no    = s.sale_no AND s.sale_date BETWEEN ? AND ? AND s.status='Paid'
             GROUP BY c.category_id
             ORDER BY revenue DESC",
            'ss', [$start, $end]
        );
    }

    public static function revenueByPayment(string $start, string $end): array
    {
        return Database::all(
            "SELECT pay.payment_method AS method, IFNULL(SUM(pay.amount),0) AS amount
             FROM payments pay
             JOIN sales s ON pay.sale_no = s.sale_no
             WHERE s.sale_date BETWEEN ? AND ? AND s.status='Paid'
             GROUP BY pay.payment_method
             ORDER BY amount DESC",
            'ss', [$start, $end]
        );
    }

    public static function stockOverview(): array
    {
        return Database::all(
            "SELECT p.product_id, p.name, c.category_name AS category, p.stock, p.reorder_level, p.price,
                    (p.stock * p.price) AS value,
                    IFNULL((SELECT SUM(si.quantity)
                            FROM sale_items si
                            JOIN sales s ON si.sale_no = s.sale_no
                            WHERE si.product_no = p.product_no AND s.status='Paid'), 0) AS sold_qty
             FROM products p
             JOIN categories c ON p.category_id = c.category_id
             ORDER BY p.name"
        );
    }

    // ---------------- Inventory chart data (dashboard) ----------------

    /** Current stock vs reorder level for the N products with the lowest stock. */
    public static function stockByProduct(int $limit = 10): array
    {
        return Database::all(
            "SELECT p.name, p.stock, p.reorder_level
             FROM products p
             ORDER BY p.stock ASC
             LIMIT " . (int)$limit
        );
    }

    /** Total stock value (stock * price) grouped by category. */
    public static function stockValueByCategory(): array
    {
        return Database::all(
            "SELECT c.category_name AS category, IFNULL(SUM(p.stock * p.price),0) AS value
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.category_id
             GROUP BY c.category_id
             ORDER BY value DESC"
        );
    }

    /** Daily stock IN vs OUT quantities for the last N days (inventory flow). */
    public static function stockMovementTrend(int $days = 14): array
    {
        return Database::all(
            "SELECT DATE(created_at) AS d,
                    DATE_FORMAT(created_at, '%b %e') AS label,
                    SUM(CASE WHEN type='IN'  THEN change_qty ELSE 0 END) AS in_qty,
                    SUM(CASE WHEN type='OUT' THEN change_qty ELSE 0 END) AS out_qty
             FROM stock_movements
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL " . (int)$days . " DAY)
             GROUP BY DATE(created_at)
             ORDER BY d"
        );
    }

    public static function onlineOrdersStats(string $start, string $end): array
    {
        $row = Database::one(
            "SELECT COUNT(DISTINCT s.sale_no) AS orders,
                    COUNT(DISTINCT s.customer_no) AS customers,
                    IFNULL(SUM(s.total),0) AS revenue
             FROM sales s
             WHERE s.sale_type='ONLINE' AND s.sale_date BETWEEN ? AND ? AND s.status='Paid'",
            'ss', [$start, $end]
        ) ?? [];
        $rev = (float)($row['revenue'] ?? 0);
        $ord = (int)  ($row['orders']  ?? 0);
        return [
            'orders'    => $ord,
            'customers' => (int)($row['customers'] ?? 0),
            'revenue'   => $rev,
            'avg'       => $ord ? $rev / $ord : 0,
        ];
    }
}
