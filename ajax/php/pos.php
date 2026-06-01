<?php
/**
 * AJAX endpoint used by the cashier POS screen.
 *   ?action=complete   POST (JSON body)  -> { items:[{product_no,qty,price}], customer_name, payment_method }
 */
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

if (!Auth::isStaff() || !in_array($_SESSION['admin_role'], ['cashier','owner'], true)) {
    echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit;
}

$action = $_REQUEST['action'] ?? 'complete';

try {
    if ($action === 'complete') {
        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        if (empty($payload['items'])) {
            echo json_encode(['ok'=>false,'error'=>'Cart is empty']); exit;
        }

        $customerNo = Customer::ensureWalkIn(); // walk-in default (self-heals if seed row missing)
        if (!empty($payload['customer_name'])) {
            $customerNo = Customer::findOrCreateGuest(
                (string)$payload['customer_name'],
                (string)($payload['customer_email'] ?? ''),
                (string)($payload['customer_phone'] ?? ''),
                (string)($payload['customer_address'] ?? '')
            );
        }

        $saleNo = Sale::create(
            $payload['items'],
            $customerNo,
            'POS',
            (string)($payload['payment_method'] ?? 'Cash'),
            (int)($_SESSION['admin_user_no'] ?? 0) ?: null
        );
        $sale = Sale::find($saleNo);

        echo json_encode([
            'ok'      => true,
            'success' => true,
            'sale_no' => $saleNo,
            'sale_id' => $sale['sale_id'] ?? null,
        ]);
        exit;
    }
    echo json_encode(['ok'=>false,'error'=>'Unknown action']);
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'success'=>false,'error'=>$e->getMessage(),'message'=>$e->getMessage()]);
}
