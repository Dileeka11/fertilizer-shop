<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config.php';

$sale_id = $_GET['sale_id'] ?? '';
$stmt = $conn->prepare("
    SELECT s.*, c.first_name, c.last_name, c.phone
    FROM sales s
    LEFT JOIN customers c ON s.customer_no = c.customer_no
    WHERE s.sale_id = ?
");
$stmt->bind_param("s", $sale_id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();
if (!$sale) die('Invoice not found.');

$items_sql = "SELECT si.*, p.name FROM sale_items si JOIN products p ON si.product_no = p.product_no WHERE si.sale_no = ?";
$stmt = $conn->prepare($items_sql);
$stmt->bind_param("i", $sale['sale_no']);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$payment_sql = "SELECT payment_method, amount FROM payments WHERE sale_no = ?";
$stmt = $conn->prepare($payment_sql);
$stmt->bind_param("i", $sale['sale_no']);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice <?php echo $sale['sale_id']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; width: 80mm; margin: 0 auto; padding: 5mm; background: white; }
        .shop-details { text-align: center; margin-bottom: 1em; }
        hr { border: none; border-top: 1px dashed #ccc; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 4px 0; }
        .total { text-align: right; font-weight: bold; margin-top: 1em; }
        .footer { text-align: center; font-size: 0.8em; margin-top: 1em; }
        .no-print { text-align: center; margin-top: 1em; }
        button { background: #2e7d32; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        @media print { .no-print { display: none; } body { margin: 0; padding: 0; } }
    </style>
</head>
<body>
    <div class="shop-details">
        <h2>Agro City</h2>
        <p>Epaladeniya, Kuliyapitiya, Sri Lanka<br>Tel: 076 115 7794</p>
    </div>
    <hr>
    <p><strong>Invoice:</strong> <?php echo $sale['sale_id']; ?><br>
    <strong>Date:</strong> <?php echo $sale['sale_date']; ?><br>
    <strong>Customer:</strong> <?php echo htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']); ?><br>
    <strong>Payment:</strong> <?php echo $payment['payment_method']; ?></p>
    <hr>
    <table>
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
        <tbody>
            <?php foreach ($items as $item): 
                $subtotal = $item['quantity'] * $item['price'];
            ?>
            <tr><td><?php echo $item['name']; ?></td><td><?php echo $item['quantity']; ?></td><td>Rs. <?php echo number_format($item['price'],2); ?></td><td>Rs. <?php echo number_format($subtotal,2); ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <hr>
    <div class="total">Total: Rs. <?php echo number_format($sale['total'],2); ?></div>
    <div class="footer">Thank you for your purchase!</div>
    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.location.href='pos.php'">New Sale</button>
    </div>
</body>
</html>