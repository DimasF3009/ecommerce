<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Rating.php';

$productModel = new Product($conn);
$ratingModel  = new Rating($conn);

$user = $_SESSION['user'] ?? null;
if (!isset($_GET['id'])) {
  echo "Produk tidak ditemukan";
  exit;
}

$product_id = $_GET['id'];
$product = $productModel->getById($product_id);
if (!$product) {
  echo "Produk tidak ditemukan";
  exit;
}

// Tambah ke keranjang
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pembeli') {
        echo "<script>alert('Login dulu untuk menambahkan ke keranjang!');</script>";
    } else {
        $buyer_id = $_SESSION['user']['id'];

        $cart = $conn->query("SELECT * FROM cart WHERE buyer_id='$buyer_id' LIMIT 1")->fetch_assoc();

        if (!$cart) {
            $conn->query("INSERT INTO cart (id, buyer_id, created_at) VALUES (UUID(), '$buyer_id', NOW())");
            $cart = $conn->query("SELECT * FROM cart WHERE buyer_id='$buyer_id' LIMIT 1")->fetch_assoc();
        }

        $cart_id = $cart['id'];

        $item = $conn->query("SELECT * FROM cart_item WHERE cart_id='$cart_id' AND product_id='$product_id' LIMIT 1")->fetch_assoc();

        if ($item) {
            $new_qty = $item['quantity'] + 1;
            $conn->query("UPDATE cart_item SET quantity=$new_qty WHERE id='" . $item['id'] . "'");
        } else {
            $conn->query("INSERT INTO cart_item (id, cart_id, product_id, quantity) VALUES (UUID(), '$cart_id', '$product_id', 1)");
        }

        echo "<script>alert('Produk berhasil ditambahkan ke keranjang!');</script>";
    }
}

// Query kategori
$catResult = $conn->query("SELECT name FROM category WHERE id = ".$product['category_id']);
$cat = $catResult->fetch_assoc();

// Ambil rating
$ratings = $ratingModel->getByProduct($product_id);
$avg = $ratingModel->getAverage($product_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($product['name']) ?> - SNACK.IDN</title>
  <link rel="stylesheet" href="../assets/css/base.css"/>
  <link rel="stylesheet" href="../assets/css/detail.css"/>
  <script src="detail.js" defer></script>
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
                    <a href="<?= $user ? 'cart.php' : 'login.php' ?>"
                    onclick="<?= $user ? '' : 'alert(\'Silakan login terlebih dahulu untuk melihat keranjang.\');' ?>">🛒</a>
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
        <nav class="breadcrumbs">
            <a href="../index.php" class="breadcrumb-link">Home</a>
            <span>&gt;</span>
            <a href="product.php" class="breadcrumb-link">Produk</a>
            <span>&gt;</span>
            <span class="breadcrumb-current"><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <!-- Product Detail -->
        <section class="product-detail">
            <img src="../<?= htmlspecialchars($product['image_url'] ?? 'assets/img/no-image.png') ?>" 
                alt="<?= htmlspecialchars($product['name']) ?>" 
                class="product-image" />

            <div class="product-info-section">
                <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
                <p class="product-category">Kategori: <?= htmlspecialchars($cat['name']);?></p>
                
                <!-- Nama toko -->
                <p class="product-seller">
                    🏪 
                    <a href="store.php?id=<?= $product['seller_id'] ?>" class="store-link">
                        <?= htmlspecialchars($product['seller_name']) ?>
                    </a>
                </p>

                <!-- Price + Rating sejajar -->
                <div class="product-meta">
                    <p class="product-price">Rp <?= number_format($product['price'],0,',','.') ?></p>
                    <div class="product-rating">
                        ⭐ <?= number_format($avg['avg_rating'],1) ?>/5 
                        <span>(<?= $avg['total_rating'] ?> ulasan)</span>
                    </div>
                </div>

                <p class="product-description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                <div class="product-actions">
                    <form method="POST">
                        <button type="submit" name="add_to_cart" class="add-to-cart-btn">Tambah ke Keranjang</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
  </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
