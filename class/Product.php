<?php
/**
 * Product CRUD + category-specific detail tables
 *  category_id => detail table
 *    1 = Fertilizer   -> fertilizer_details   (npk_ratio, package_size)
 *    2 = Insecticide  -> insecticide_details  (form, active_ingredient, package_size)
 *    3 = Herbicide    -> herbicide_details    (form, package_size)
 *    4 = Fungicide    -> fungicide_details    (disease_control, package_size)
 *    5 = Seed         -> seed_details         (variety, package_size)
 *    6 = Tool         -> tool_details         (material)
 */
final class Product
{
    public const DETAIL_TABLES = [
        1 => 'fertilizer_details',
        2 => 'insecticide_details',
        3 => 'herbicide_details',
        4 => 'fungicide_details',
        5 => 'seed_details',
        6 => 'tool_details',
    ];

    // ---------------- Read ----------------

    public static function all(array $filter = []): array
    {
        $sql = "SELECT p.product_no, p.product_id, p.name, p.brand, p.description, p.image,
                       p.price, p.stock, p.reorder_level, p.discount, p.status,
                       p.category_id, c.category_name, c.slug AS category_slug,
                       p.supplier_no, s.company_name AS supplier
                FROM products p
                JOIN categories c ON p.category_id = c.category_id
                LEFT JOIN suppliers s ON p.supplier_no = s.supplier_no
                WHERE 1=1";
        $types = '';
        $params = [];

        if (!empty($filter['category_slug'])) {
            $sql .= " AND c.slug = ?";
            $types .= 's';
            $params[] = $filter['category_slug'];
        }
        if (!empty($filter['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $types .= 'i';
            $params[] = (int)$filter['category_id'];
        }
        if (!empty($filter['search'])) {
            $like  = '%' . $filter['search'] . '%';
            $sql  .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.product_id LIKE ?)";
            $types .= 'sss';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        if (!empty($filter['low_stock'])) {
            $sql .= " AND p.stock <= p.reorder_level";
        }
        if (!empty($filter['active_only'])) {
            $sql .= " AND p.status = 'Active'";
        }

        $sql .= " ORDER BY " . ($filter['order_by'] ?? 'p.name') . " " . (($filter['order_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC');

        if (!empty($filter['limit'])) {
            $sql .= " LIMIT " . (int)$filter['limit'];
        }

        return Database::all($sql, $types, $params);
    }

    public static function find(int $no): ?array
    {
        return Database::one(
            "SELECT p.*, c.category_name, c.slug AS category_slug, s.company_name AS supplier
             FROM products p
             JOIN categories c ON p.category_id = c.category_id
             LEFT JOIN suppliers s ON p.supplier_no = s.supplier_no
             WHERE p.product_no = ?",
            'i', [$no]
        );
    }

    public static function findById(string $productId): ?array
    {
        return Database::one(
            "SELECT p.*, c.category_name, c.slug AS category_slug, s.company_name AS supplier
             FROM products p
             JOIN categories c ON p.category_id = c.category_id
             LEFT JOIN suppliers s ON p.supplier_no = s.supplier_no
             WHERE p.product_id = ?",
            's', [$productId]
        );
    }

    public static function details(int $productNo, int $categoryId): array
    {
        if (!isset(self::DETAIL_TABLES[$categoryId])) return [];
        $table = self::DETAIL_TABLES[$categoryId];
        $row = Database::one("SELECT * FROM `$table` WHERE product_no = ?", 'i', [$productNo]);
        return $row ?: [];
    }

    public static function counts(): array
    {
        return [
            'total'      => (int)Database::scalar("SELECT COUNT(*) FROM products"),
            'low_stock'  => (int)Database::scalar("SELECT COUNT(*) FROM products WHERE stock <= reorder_level"),
            'out_stock'  => (int)Database::scalar("SELECT COUNT(*) FROM products WHERE stock <= 0"),
            'stock_value'=> (float)Database::scalar("SELECT IFNULL(SUM(stock*price),0) FROM products"),
        ];
    }

    public static function lowStock(): array
    {
        return Database::all(
            "SELECT p.*, c.category_name
             FROM products p
             JOIN categories c ON p.category_id = c.category_id
             WHERE p.stock <= p.reorder_level
             ORDER BY p.stock ASC"
        );
    }

    // ---------------- Write ----------------

    public static function create(array $d): int
    {
        Database::beginTransaction();
        try {
            $pid = 'P' . str_pad((string)((int)Database::scalar("SELECT IFNULL(MAX(product_no),0)+1 FROM products")), 4, '0', STR_PAD_LEFT);
            $productNo = Database::insert(
                "INSERT INTO products
                    (product_id, category_id, supplier_no, name, brand, description, image,
                     price, stock, reorder_level, discount, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                'siisssdiidss',
                [
                    $pid,
                    (int)$d['category_id'],
                    !empty($d['supplier_no']) ? (int)$d['supplier_no'] : null,
                    (string)$d['name'],
                    (string)($d['brand']        ?? ''),
                    (string)($d['description']  ?? ''),
                    (string)($d['image']        ?? ''),
                    (float)$d['price'],
                    (int)$d['stock'],
                    (int)($d['reorder_level']   ?? 0),
                    (float)($d['discount']      ?? 0),
                    (string)($d['status']       ?? 'Active'),
                ]
            );

            self::saveDetails($productNo, (int)$d['category_id'], $d);

            // initial stock IN movement
            if ((int)$d['stock'] > 0) {
                Inventory::logMovement($productNo, (int)$d['stock'], 'IN', 'Initial stock');
            }

            Database::commit();
            return $productNo;
        } catch (Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public static function update(int $productNo, array $d): int
    {
        Database::beginTransaction();
        try {
            $old = self::find($productNo);
            if (!$old) throw new RuntimeException('Product not found');

            $affected = Database::exec(
                "UPDATE products
                 SET category_id = ?, supplier_no = ?, name = ?, brand = ?, description = ?,
                     image = COALESCE(NULLIF(?, ''), image),
                     price = ?, stock = ?, reorder_level = ?, discount = ?, status = ?
                 WHERE product_no = ?",
                'iisssddiidsi',
                [
                    (int)$d['category_id'],
                    !empty($d['supplier_no']) ? (int)$d['supplier_no'] : null,
                    (string)$d['name'],
                    (string)($d['brand']        ?? ''),
                    (string)($d['description']  ?? ''),
                    (string)($d['image']        ?? ''),
                    (float)$d['price'],
                    (int)$d['stock'],
                    (int)($d['reorder_level']   ?? 0),
                    (float)($d['discount']      ?? 0),
                    (string)($d['status']       ?? 'Active'),
                    $productNo,
                ]
            );

            // if category changed, drop old detail row, insert new
            if ((int)$old['category_id'] !== (int)$d['category_id']) {
                if (isset(self::DETAIL_TABLES[(int)$old['category_id']])) {
                    Database::exec("DELETE FROM `" . self::DETAIL_TABLES[(int)$old['category_id']] . "` WHERE product_no = ?", 'i', [$productNo]);
                }
            }
            self::saveDetails($productNo, (int)$d['category_id'], $d);

            // log stock adjustment if stock changed
            $delta = (int)$d['stock'] - (int)$old['stock'];
            if ($delta !== 0) {
                Inventory::logMovement($productNo, abs($delta), $delta > 0 ? 'IN' : 'OUT', 'Manual adjustment');
            }

            Database::commit();
            return $affected;
        } catch (Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public static function delete(int $productNo): int
    {
        return Database::exec("DELETE FROM products WHERE product_no = ?", 'i', [$productNo]);
    }

    public static function setDiscount(int $productNo, float $discount): int
    {
        return Database::exec("UPDATE products SET discount = ? WHERE product_no = ?", 'di', [$discount, $productNo]);
    }

    // ---------------- Helpers ----------------

    private static function saveDetails(int $productNo, int $categoryId, array $d): void
    {
        if (!isset(self::DETAIL_TABLES[$categoryId])) return;
        $table = self::DETAIL_TABLES[$categoryId];

        // remove old row first then insert (simpler than upsert across diff column sets)
        Database::exec("DELETE FROM `$table` WHERE product_no = ?", 'i', [$productNo]);

        switch ($categoryId) {
            case 1: // fertilizer
                Database::exec(
                    "INSERT INTO fertilizer_details (product_no, npk_ratio, package_size) VALUES (?, ?, ?)",
                    'iss', [$productNo, (string)($d['npk_ratio'] ?? ''), (string)($d['package_size'] ?? '')]
                ); break;
            case 2: // insecticide
                Database::exec(
                    "INSERT INTO insecticide_details (product_no, form, active_ingredient, package_size) VALUES (?, ?, ?, ?)",
                    'isss', [$productNo, (string)($d['form'] ?? ''), (string)($d['active_ingredient'] ?? ''), (string)($d['package_size'] ?? '')]
                ); break;
            case 3: // herbicide
                Database::exec(
                    "INSERT INTO herbicide_details (product_no, form, package_size) VALUES (?, ?, ?)",
                    'iss', [$productNo, (string)($d['form'] ?? ''), (string)($d['package_size'] ?? '')]
                ); break;
            case 4: // fungicide
                Database::exec(
                    "INSERT INTO fungicide_details (product_no, disease_control, package_size) VALUES (?, ?, ?)",
                    'iss', [$productNo, (string)($d['disease_control'] ?? ''), (string)($d['package_size'] ?? '')]
                ); break;
            case 5: // seed
                Database::exec(
                    "INSERT INTO seed_details (product_no, variety, package_size) VALUES (?, ?, ?)",
                    'iss', [$productNo, (string)($d['variety'] ?? ''), (string)($d['package_size'] ?? '')]
                ); break;
            case 6: // tool
                Database::exec(
                    "INSERT INTO tool_details (product_no, material) VALUES (?, ?)",
                    'is', [$productNo, (string)($d['material'] ?? '')]
                ); break;
        }
    }
}
