<?php
session_start();
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Rating.php';
require_once __DIR__ . '/../config/database.php';

$user = $_SESSION['user'] ?? null;
$productModel = new Product($conn);
$ratingModel  = new Rating($conn);
// --- Ambil kategori ---
$categories = [];
$catResult = $conn->query("SELECT * FROM category ORDER BY name ASC");
if ($catResult) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// --- Filter produk ---
$where = [];
$order = "";

// --- Search produk ---
$searchQuery = $_GET['search'] ?? '';

// --- Query produk dengan search ---
$sql = "SELECT p.*, c.name AS category_name, u.name AS seller_name
FROM product p
LEFT JOIN category c ON p.category_id = c.id
LEFT JOIN user u ON p.seller_id = u.id";


$filters = [];
if (!empty($_GET['category'])) {
    $cats = array_map('intval', $_GET['category']);
    if ($cats) $filters[] = "p.category_id IN (" . implode(",", $cats) . ")";
}
if ($searchQuery) {
    $filters[] = "p.name LIKE '%" . $conn->real_escape_string($searchQuery) . "%'";
}

if ($filters) {
    $sql .= " WHERE " . implode(" AND ", $filters);
}

if (!empty($_GET['harga'])) {
    if ($_GET['harga'] === 'asc') $sql .= " ORDER BY p.price ASC";
    elseif ($_GET['harga'] === 'desc') $sql .= " ORDER BY p.price DESC";
}

$products = $conn->query($sql);
if (!$products) die("Query error: " . $conn->error);

// --- Tambah ke keranjang ---
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'] ?? null;

    if (!$product_id) {
        echo "<script>alert('Produk tidak valid');</script>";
    } elseif (!$user || $user['role'] !== 'pembeli') {
        echo "<script>alert('Login dulu untuk menambahkan ke keranjang!');</script>";
    } else {
        $buyer_id = $user['id'];
        $cart = $conn->query("SELECT id FROM cart WHERE buyer_id='$buyer_id' LIMIT 1")->fetch_assoc();

        if (!$cart) {
            $conn->query("INSERT INTO cart (id, buyer_id, created_at) VALUES (UUID(), '$buyer_id', NOW())");
            $cart = $conn->query("SELECT id FROM cart WHERE buyer_id='$buyer_id' LIMIT 1")->fetch_assoc();
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

// --- Search produk ---
$searchQuery = $_GET['search'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SNACK.IDN - Produk</title>
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="../assets/css/product.css">
</head>
<body>
<header class="header">
    <div class="container header__content">
        <a href="<?= $user ? 'homepage.php' : '../index.php' ?>" class="logo">SNACK.IDN</a>
        <nav class="nav nav--bottom">
            <a href="<?= $user ? 'homepage.php' : '../index.php' ?>" class="nav__link">Beranda</a>
            <a href="product.php" class="nav__link">Produk</a>
            <a href="riwayat.php" class="nav__link">Riwayat</a>
        </nav>
        <div class="header__top">

            <?php include __DIR__ . '/../partials/search.php'; ?>

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

<main class="container main-content">
    <!-- Sidebar filter -->
    <aside class="sidebar">
        <h3 class="sidebar__title">Filter</h3>
        <form method="get" action="product.php">
            <div class="filter-group">
                <p class="filter-heading">Kategori</p>
                <label>
                    <input type="checkbox" name="all_category" value="1" <?= empty($_GET['category']) ? 'checked' : '' ?>> Semua Produk
                </label>
                <?php foreach ($categories as $cat): ?>
                    <label>
                        <input type="checkbox" name="category[]" value="<?= $cat['id'] ?>"
                               <?= isset($_GET['category']) && in_array($cat['id'], $_GET['category']) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="filter-group">
                <p class="filter-heading">Harga</p>
                <label>
                    <input type="radio" name="harga" value="" <?= empty($_GET['harga']) ? 'checked' : '' ?>> Semua
                </label>
                <label>
                    <input type="radio" name="harga" value="asc" <?= (isset($_GET['harga']) && $_GET['harga']==='asc') ? 'checked' : '' ?>> Termurah
                </label>
                <label>
                    <input type="radio" name="harga" value="desc" <?= (isset($_GET['harga']) && $_GET['harga']==='desc') ? 'checked' : '' ?>> Termahal
                </label>
            </div>

            <button type="submit" class="terapkan">Terapkan</button>
        </form>
    </aside>

    <!-- Produk grid -->
    <section class="products">
        <?php if ($products->num_rows > 0): ?>
            <?php while($row = $products->fetch_assoc()): ?>
                <?php $avg = $ratingModel->getAverage($row['id']); ?>
                <article class="product-card">
                    <a href="product_detail.php?id=<?= $row['id'] ?>">
                        <img src="<?= !empty($row['image_url']) ? '../'.$row['image_url'] : '../assets/img/no-image.png' ?>"
                            alt="<?= htmlspecialchars($row['name']) ?>" class="product-image">
                        <h3 class="product-name"><?= htmlspecialchars($row['name']) ?></h3>
                        <p class="product-price">Rp <?= number_format($row['price'],0,',','.') ?></p>
                        <p class="product-category"><?= htmlspecialchars($row['category_name'] ?? '-') ?></p>
                        <p class="product-seller">🏪 <?= htmlspecialchars($row['seller_name'] ?? 'Tidak diketahui') ?></p>
                    </a>
                    <p class="product-rating">
                        ⭐ <?= $avg['avg_rating'] ? number_format($avg['avg_rating'],1) : '0' ?>/5
                        (<?= $avg['total_rating'] ?>)
                    </p>

                    <?php if ($user): ?>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="add_to_cart" class="add-to-cart">Tambah ke Keranjang</button>
                        </form>
                    <?php else: ?>
                        <button class="add-to-cart" onclick="alert('Silakan login terlebih dahulu untuk menambah ke keranjang.');">
                            Tambah ke Keranjang
                        </button>
                    <?php endif; ?>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-products">
                <p>😢 Produk tidak ditemukan</p>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
