<?php
require_once __DIR__ . '/../config.php';
if (Cart::isEmpty()) redirect(BASE_URL . '/public/cart.php');

try {
    $name    = trim((string)($_POST['name']    ?? ''));
    $email   = trim((string)($_POST['email']   ?? ''));
    $phone   = trim((string)($_POST['phone']   ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $method  = (string)($_POST['payment_method'] ?? 'Cash on Delivery');

    // logged-in customer wins, else find/create by email
    $customerNo = Auth::isCustomer()
        ? (int)$_SESSION['customer_no']
        : Customer::findOrCreateGuest($name, $email, $phone, $address);

    $items = array_map(function ($i) {
        return ['product_no' => (int)$i['product_no'], 'qty' => (int)$i['qty'], 'price' => (float)$i['price']];
    }, Cart::all());

    $saleNo = Sale::create($items, $customerNo, 'ONLINE', $method, null);

    // create an online_orders record
    $sale = Sale::find($saleNo);
    Database::insert(
        "INSERT INTO online_orders (order_id, customer_no, sale_no, shipping_address, status)
         VALUES (?, ?, ?, ?, 'Pending')",
        'siis',
        ['ORD' . $sale['sale_id'], $customerNo, $saleNo, $address]
    );

    Cart::clear();
    redirect(BASE_URL . '/public/order_confirmation.php?sale_id=' . urlencode($sale['sale_id']));
} catch (Throwable $e) {
    die('Order failed: ' . htmlspecialchars($e->getMessage()));
}
