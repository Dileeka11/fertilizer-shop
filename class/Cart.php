<?php
/**
 * Cart — session-based shopping cart for the storefront.
 * Stored at $_SESSION['cart'] as an array of:
 *   [product_no => ['product_no','name','price','qty','image']]
 */
final class Cart
{
    private static function init(): void
    {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public static function all(): array
    {
        self::init();
        return array_values($_SESSION['cart']);
    }

    public static function add(int $productNo, int $qty = 1): void
    {
        self::init();
        $p = Product::find($productNo);
        if (!$p) return;

        $key = (string)$productNo;
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$key] = [
                'product_no' => (int)$p['product_no'],
                'product_id' => $p['product_id'],
                'name'       => $p['name'],
                'price'      => (float)$p['price'],
                'image'      => $p['image'],
                'qty'        => $qty,
            ];
        }
    }

    public static function update(int $productNo, int $qty): void
    {
        self::init();
        $key = (string)$productNo;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$key]);
            return;
        }
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] = $qty;
        }
    }

    public static function remove(int $productNo): void
    {
        self::init();
        unset($_SESSION['cart'][(string)$productNo]);
    }

    public static function clear(): void
    {
        $_SESSION['cart'] = [];
    }

    public static function count(): int
    {
        self::init();
        $n = 0;
        foreach ($_SESSION['cart'] as $it) $n += (int)$it['qty'];
        return $n;
    }

    public static function total(): float
    {
        self::init();
        $t = 0.0;
        foreach ($_SESSION['cart'] as $it) {
            $t += (float)$it['price'] * (int)$it['qty'];
        }
        return $t;
    }

    public static function isEmpty(): bool
    {
        self::init();
        return empty($_SESSION['cart']);
    }
}
