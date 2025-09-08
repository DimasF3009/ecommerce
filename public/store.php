<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';

$productModel = new Product($conn);

$user = $_SESSION['user'] ?? null;

if (!isset($_GET['id'])) {
    echo "Toko tidak ditemukan.";
    exit;
}

$seller_id = $_GET['id'];

// Ambil data toko (penjual)
$stmt = $conn->prepare("SELECT id, name, email, store_logo, address 
                        FROM user 
                        WHERE id = ? AND role = 'penjual'");
$stmt->bind_param("s", $seller_id);
$stmt->execute();
$seller = $stmt->get_result()->fetch_assoc();

if (!$seller) {
    echo "Toko tidak ditemukan.";
    exit;
}

// Ambil semua produk milik penjual
$stmt = $conn->prepare("SELECT * FROM product WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $seller_id);
$stmt->execute();
$products = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($seller['name']) ?> - SNACK.IDN</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/store.css">
</head>
<body>

    <!-- Header -->
    <header class="header">
        <div class="container header__content">
            <a href="<?= $user ? 'homepage.php' : '../index.php' ?>" class="logo">SNACK.IDN</a>
            <nav class="nav nav--bottom">
                <a href="<?= $user ? 'homepage.php' : '../index.php' ?>" class="nav__link">Beranda</a>
                <a href="product.php" class="nav__link">Produk</a>
                <a href="riwayat.php" class="nav__link">Riwayat</a>
            </nav>
            <div class="header__top">
                <div class="header__actions">
                    <a href="<?= $user ? 'cart.php' : 'login.php' ?>">🛒</a>
                    <?php if ($user): ?>
                        <a href="profile.php"><span class="icon-profile">👤 <?= htmlspecialchars($user['name']) ?></span></a>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="icon-btn">👤</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main -->
    <main class="main-content">
        <div class="container">

            <!-- Informasi Toko -->
            <section class="store-profile">
                <div class="store-header">
                    <img src="<?= !empty($seller['store_logo']) 
                                ? '../' . $seller['store_logo'] 
                                : '../assets/img/store-default.png' ?>" 
                         alt="Logo <?= htmlspecialchars($seller['name']) ?>" 
                         class="store-logo">

                    <div class="store-details">
                        <h1 class="store-title"><?= htmlspecialchars($seller['name']) ?></h1>
                        <p class="store-address">
                            📍 <?= htmlspecialchars($seller['address'] ?? 'Alamat belum diatur') ?>
                        </p>
                    </div>
                </div>
            </section>

            <!-- Produk Toko -->
            <section class="store-products">
                <h2 class="section-title">Produk dari Toko</h2>
                <div class="product-grid">
                    <?php if ($products->num_rows > 0): ?>
                        <?php foreach ($products as $p): ?>
                        <div class="product-card">
                            <img src="../<?= htmlspecialchars($p['image_url'] ?? 'assets/img/no-image.png') ?>" 
                                 alt="<?= htmlspecialchars($p['name']) ?>" 
                                 class="product-image" />
                            <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                            <p class="product-price">Rp <?= number_format($p['price'], 0, ',', '.') ?></p>
                            <a href="product_detail.php?id=<?= $p['id'] ?>" class="btn">Lihat Detail</a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-product">Belum ada produk dari toko ini.</p>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
