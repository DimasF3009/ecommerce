<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// --- Auth check ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'pembeli') {
    header("Location: login.php");
    exit;
}

$user    = $_SESSION['user'];
$buyerId = $user['id'];

// --- Ambil transaksi user ---
$stmt = $conn->prepare("
    SELECT id, total_price, shipping_cost, status, created_at
    FROM transaction
    WHERE buyer_id = ?
    ORDER BY created_at DESC
");
if (!$stmt) {
    die("Query error (transaction): " . $conn->error);
}
$stmt->bind_param("s", $buyerId);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KueCommerce - Riwayat Transaksi</title>
  <link rel="stylesheet" href="../assets/css/base.css" />
  <link rel="stylesheet" href="../assets/css/riwayat.css" />
</head>
<body>

  <!-- Header -->
  <header class="header">
    <div class="container header__content">
      <a href="homepage.php" class="logo">SNACK.IDN</a>
      <nav class="nav nav--bottom">
        <a href="homepage.php" class="nav__link">Beranda</a>
        <a href="product.php" class="nav__link">Produk</a>
        <a href="riwayat.php" class="nav__link">Riwayat</a>
      </nav>

      <div class="header__top">
        <?php include __DIR__ . '/../partials/search.php'; ?>
        <div class="header__actions">
          <a href="cart.php" class="icon-btn" aria-label="Keranjang">🛒</a>
          <a href="profile.php"><span class="icon-profile">👤 <?= htmlspecialchars($user['name']) ?></span></a>
          <a href="logout.php" class="nav__link">Logout</a>
        </div>
      </div>
    </div>
  </header>
  
  <!-- Main -->
  <main>
    <section class="container">
      <h2 class="section-title">Riwayat Transaksi</h2>

      <?php if (empty($transactions)): ?>
        <p>Belum ada transaksi.</p>
      <?php else: ?>
        <div class="grid">
          <?php foreach ($transactions as $trx): ?>
            <article class="transaction-card">
              <div class="transaction-header">
                <h3>Order #<?= htmlspecialchars($trx['id']) ?></h3>
                <span class="status <?= strtolower($trx['status']) ?>">
                  <?= htmlspecialchars($trx['status']) ?>
                </span>
              </div>

              <p><strong>Total:</strong> Rp <?= number_format($trx['total_price'] + $trx['shipping_cost'], 0, ',', '.') ?></p>
              <p><strong>Ongkir:</strong> Rp <?= number_format($trx['shipping_cost'], 0, ',', '.') ?></p>
              <p><strong>Tanggal:</strong> <?= date("d M Y H:i", strtotime($trx['created_at'])) ?></p>

              <div class="item-list">
                <?php
                $items = [];
                $stmtItems = $conn->prepare("
                    SELECT oi.quantity, oi.price_at_purchase, p.name 
                    FROM order_item oi
                    JOIN product p ON oi.product_id = p.id
                    WHERE oi.transaction_id = ?
                ");
                if ($stmtItems) {
                    $stmtItems->bind_param("s", $trx['id']);
                    $stmtItems->execute();
                    $items = $stmtItems->get_result()->fetch_all(MYSQLI_ASSOC);
                }
                ?>

                <?php if ($items): ?>
                  <?php foreach ($items as $item): ?>
                    <div class="item-row">
                      <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                      <span class="item-qty">x<?= (int) $item['quantity'] ?></span>
                      <span class="item-price">Rp <?= number_format($item['price_at_purchase'], 0, ',', '.') ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p class="no-items">Tidak ada produk</p>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
  
  <!-- Footer -->
  <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
