<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Ambil transaksi + pembeli + toko (penjual) + produk
$sql = "SELECT 
            t.id,
            u.name AS buyer_name,
            t.shipping_address,
            t.payment_method,
            t.total_price,
            t.status,
            t.created_at,
            GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS seller_names,
            GROUP_CONCAT(CONCAT(p.name, ' x', oi.quantity) SEPARATOR ', ') AS product_list
        FROM transaction t
        JOIN user u ON t.buyer_id = u.id
        LEFT JOIN order_item oi ON t.id = oi.transaction_id
        LEFT JOIN product p ON oi.product_id = p.id
        LEFT JOIN user s ON p.seller_id = s.id
        GROUP BY t.id
        ORDER BY t.created_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monitoring Transaksi - SNACK.IDN</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/transaksi.css">
</head>
<body>
  <div class="dashboard-container">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="main-content">
            <header class="main-header">
                <h1>📊 Monitoring Transaksi</h1>
            </header>

            <section class="content-section transaksi-list">
              <table>
                <thead>
                  <tr>
                    <th>ID Transaksi</th>
                    <th>Pembeli</th>
                    <th>Alamat</th>
                    <th>Toko</th>
                    <th>Produk</th>
                    <th>Metode Bayar</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                      <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['buyer_name']) ?></td>
                        <td><?= $row['shipping_address'] ? htmlspecialchars($row['shipping_address']) : '-' ?></td>
                        <td><?= $row['seller_names'] ? htmlspecialchars($row['seller_names']) : '-' ?></td>
                        <td><?= $row['product_list'] ? htmlspecialchars($row['product_list']) : '-' ?></td>
                        <td><?= htmlspecialchars(ucfirst($row['payment_method'])) ?></td>
                        <td>Rp <?= number_format($row['total_price'], 0, ',', '.') ?></td>
                        <td>
                          <span class="status <?= $row['status'] ?>">
                            <?= ucfirst($row['status']) ?>
                          </span>
                        </td>
                        <td><?= $row['created_at'] ?></td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                      <tr>
                        <td colspan="9" style="text-align:center;">Belum ada transaksi</td>
                      </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </section>
        </main>
  </div>
</body>
</html>
