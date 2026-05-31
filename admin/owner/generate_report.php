<?php
require_once '../includes/auth_check.php';
require_once '../../vendor/fpdf/fpdf.php';
require_once '../../includes/config.php';

function getSalesData($conn, $period, $start_date, $end_date) {
    if (!empty($start_date) && !empty($end_date)) {
        $date_condition = "sale_date BETWEEN '$start_date' AND '$end_date 23:59:59'";
        $labels = ["$start_date to $end_date"];
        $sales = [];
        $total_revenue = $conn->query("SELECT IFNULL(SUM(total),0) as total FROM sales WHERE $date_condition")->fetch_assoc()['total'];
        $total_orders = $conn->query("SELECT COUNT(*) as cnt FROM sales WHERE $date_condition")->fetch_assoc()['cnt'];
        // For simplicity, keep sales array empty (we'll show only total)
        $sales_data = [$total_revenue];
        $period_header = "Period";
        $unit = "";
    } elseif ($period == '7days') {
        $date_condition = "sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $labels = ['Day 1','Day 2','Day 3','Day 4','Day 5','Day 6','Day 7'];
        $result = $conn->query("SELECT DATE(sale_date) as d, SUM(total) as t FROM sales WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(sale_date)");
        $daily = array_fill(0,7,0);
        while ($row = $result->fetch_assoc()) {
            $idx = (int)((strtotime($row['d']) - strtotime(date('Y-m-d', strtotime('-6 days')))) / 86400);
            if ($idx>=0 && $idx<7) $daily[$idx] = $row['t'];
        }
        $sales_data = $daily;
        $total_revenue = array_sum($daily);
        $total_orders = $conn->query("SELECT COUNT(*) FROM sales WHERE $date_condition")->fetch_row()[0];
        $period_header = "Days";
    } elseif ($period == '1month') {
        $date_condition = "sale_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        $labels = ['Week 1','Week 2','Week 3','Week 4'];
        $weekly = array_fill(0,4,0);
        $result = $conn->query("SELECT WEEK(sale_date,1) - WEEK(DATE_SUB(CURDATE(), INTERVAL 1 MONTH),1) + 1 as w, SUM(total) as t FROM sales WHERE $date_condition GROUP BY w");
        while ($row = $result->fetch_assoc()) {
            $idx = $row['w']-1;
            if ($idx>=0 && $idx<4) $weekly[$idx] = $row['t'];
        }
        $sales_data = $weekly;
        $total_revenue = array_sum($weekly);
        $total_orders = $conn->query("SELECT COUNT(*) FROM sales WHERE $date_condition")->fetch_row()[0];
        $period_header = "Weeks";
    } else { // 1year
        $date_condition = "sale_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $monthly = array_fill(0,12,0);
        $result = $conn->query("SELECT MONTH(sale_date) as m, SUM(total) as t FROM sales WHERE $date_condition GROUP BY MONTH(sale_date)");
        while ($row = $result->fetch_assoc()) {
            $idx = $row['m']-1;
            $monthly[$idx] = $row['t'];
        }
        $sales_data = $monthly;
        $total_revenue = array_sum($monthly);
        $total_orders = $conn->query("SELECT COUNT(*) FROM sales WHERE $date_condition")->fetch_row()[0];
        $period_header = "Months";
    }
    return [
        'labels' => $labels,
        'sales_data' => $sales_data,
        'total_revenue' => $total_revenue,
        'total_orders' => $total_orders,
        'period_header' => $period_header
    ];
}

$type = $_GET['type'] ?? 'sales';
$period = $_GET['period'] ?? '7days';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

if (!empty($start_date) && !empty($end_date)) {
    $periodDisplay = "Custom: $start_date to $end_date";
} else {
    $periodDisplay = ['7days'=>'Last 7 Days','1month'=>'Last Month','1year'=>'Last Year'][$period] ?? 'Unknown';
}

$data = getSalesData($conn, $period, $start_date, $end_date);

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'Agro City - Sales Report',0,1,'C');
        $this->SetFont('Arial','',12);
        $this->Cell(0,6,'Epaladeniya, Kuliyapitiya, Sri Lanka',0,1,'C');
        $this->Cell(0,6,'Tel: 076 115 7794',0,1,'C');
        $this->Ln(5);
        $this->SetFont('Arial','B',12);
        $this->Cell(0,6,'Period: '.$this->periodDisplay,0,1,'L');
        $this->Ln(5);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().' / Generated on '.date('Y-m-d H:i:s'),0,0,'C');
    }
}

$pdf = new PDF();
$pdf->periodDisplay = $periodDisplay;
$pdf->AddPage();
$pdf->SetFont('Arial','',11);

// Summary
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Summary',0,1);
$pdf->SetFont('Arial','',11);
$pdf->Cell(60,7,'Total Revenue:',0,0);
$pdf->Cell(60,7,'Rs. '.number_format($data['total_revenue'],2),0,1);
$pdf->Cell(60,7,'Total Orders:',0,0);
$pdf->Cell(60,7,$data['total_orders'],0,1);
$avg = $data['total_orders'] ? $data['total_revenue']/$data['total_orders'] : 0;
$pdf->Cell(60,7,'Avg Order Value:',0,0);
$pdf->Cell(60,7,'Rs. '.number_format($avg,2),0,1);
$pdf->Ln(5);

// Sales by period
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Sales by '.$data['period_header'],0,1);
$pdf->SetFont('Arial','',11);
$pdf->Cell(80,7,$data['period_header'],1,0,'C');
$pdf->Cell(50,7,'Sales (Rs.)',1,1,'C');
foreach ($data['labels'] as $i => $label) {
    $pdf->Cell(80,7,$label,1,0);
    $pdf->Cell(50,7,number_format($data['sales_data'][$i],2),1,1,'R');
}
$pdf->Ln(5);

// Top products
$top_products = $conn->query("
    SELECT p.name, SUM(si.quantity) as qty, SUM(si.quantity*si.price) as rev
    FROM sale_items si
    JOIN products p ON si.product_no = p.product_no
    GROUP BY si.product_no
    ORDER BY rev DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Top Selling Products',0,1);
$pdf->SetFont('Arial','',11);
$pdf->Cell(80,7,'Product',1,0,'C');
$pdf->Cell(40,7,'Quantity',1,0,'C');
$pdf->Cell(50,7,'Revenue',1,1,'C');
foreach ($top_products as $p) {
    $pdf->Cell(80,7,$p['name'],1,0);
    $pdf->Cell(40,7,$p['qty'],1,0,'C');
    $pdf->Cell(50,7,'Rs. '.number_format($p['rev'],2),1,1,'R');
}

$pdf->Output('D', 'AgroCity_sales_report.pdf');
?>