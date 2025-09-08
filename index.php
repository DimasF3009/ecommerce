<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Product.php';

$user = $_SESSION['user'] ?? null;

$productModel = new Product($conn);
$products = $productModel->getAll();
// --- Search produk ---
$searchQuery = $_GET['search'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>KueCommerce</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <!-- Header -->
  <header class="header">
      <div class="container header__content">
        <a href="#" class="logo">SNACK.IDN</a>
        <!-- Nav pindah ke bawah -->
        <nav class="nav nav--bottom">
          <a href="#" class="nav__link">Beranda</a>
          <a href="public/product.php" class="nav__link">Produk</a>
        </nav>
          <div class="header__top">
            <!-- Search lebih besar -->
            <?php include __DIR__ . '/partials/search-guest.php'; ?>

            <div class="header__actions">
              <?php if ($user): ?>
                <a href="cart.php">🛒</a>
              <?php else: ?>
                <a href="public/login.php" onclick="return confirm('Silakan login terlebih dahulu untuk melihat keranjang.')">🛒</a>
              <?php endif; ?>
              <a href="public/login.php" class="icon-btn">👤</a>
            </div>
          </div>
      </div>
  </header>

  <!-- Main -->
  <main>
    <!-- Hero -->
    <section class="hero">
      <div class="container hero__content">
        <div class="hero__text">
          <h1>Nikmati Jajanan Nusantara</h1>
          <p>Temukan berbagai jajanan khas Indonesia dari UMKM lokal. 
             Pesan dengan mudah dan rasakan kelezatannya!</p>
          <a href="public/product.php" class="btn">Belanja Sekarang</a>
        </div>
        <img src="assets/img/68b062c12ba0f.jpeg" 
             alt="Jajanan Nusantara" class="hero__image" />
      </div>
    </section>

    <!-- Produk -->
    <section class="products">
      <div class="container">
        <h2 class="section-title">Produk Terbaru</h2>
        <div class="grid">
          <?php while($p = $products->fetch_assoc()): ?>
            <article class="product-card">
              <a href="public/product_detail.php?id=<?= $p['id'] ?>">
                <img src="<?= htmlspecialchars($p['image_url']) ?>" 
                     alt="<?= htmlspecialchars($p['name']) ?>" 
                     class="product-image" />
                <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                <p class="product-price">Rp <?= number_format($p['price'],0,',','.') ?></p>
              </a>

              <?php if (!empty($user)): ?>
                <!-- Sudah login -->
                <form action="public/add_to_cart.php" method="POST">
                  <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                  <button type="submit" class="add-to-cart">Tambah ke Keranjang</button>
                </form>
              <?php else: ?>
                <!-- Tamu -->
                <button type="button" class="add-to-cart" onclick="alertLogin()">
                  Tambah ke Keranjang
                </button>
              <?php endif; ?>
            </article>
          <?php endwhile; ?>
        </div>
      </div>
    </section>
  
  </main>

  <!-- Footer -->
  <footer class="footer">
      <div class="container footer__grid">
        <div class="footer__brand">
          <h2>Snack.IDN</h2>
          <p>Nikmati jajanan khas Nusantara langsung dari UMKM lokal.</p>
        </div>

        <div>
          <h3>Help</h3>
          <ul>
            <li><a href="#">Customer Support</a></li>
            <li><a href="#">Delivery</a></li>
          </ul>
        </div>

        <div>
          <h3>FAQ</h3>
          <ul>
            <li><a href="#">Akun</a></li>
            <li><a href="#">Pesanan</a></li>
          </ul>
        </div>

        <div>
          <h3>Pembayaran</h3>
          <div class="payment">
            <img src="assets/pict/qris.png" alt="qris" />
          </div>
        </div>
      </div>
      <p class="copyright">© 2025 SNACK.IDN</p>
  </footer>

  <!-- Script -->
  <script>
    function alertLogin() {
      alert("Silakan login terlebih dahulu untuk menambahkan ke keranjang.");
      window.location.href = "public/login.php";
    }
  </script>
</body>
</html>
