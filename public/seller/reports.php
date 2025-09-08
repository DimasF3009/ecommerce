<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'penjual') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$seller_id = $_SESSION['user']['id'];

// --- Ambil filter bulan & tahun ---
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// --- Total penjualan (periode terpilih) ---
$total_sales_res = $conn->query("
    SELECT SUM(oi.price_at_purchase * oi.quantity) AS total_sales
    FROM order_item oi
    JOIN product p ON oi.product_id=p.id
    JOIN transaction t ON oi.transaction_id=t.id
    WHERE p.seller_id='$seller_id' 
      AND t.status='selesai'
      AND MONTH(t.created_at) = $month
      AND YEAR(t.created_at) = $year
");
$total_sales = $total_sales_res->fetch_assoc()['total_sales'] ?? 0;

// --- Produk terlaris (periode terpilih) ---
$best_seller_res = $conn->query("
    SELECT p.name, SUM(oi.quantity) AS sold_qty
    FROM order_item oi
    JOIN product p ON oi.product_id=p.id
    JOIN transaction t ON oi.transaction_id=t.id
    WHERE p.seller_id='$seller_id' 
      AND t.status='selesai'
      AND MONTH(t.created_at) = $month
      AND YEAR(t.created_at) = $year
    GROUP BY p.id
    ORDER BY sold_qty DESC
    LIMIT 5
");

// --- Jumlah pesanan selesai (periode terpilih) ---
$total_orders_res = $conn->query("
    SELECT COUNT(DISTINCT t.id) AS total_orders
    FROM transaction t
    JOIN order_item oi ON t.id = oi.transaction_id
    JOIN product p ON oi.product_id=p.id
    WHERE p.seller_id='$seller_id' 
      AND t.status='selesai'
      AND MONTH(t.created_at) = $month
      AND YEAR(t.created_at) = $year
");
$total_orders = $total_orders_res->fetch_assoc()['total_orders'] ?? 0;

// --- Data penjualan bulanan sepanjang tahun terpilih ---
$sales_monthly_res = $conn->query("
    SELECT DATE_FORMAT(t.created_at, '%m') AS month, 
           SUM(oi.price_at_purchase * oi.quantity) AS total
    FROM order_item oi
    JOIN product p ON oi.product_id=p.id
    JOIN transaction t ON oi.transaction_id=t.id
    WHERE p.seller_id='$seller_id' 
      AND t.status='selesai'
      AND YEAR(t.created_at) = $year
    GROUP BY month
    ORDER BY month ASC
");

$months = [];
$totals = [];
while ($row = $sales_monthly_res->fetch_assoc()) {
    $months[] = $row['month'];
    $totals[] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Penjualan - SNACK.IDN</title>
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
            <label for="month">Bulan:</label>
            <select name="month" id="month">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                        <?= date("F", mktime(0,0,0,$m,1)) ?>
                    </option>
                <?php endfor; ?>
            </select>

            <label for="year">Tahun:</label>
            <select name="year" id="year">
                <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>

            <button type="submit">Terapkan</button>
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

        <!-- Grafik -->
        <div class="card">
            <h3>Grafik Penjualan Tahun <?= $year ?></h3>
            <canvas id="salesChart"></canvas>
        </div>

        <!-- Produk Terlaris -->
        <div class="card">
            <h3>Produk Terlaris Bulan <?= date("F", mktime(0,0,0,$month,1)) ?> <?= $year ?></h3>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jumlah Terjual</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($best_seller_res->num_rows > 0): ?>
                    <?php while($b = $best_seller_res->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['name']) ?></td>
                            <td><?= $b['sold_qty'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2" style="text-align:center;">Belum ada data.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(function($m){ return date("F", mktime(0,0,0,$m,1)); }, $months)) ?>,
        datasets: [{
            label: 'Total Penjualan',
            data: <?= json_encode($totals) ?>,
            backgroundColor: '#4CAF50'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => 'Rp ' + value.toLocaleString('id-ID')
                }
            }
        }
    }
});
</script>
</body>
</html>
