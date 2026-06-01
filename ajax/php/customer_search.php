<?php
/**
 * AJAX endpoint for the POS customer live-search.
 *   ?action=search&q=<term>  ->  { ok:true, data:[{customer_no,customer_id,name,email,phone,address}, ...] }
 */
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

if (!Auth::isStaff() || !in_array($_SESSION['admin_role'] ?? '', ['cashier','owner'], true)) {
    echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit;
}

$action = $_REQUEST['action'] ?? 'search';
try {
    if ($action === 'search') {
        $term = (string)($_REQUEST['q'] ?? '');
        echo json_encode(['ok'=>true, 'data'=>Customer::search($term)]);
        exit;
    }
    echo json_encode(['ok'=>false,'error'=>'Unknown action']);
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
