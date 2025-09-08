<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Rating.php';

$user = $_SESSION['user'] ?? null;

$productModel = new Product($conn);
$products = $productModel->getAll();

// --- Search produk ---
$searchQuery = $_GET['search'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KueCommerce - Beranda</title>
  <link rel="stylesheet" href="../assets/css/base.css" />
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>

  <!-- Header -->
  <header class="header">
    <div class="container header__content">
      <a href="homepage.php" class="logo">SNACK.IDN</a>
      <!-- Nav -->
      <nav class="nav nav--bottom">
        <a href="#" class="nav__link">Beranda</a>
        <a href="product.php" class="nav__link">Produk</a>
        <a href="riwayat.php" class="nav__link">Riwayat</a>
      </nav>

      <div class="header__top">
        <!-- Search -->
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
    <!-- Hero Section -->
    <section class="hero">
      <div class="container hero__content">
        <div class="hero__text">
          <h1>Nikmati Jajanan Nusantara</h1>
          <p>Temukan berbagai jajanan khas Indonesia dari UMKM lokal. Pesan dengan mudah dan rasakan kelezatannya!</p>
          <a href="product.php" class="btn">Belanja Sekarang</a>
        </div>
        <img src="../assets/pict/logo.png" alt="Jajanan Nusantara" class="hero__image" />
      </div>
    </section>

    <!-- Produk Terbaru -->
    <section class="products">
      <div class="container">
        <h2 class="section-title">Produk Terbaru</h2>
            <div class="grid">
                <?php while($p = $products->fetch_assoc()): ?>
                    <?php
                    $ratingModel = new Rating($conn);
                    $avg = $ratingModel->getAverage($p['id']);
                    $imagePath = !empty($p['image_url']) ? "../" . $p['image_url'] : "../assets/img/no-image.png";
                    ?>
                    <article class="product-card">
                        <a href="product_detail.php?id=<?= $p['id'] ?>">
                            <img src="<?= $imagePath ?>" 
                                alt="<?= htmlspecialchars($p['name']) ?>" 
                                class="product-image" />
                            <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                            <p class="product-price">Rp <?= number_format($p['price'],0,',','.') ?></p>
                        </a>
                        <p class="product-rating">
                            ⭐ <?= $avg['avg_rating'] ? number_format($avg['avg_rating'],1) : '0' ?>/5 
                            (<?= $avg['total_rating'] ?>)
                        </p>
                        <button class="add-to-cart" data-id="<?= $p['id'] ?>">Tambah ke Keranjang</button>
                    </article>
                <?php endwhile; ?>
            </div>
      </div>
    </section>

  </main>
  <!-- Footer -->
  <?php include __DIR__ . '/../partials/footer.php'; ?>

  <script>
    // Tambah ke keranjang
    document.querySelectorAll('.add-to-cart').forEach(btn => {
      btn.addEventListener('click', () => {
        const productId = btn.dataset.id;
        fetch('cart.php', {
          method: 'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded'},
          body: `add=${productId}`
        }).then(res => res.text())
          .then(msg => alert("Produk berhasil ditambahkan ke keranjang!"))
          .catch(err => console.error(err));
      });
    });
  </script>
</body>
</html>
