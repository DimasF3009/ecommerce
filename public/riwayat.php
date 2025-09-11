<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// --- Auth check ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'pembeli') {
    header("Location: login.php");
    exit;
}

$user     = $_SESSION['user'];
$buyer_id = $user['id'];

// ===== Handle Submit Rating =====
if (isset($_POST['submit_rating'])) {
    $product_id = $_POST['product_id'];
    $rating     = (int) $_POST['rating'];
    $review     = trim($_POST['review']);

    if ($rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("
            INSERT INTO rating (id, product_id, buyer_id, rating, review, created_at)
            VALUES (UUID(), ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review), created_at = NOW()
        ");
        $stmt->bind_param("ssis", $product_id, $buyer_id, $rating, $review);
        $stmt->execute();
    }
    header("Location: riwayat.php"); 
    exit;
}

// ===== Ambil transaksi user =====
$stmt = $conn->prepare("
    SELECT id, total_price, shipping_cost, status, created_at
    FROM transaction
    WHERE buyer_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("s", $buyer_id);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ===== Fungsi ambil item + rating =====
function getTransactionItems($conn, $trxId, $buyer_id) {
    $stmt = $conn->prepare("
        SELECT 
            oi.id, oi.quantity, oi.price_at_purchase, 
            p.id AS product_id, p.name, p.image_url,
            r.rating AS user_rating, r.review AS user_review
        FROM order_item oi
        JOIN product p ON oi.product_id = p.id
        LEFT JOIN rating r 
            ON r.product_id = p.id AND r.buyer_id = ?
        WHERE oi.transaction_id = ?
    ");
    $stmt->bind_param("ss", $buyer_id, $trxId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Riwayat Transaksi - SNACK.IDN</title>
  <link rel="stylesheet" href="../assets/css/base.css" />
  <link rel="stylesheet" href="../assets/css/riwayat.css" />
  <style>
    .stars { display:flex; flex-direction: row-reverse; gap:5px; }
    .stars input { display:none; }
    .stars label { cursor:pointer; font-size: 1.2rem; }
    .stars input:checked ~ label { color: gold; }
    .rated { color: gold; }
    .review-box { margin-top: 4px; font-style: italic; font-size: 0.9rem; }
  </style>
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
            <?php $items = getTransactionItems($conn, $trx['id'], $buyer_id); ?>
            
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
                <?php if ($items): ?>
                  <?php foreach ($items as $item): ?>
                    <div class="item-row">
                      <div class="item-info">
                        <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                        <span class="item-qty">x<?= (int) $item['quantity'] ?></span>
                        <span class="item-price">Rp <?= number_format($item['price_at_purchase'], 0, ',', '.') ?></span>
                      </div>

                      <div class="item-rating">
                        <?php if ($item['user_rating']): ?>
                          <!-- Sudah ada rating -->
                          <p class="rated">Rating kamu: <?= str_repeat("⭐", (int)$item['user_rating']) ?></p>
                          <?php if (!empty($item['user_review'])): ?>
                            <p class="review-box">"<?= htmlspecialchars($item['user_review']) ?>"</p>
                          <?php endif; ?>
                        <?php elseif ($trx['status'] === 'selesai'): ?>
                          <!-- Belum ada rating, form muncul -->
                          <form method="POST" class="rating-form">
                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                            
                            <div class="stars">
                              <?php for ($i=5; $i>=1; $i--): ?>
                                <input type="radio" id="star<?= $i ?>-<?= $item['product_id'] ?>" name="rating" value="<?= $i ?>" required>
                                <label for="star<?= $i ?>-<?= $item['product_id'] ?>">⭐</label>
                              <?php endfor; ?>
                            </div>
                            
                            <textarea name="review" placeholder="Tulis ulasan..." rows="2"></textarea>
                            <button type="submit" name="submit_rating">Kirim</button>
                          </form>
                        <?php endif; ?>
                      </div>

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
