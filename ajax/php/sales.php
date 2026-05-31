<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

if (!Auth::isStaff()) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }

$action = $_REQUEST['action'] ?? '';
try {
    switch ($action) {
        case 'cancel':
            $saleNo = (int)($_POST['sale_no'] ?? 0);
            if (!$saleNo) {
                $sid = (string)($_POST['sale_id'] ?? '');
                $s = $sid ? Sale::findById($sid) : null;
                if ($s) $saleNo = (int)$s['sale_no'];
            }
            if (!$saleNo) { echo json_encode(['ok'=>false,'error'=>'Sale not found']); break; }
            $reason = (string)($_POST['reason'] ?? '');
            $ok = Sale::cancel($saleNo, $reason);
            echo json_encode(['ok' => $ok, 'message' => $ok ? 'Sale cancelled' : 'Sale was not cancelled (already cancelled?)']);
            break;
        default:
            echo json_encode(['ok'=>false,'error'=>'Unknown action']);
    }
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
