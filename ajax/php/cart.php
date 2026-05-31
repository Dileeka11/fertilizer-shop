<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';
try {
    switch ($action) {
        case 'add':
            Cart::add((int)$_POST['product_no'], max(1, (int)($_POST['qty'] ?? 1)));
            break;
        case 'update':
            Cart::update((int)$_POST['product_no'], (int)$_POST['qty']);
            break;
        case 'remove':
            Cart::remove((int)$_POST['product_no']);
            break;
        case 'clear':
            Cart::clear();
            break;
        case 'get':
        default:
            // fallthrough -> return cart
    }
    echo json_encode([
        'ok'    => true,
        'items' => Cart::all(),
        'total' => Cart::total(),
        'count' => Cart::count(),
    ]);
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
