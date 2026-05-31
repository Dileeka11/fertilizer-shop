<?php
/**
 * AJAX endpoint: products
 *   ?action=list[&category_slug=...&search=...&limit=...]
 *   ?action=get&product_no=NN
 *   ?action=create        (POST, staff)
 *   ?action=update        (POST, staff)
 *   ?action=delete        (POST, staff)
 *   ?action=set_discount  (POST, staff)
 */
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            echo json_encode([
                'ok'   => true,
                'data' => Product::all([
                    'category_slug' => $_GET['category_slug'] ?? '',
                    'search'        => $_GET['search']        ?? '',
                    'limit'         => (int)($_GET['limit']   ?? 0),
                    'active_only'   => true,
                ]),
            ]);
            break;

        case 'get':
            $p = Product::find((int)($_REQUEST['product_no'] ?? 0));
            if (!$p) { echo json_encode(['ok' => false, 'error' => 'Not found']); break; }
            $p['details'] = Product::details((int)$p['product_no'], (int)$p['category_id']);
            echo json_encode(['ok' => true, 'data' => $p]);
            break;

        case 'create':
            if (!Auth::isStaff()) throw new RuntimeException('Unauthorized');
            $id = Product::create($_POST);
            echo json_encode(['ok' => true, 'product_no' => $id]);
            break;

        case 'update':
            if (!Auth::isStaff()) throw new RuntimeException('Unauthorized');
            $n = Product::update((int)$_POST['product_no'], $_POST);
            echo json_encode(['ok' => true, 'affected' => $n]);
            break;

        case 'delete':
            if (!Auth::isStaff()) throw new RuntimeException('Unauthorized');
            $n = Product::delete((int)$_POST['product_no']);
            echo json_encode(['ok' => true, 'affected' => $n]);
            break;

        case 'set_discount':
            if (!Auth::isStaff()) throw new RuntimeException('Unauthorized');
            $n = Product::setDiscount((int)$_POST['product_no'], (float)$_POST['discount']);
            echo json_encode(['ok' => true, 'affected' => $n]);
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
