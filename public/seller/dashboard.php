<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'penjual') {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// --- Data penjual ---
$seller_id = $_SESSION['user']['id'];

// --- Ambil produk penjual ---
$stmtProducts = $conn->prepare("SELECT id, name, price, stock FROM product WHERE seller_id = ?");
$stmtProducts->bind_param("i", $seller_id);
$stmtProducts->execute();
$products = $stmtProducts->get_result();

// --- Pesanan baru (status pending) ---
$stmtOrders = $conn->prepare("
    SELECT COUNT(DISTINCT t.id) as total
    FROM transaction t
    JOIN order_item oi ON t.id = oi.transaction_id
    JOIN product p ON oi.product_id = p.id
    WHERE p.seller_id = ? AND t.status = 'pending'
");
$stmtOrders->bind_param("i", $seller_id);
$stmtOrders->execute();
$newOrders = $stmtOrders->get_result()->fetch_assoc()['total'] ?? 0;

// --- Total penjualan (status selesai) ---
$stmtSales = $conn->prepare("
    SELECT SUM(oi.price_at_purchase * oi.quantity) as total
    FROM transaction t
    JOIN order_item oi ON t.id = oi.transaction_id
    JOIN product p ON oi.product_id = p.id
    WHERE p.seller_id = ? AND t.status = 'selesai'
");
$stmtSales->bind_param("i", $seller_id);
$stmtSales->execute();
$totalSales = $stmtSales->get_result()->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Penjual - SNACK.IDN</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/base.css">
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main -->
    <main class="main-content">
      <header class="main-header">
        <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['user']['name']) ?></h1>
      </header>

      <section class="cards">
        <div class="card">
          <h3>Total Produk</h3>
          <p><?= $products->num_rows ?></p>
        </div>
        <div class="card">
          <h3>Pesanan Baru</h3>
          <p><?= $newOrders ?></p>
        </div>
        <div class="card">
          <h3>Total Penjualan</h3>
          <p>Rp <?= number_format($totalSales, 0, ',', '.') ?></p>
        </div>
      </section>

      <section class="product-list">
        <h2>Produk Terbaru</h2>
        <table>
          <thead>
            <tr>
              <th>Nama Produk</th>
              <th>Harga</th>
              <th>Stok</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $products->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                <td><?= (int)$row['stock'] ?></td>
                <td>
                  <a href="products.php?action=edit&id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                  <a href="products.php?action=delete&id=<?= $row['id'] ?>" class="btn-delete">Hapus</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</body>
</html>
