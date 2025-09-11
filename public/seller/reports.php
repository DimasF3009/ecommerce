<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'penjual') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/vendor/autoload.php';
use Dompdf\Dompdf;

$seller_id = $_SESSION['user']['id'];
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// --- Ambil total penjualan ---
$total_sales_res = $conn->query("
    SELECT SUM(oi.price_at_purchase*oi.quantity) AS total_sales
    FROM order_item oi
    JOIN product p ON oi.product_id=p.id
    JOIN transaction t ON oi.transaction_id=t.id
    WHERE p.seller_id='$seller_id'
      AND t.status='selesai'
      AND MONTH(t.created_at)=$month
      AND YEAR(t.created_at)=$year
");
$total_sales = $total_sales_res ? $total_sales_res->fetch_assoc()['total_sales'] ?? 0 : 0;

// --- Produk terlaris ---
$best_seller_res = $conn->query("
    SELECT p.name, SUM(oi.quantity) AS sold_qty
    FROM order_item oi
    JOIN product p ON oi.product_id=p.id
    JOIN transaction t ON oi.transaction_id=t.id
    WHERE p.seller_id='$seller_id'
      AND t.status='selesai'
      AND MONTH(t.created_at)=$month
      AND YEAR(t.created_at)=$year
    GROUP BY p.id
    ORDER BY sold_qty DESC
    LIMIT 5
");
$best_seller_data = $best_seller_res ? $best_seller_res->fetch_all(MYSQLI_ASSOC) : [];

// --- Total pesanan selesai ---
$total_orders_res = $conn->query("
    SELECT COUNT(DISTINCT t.id) AS total_orders
    FROM transaction t
    JOIN order_item oi ON t.id=oi.transaction_id
    JOIN product p ON oi.product_id=p.id
    WHERE p.seller_id='$seller_id'
      AND t.status='selesai'
      AND MONTH(t.created_at)=$month
      AND YEAR(t.created_at)=$year
");
$total_orders = $total_orders_res ? $total_orders_res->fetch_assoc()['total_orders'] ?? 0 : 0;

// --- Data chart mengikuti filter bulan & tahun ---
$sales_chart_res = $conn->query("
    SELECT SUM(oi.price_at_purchase*oi.quantity) AS total
    FROM order_item oi
    JOIN product p ON oi.product_id=p.id
    JOIN transaction t ON oi.transaction_id=t.id
    WHERE p.seller_id='$seller_id'
      AND t.status='selesai'
      AND MONTH(t.created_at)=$month
      AND YEAR(t.created_at)=$year
");
$sales_chart = $sales_chart_res ? $sales_chart_res->fetch_assoc()['total'] ?? 0 : 0;
$monthName = date("F", mktime(0,0,0,$month,1));

// --- Export PDF ---
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    ob_start();
    include __DIR__ . '/report_template.php'; // Ringkasan + tabel
    $html = ob_get_clean();

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("laporan_penjualan_{$month}_{$year}.pdf", ["Attachment" => false]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Penjualan</title>
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/reports.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="dashboard-container">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main-content">
<h2>Laporan Penjualan</h2>

<!-- Filter Bulan & Tahun -->
<form method="GET" class="filter-form">
    <label>Bulan:</label>
    <select name="month">
        <?php for ($m=1;$m<=12;$m++): ?>
            <option value="<?= $m ?>" <?= $m==$month?'selected':'' ?>><?= date("F", mktime(0,0,0,$m,1)) ?></option>
        <?php endfor; ?>
    </select>

    <label>Tahun:</label>
    <select name="year">
        <?php for ($y=date('Y');$y>=date('Y')-5;$y--): ?>
            <option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option>
        <?php endfor; ?>
    </select>

    <button type="submit">Terapkan</button>
    <button type="submit" name="export" value="pdf">Export PDF</button>
</form>

<!-- Ringkasan -->
<div class="summary-cards">
    <div class="summary-card">
        <h3>Total Penjualan</h3>
        <p>Rp <?= number_format($total_sales,0,',','.') ?></p>
    </div>
    <div class="summary-card">
        <h3>Total Pesanan Selesai</h3>
        <p><?= $total_orders ?></p>
    </div>
</div>

<!-- Chart Web Preview -->
<div class="card">
<h3>Grafik Penjualan Bulan <?= $monthName ?> <?= $year ?></h3>
<canvas id="salesChart"></canvas>
</div>

<!-- Produk Terlaris -->
<div class="card">
<h3>Produk Terlaris Bulan <?= $monthName ?> <?= $year ?></h3>
<table class="styled-table">
<thead>
<tr><th>Nama Produk</th><th>Jumlah Terjual</th></tr>
</thead>
<tbody>
<?php if(count($best_seller_data)>0): foreach($best_seller_data as $b): ?>
<tr><td><?= htmlspecialchars($b['name']) ?></td><td><?= $b['sold_qty'] ?></td></tr>
<?php endforeach; else: ?>
<tr><td colspan="2" style="text-align:center">Belum ada data.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div></div>

<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx,{
    type:'bar',
    data:{
        labels:['<?= $monthName ?>'],
        datasets:[{
            label:'Total Penjualan',
            data:[<?= $sales_chart ?>],
            backgroundColor:'#4CAF50'
        }]
    },
    options:{
        responsive:true,
        plugins:{legend:{display:false}},
        scales:{y:{beginAtZero:true,ticks:{callback:value=>'Rp '+value.toLocaleString('id-ID')}}}
    }
});
</script>
</body>
</html>
