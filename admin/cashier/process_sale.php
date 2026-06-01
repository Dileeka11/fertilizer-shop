<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

if (!Auth::isStaff() || !in_array($_SESSION['admin_role'], ['cashier','owner'], true)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'No items']); exit;
}

try {
    // 1) An existing customer picked from live search, 2) a typed name (find/create),
    // otherwise 3) the anonymous walk-in (self-heals if the seed row is missing).
    if (!empty($input['customer_no']) && Customer::find((int)$input['customer_no'])) {
        $customerNo = (int)$input['customer_no'];
    } elseif (!empty($input['customer_name'])) {
        $customerNo = Customer::findOrCreateGuest(
            (string)$input['customer_name'],
            (string)($input['customer_email']   ?? ''),
            (string)($input['customer_phone']   ?? ''),
            (string)($input['customer_address'] ?? '')
        );
    } else {
        $customerNo = Customer::ensureWalkIn();
    }
    $saleNo = Sale::create(
        $input['items'],
        $customerNo,
        'POS',
        (string)($input['payment_method'] ?? 'Cash'),
        (int)($_SESSION['admin_user_no'] ?? 0) ?: null
    );
    $sale = Sale::find($saleNo);
    echo json_encode(['success' => true, 'sale_id' => $sale['sale_id']]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
