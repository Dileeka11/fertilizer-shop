<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config.php';
$product_no = $_GET['product_no'] ?? 0;
$stmt = $conn->prepare("DELETE FROM products WHERE product_no = ?");
$stmt->bind_param("i", $product_no);
$stmt->execute();
header("Location: inventory.php");
exit;
?>