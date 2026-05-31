<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');
if (!Auth::isStaff()) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }

$action = $_REQUEST['action'] ?? '';
try {
    switch ($action) {
        case 'list':
            echo json_encode(['ok'=>true,'data'=>Supplier::all()]);
            break;
        case 'get':
            echo json_encode(['ok'=>true,'data'=>Supplier::find((int)$_REQUEST['supplier_no'])]);
            break;
        case 'create':
            $id = Supplier::create($_POST);
            echo json_encode(['ok'=>true,'supplier_no'=>$id]);
            break;
        case 'update':
            $n = Supplier::update((int)$_POST['supplier_no'], $_POST);
            echo json_encode(['ok'=>true,'affected'=>$n]);
            break;
        case 'delete':
            $n = Supplier::delete((int)$_POST['supplier_no']);
            echo json_encode(['ok'=>true,'affected'=>$n]);
            break;
        default:
            echo json_encode(['ok'=>false,'error'=>'Unknown action']);
    }
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
