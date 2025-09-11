<?php $monthName = date("F", mktime(0,0,0,$month,1)); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
h2,h3{text-align:center;margin:0;}
table{width:100%;border-collapse:collapse;margin-top:15px;}
table,th,td{border:1px solid #333;}
th,td{padding:6px;text-align:center;}
.summary{margin-top:15px;}
.summary p{margin:4px 0;}
</style>
</head>
<body>
<h2>Laporan Penjualan</h2>
<h3>Bulan <?= $monthName ?> <?= $year ?></h3>

<div class="summary">
<p><strong>Total Penjualan:</strong> Rp <?= number_format($total_sales,0,',','.') ?></p>
<p><strong>Total Pesanan Selesai:</strong> <?= $total_orders ?></p>
</div>

<h3>Produk Terlaris</h3>
<table>
<thead><tr><th>No</th><th>Nama Produk</th><th>Jumlah Terjual</th></tr></thead>
<tbody>
<?php if(count($best_seller_data)>0): $no=1; foreach($best_seller_data as $b): ?>
<tr><td><?= $no++ ?></td><td><?= htmlspecialchars($b['name']) ?></td><td><?= $b['sold_qty'] ?></td></tr>
<?php endforeach; else: ?>
<tr><td colspan="3">Belum ada data.</td></tr>
<?php endif; ?>
</tbody>
</table>

<p style="margin-top:30px;text-align:right;">Dicetak pada: <?= date('d-m-Y H:i') ?></p>
</body>
</html>
