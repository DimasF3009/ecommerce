<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../vendor/phpqrcode/qrlib.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'pembeli') {
    header("Location: login.php");
    exit;
}

$buyer_id = $_SESSION['user']['id'];
$user = $_SESSION['user'];
$productModel = new Product($conn);

// ===== Fungsi bantu =====
function getCart($conn, $buyer_id) {
    $stmt = $conn->prepare("SELECT * FROM cart WHERE buyer_id=? LIMIT 1");
    $stmt->bind_param("s", $buyer_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getCartItems($conn, $cart_id) {
    $stmt = $conn->prepare("SELECT ci.*, p.name, p.price, p.image_url, p.stock 
                            FROM cart_item ci 
                            JOIN product p ON ci.product_id=p.id 
                            WHERE ci.cart_id=?");
    $stmt->bind_param("s", $cart_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $items = [];
    while($row = $res->fetch_assoc()) $items[] = $row;
    return $items;
}

// ===== Update qty =====
if (isset($_POST['update_qty'])) {
    $product_id = $_POST['product_id'];
    $action = $_POST['update_qty'];

    $cart = getCart($conn, $buyer_id);
    if ($cart) {
        $cart_id = $cart['id'];

        // cek stok produk
        $stockStmt = $conn->prepare("SELECT stock FROM product WHERE id=?");
        $stockStmt->bind_param("s", $product_id);
        $stockStmt->execute();
        $stock = $stockStmt->get_result()->fetch_assoc()['stock'];

        $stmt = $conn->prepare("SELECT id, quantity FROM cart_item WHERE cart_id=? AND product_id=? LIMIT 1");
        $stmt->bind_param("ss", $cart_id, $product_id);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();

        if ($item) {
            $qty = $item['quantity'];
            if ($action === 'plus' && $qty < $stock) $qty++;
            elseif ($action === 'minus' && $qty > 1) $qty--;
            
            $stmt = $conn->prepare("UPDATE cart_item SET quantity=? WHERE id=?");
            $stmt->bind_param("is", $qty, $item['id']);
            $stmt->execute();
        }
    }
    header("Location: cart.php");
    exit;
}

// ===== Hapus item =====
if (isset($_POST['remove_item'])) {
    $product_id = $_POST['product_id'];
    $cart = getCart($conn, $buyer_id);
    if ($cart) {
        $cart_id = $cart['id'];
        $stmt = $conn->prepare("DELETE FROM cart_item WHERE cart_id=? AND product_id=?");
        $stmt->bind_param("ss", $cart_id, $product_id);
        $stmt->execute();
    }
    header("Location: cart.php");
    exit;
}

// ===== Checkout / Konfirmasi pembayaran =====
$payment_done = false;
$cart = getCart($conn, $buyer_id);
$cart_items = [];
$subtotal = 0;
$delivery = 15000;

if ($cart) {
    $cart_id = $cart['id'];
    $cart_items = getCartItems($conn, $cart_id);
    foreach($cart_items as $item) $subtotal += $item['price'] * $item['quantity'];
}

$total = $subtotal + $delivery;

if(isset($_POST['confirm_payment']) && $cart_items) {
    // --- Validasi stok cukup ---
    foreach($cart_items as $item) {
        if ($item['quantity'] > $item['stock']) {
            echo "<script>alert('Stok produk {$item['name']} tidak mencukupi!'); window.location='cart.php';</script>";
            exit;
        }
    }

    // --- 1. Insert ke transaction ---
    $transaction_id = uniqid();
    $stmt = $conn->prepare("INSERT INTO transaction (id, buyer_id, total_price, shipping_cost, status, created_at) 
                            VALUES (?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("ssdd", $transaction_id, $buyer_id, $total, $delivery);
    $stmt->execute();

    // --- 2. Insert ke order_item & update stok ---
    $stmt = $conn->prepare("INSERT INTO order_item (id, transaction_id, product_id, quantity, price_at_purchase) 
                            VALUES (UUID(), ?, ?, ?, ?)");
    foreach($cart_items as $item) {
        $stmt->bind_param("ssid", $transaction_id, $item['product_id'], $item['quantity'], $item['price']);
        $stmt->execute();

        // Kurangi stok produk
        $update = $conn->prepare("UPDATE product SET stock = stock - ? WHERE id = ?");
        $update->bind_param("is", $item['quantity'], $item['product_id']);
        $update->execute();
    }

    // --- 3. Hapus cart_items ---
    $stmt = $conn->prepare("DELETE FROM cart_item WHERE cart_id=?");
    $stmt->bind_param("s", $cart_id);
    $stmt->execute();

    $payment_done = true;
}

// ===== Generate QR Code base64 =====
$checkoutData = "user:$buyer_id;total:$total";
ob_start();
QRcode::png($checkoutData, null, QR_ECLEVEL_L, 4);
$imageString = ob_get_contents();
ob_end_clean();
$qrBase64 = 'data:image/png;base64,' . base64_encode($imageString);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Keranjang - SNACK.IDN</title>
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="../assets/css/cart.css">
</head>
<body>

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
        <a href="cart.php" class="icon-btn">🛒</a>
        <a href="profile.php"><span class="icon-profile">👤 <?= htmlspecialchars($user['name']) ?></span></a>
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </div>
</header>

<main class="main-content container">
  <h1 class="page-title">Keranjang Belanja</h1>
  <div class="cart-layout">

    <!-- Cart Items -->
    <section class="cart-items">
      <?php if ($cart_items): ?>
        <?php foreach ($cart_items as $item): ?>
          <div class="cart-item" data-price="<?= $item['price'] ?>" data-qty="<?= $item['quantity'] ?>">
            <img src="<?= !empty($item['image_url']) ? '../'.$item['image_url'] : '../assets/img/no-image.png' ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
            <div class="item-details">
              <h2 class="item-title"><?= htmlspecialchars($item['name']) ?></h2>
              <p class="item-price">Rp <?= number_format($item['price'],0,',','.') ?></p>
            </div>
            <div class="item-actions">
              <form method="POST" style="display:flex; align-items:center; gap:0.5rem;">
                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                <button type="submit" name="update_qty" value="minus">-</button>
                <span class="qty-value"><?= $item['quantity'] ?></span>
                <button type="submit" name="update_qty" value="plus">+</button>
                <button type="submit" name="remove_item" value="1" style="background:#ff4d4f; color:#fff; border:none; padding:0.25rem 0.5rem; border-radius:4px; cursor:pointer;">❌</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Keranjang kosong</p>
      <?php endif; ?>
    </section>

    <!-- Order Summary -->
    <aside class="order-summary">
      <h2 class="summary-title">Order Summary</h2>
      
      <!-- Alamat Pengiriman -->
      <div class="summary-address">
        <p><strong>Alamat Pengiriman</strong></p>
        <p><?= htmlspecialchars($user['address'] ?? 'Alamat belum diatur') ?></p>
        <a href="profile.php" class="edit-address">Ubah Alamat</a>
      </div>
      <div class="summary-separator"></div>

      <div class="summary-details">
        <div class="summary-line subtotal">
          <p>Subtotal</p>
          <span>Rp <?= number_format($subtotal,0,',','.') ?></span>
        </div>
        <div class="summary-line delivery">
          <p>Delivery Fee</p>
          <span>Rp <?= number_format($delivery,0,',','.') ?></span>
        </div>
        <div class="summary-separator"></div>
        <div class="summary-line total">
          <p>Total</p>
          <span>Rp <?= number_format($total,0,',','.') ?></span>
        </div>
      </div>
      <button class="checkout-btn" id="checkoutBtn">Go to Checkout</button>
    </aside>

  </div>
</main>

<!-- Checkout Modal -->
<!-- Checkout Modal -->
<div id="checkoutModal" class="modal">
  <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Checkout</h2>
        <p>Total: Rp <?= number_format($total,0,',','.') ?></p>
        <img src="<?= $qrBase64 ?>" alt="QR Code" />

        <form method="POST" id="checkoutForm">
            <button type="submit" name="confirm_payment" class="confirm-btn">
              Konfirmasi Pembayaran
            </button>
        </form>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<script>
// Modal Checkout
const modal = document.getElementById('checkoutModal');
const btn = document.getElementById('checkoutBtn');
const span = modal.querySelector('.close');

btn.onclick = () => modal.style.display = 'block';
span.onclick = () => modal.style.display = 'none';
window.onclick = e => { if(e.target==modal) modal.style.display='none'; };

// Efek Pembayaran Berhasil
document.addEventListener('DOMContentLoaded', () => {
    <?php if(!empty($payment_done) && $payment_done): ?>
        // ubah konten modal jadi sukses
        const modalContent = modal.querySelector('.modal-content');
        modalContent.innerHTML = `
            <span class="close">&times;</span>
            <h2>Pembayaran Berhasil ✅</h2>
            <p>Terima kasih telah berbelanja!</p>
        `;
        // kosongkan cart di tampilan
        const cartItems = document.querySelector('.cart-items');
        if(cartItems) cartItems.innerHTML = '<p>Keranjang kosong</p>';
        const subtotalEl = document.querySelector('.summary-line.subtotal span');
        const totalEl = document.querySelector('.summary-line.total span');
        if(subtotalEl) subtotalEl.textContent = 'Rp 0';
        if(totalEl) totalEl.textContent = 'Rp 0';
        // tampilkan modal otomatis
        modal.style.display = 'block';

        // masih bisa ditutup dengan tombol x
        modal.querySelector('.close').onclick = () => modal.style.display = 'none';
    <?php endif; ?>
});
</script>


</body>
</html>
