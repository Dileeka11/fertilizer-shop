<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');
if (!Auth::isStaff() || $_SESSION['admin_role'] !== 'owner') {
    echo json_encode(['ok'=>false,'error'=>'Owner only']); exit;
}

$action = $_REQUEST['action'] ?? '';
try {
    switch ($action) {
        case 'list':
            echo json_encode(['ok'=>true,'data'=>User::all()]);
            break;
        case 'get':
            echo json_encode(['ok'=>true,'data'=>User::find((int)$_REQUEST['user_no'])]);
            break;
        case 'create':
            $id = User::create($_POST);
            echo json_encode(['ok'=>true,'user_no'=>$id]);
            break;
        case 'update':
            $n = User::update((int)$_POST['user_no'], $_POST);
            echo json_encode(['ok'=>true,'affected'=>$n]);
            break;
        case 'set_status':
            $n = User::setStatus((int)$_POST['user_no'], (string)$_POST['status']);
            echo json_encode(['ok'=>true,'affected'=>$n]);
            break;
        case 'delete':
            $n = User::delete((int)$_POST['user_no']);
            echo json_encode(['ok'=>true,'affected'=>$n]);
            break;
        default:
            echo json_encode(['ok'=>false,'error'=>'Unknown action']);
    }
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
