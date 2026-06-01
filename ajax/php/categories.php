<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');
if (!Auth::isStaff()) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }

$action = $_REQUEST['action'] ?? '';
try {
    switch ($action) {
        case 'list':
            echo json_encode(['ok'=>true,'data'=>Category::all()]);
            break;
        case 'get':
            echo json_encode(['ok'=>true,'data'=>Category::find((int)$_REQUEST['category_id'])]);
            break;
        case 'create':
            $name = trim((string)($_POST['category_name'] ?? ''));
            if ($name === '') { echo json_encode(['ok'=>false,'error'=>'Category name is required']); break; }
            if (Category::findByName($name)) { echo json_encode(['ok'=>false,'error'=>'Category already exists']); break; }
            $id = Category::create($_POST);
            echo json_encode(['ok'=>true,'category_id'=>$id]);
            break;
        case 'update':
            $id   = (int)($_POST['category_id'] ?? 0);
            $name = trim((string)($_POST['category_name'] ?? ''));
            if ($name === '') { echo json_encode(['ok'=>false,'error'=>'Category name is required']); break; }
            $existing = Category::findByName($name);
            if ($existing && (int)$existing['category_id'] !== $id) {
                echo json_encode(['ok'=>false,'error'=>'Another category already uses this name']); break;
            }
            $n = Category::update($id, $_POST);
            echo json_encode(['ok'=>true,'affected'=>$n]);
            break;
        case 'delete':
            $id = (int)($_POST['category_id'] ?? 0);
            $count = Category::productCount($id);
            if ($count > 0) {
                echo json_encode(['ok'=>false,'error'=>"Cannot delete: {$count} product(s) are using this category"]); break;
            }
            $n = Category::delete($id);
            echo json_encode(['ok'=>true,'affected'=>$n]);
            break;
        default:
            echo json_encode(['ok'=>false,'error'=>'Unknown action']);
    }
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
