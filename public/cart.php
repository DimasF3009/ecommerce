<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// --- Auth check ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'pembeli') {
    header("Location: login.php");
    exit;
}

$user       = $_SESSION['user'];
$buyer_id   = $user['id'];
$productModel = new Product($conn);

// --- Helpers ---
function getCart($conn, $buyer_id) {
    $stmt = $conn->prepare("SELECT * FROM cart WHERE buyer_id=? LIMIT 1");
    $stmt->bind_param("s", $buyer_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getCartItems($conn, $cart_id) {
    $stmt = $conn->prepare("
        SELECT ci.*, p.name, p.price, p.image_url, p.stock 
        FROM cart_item ci 
        JOIN product p ON ci.product_id = p.id 
        WHERE ci.cart_id=?
    ");
    $stmt->bind_param("s", $cart_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// --- Ambil data cart ---
$cart       = getCart($conn, $buyer_id);
$cart_items = $cart ? getCartItems($conn, $cart['id']) : [];
$subtotal   = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart_items));
$delivery   = 15000;
$total      = $subtotal + $delivery;
$has_address = !empty($user['address']);

// --- Handle POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cart) {
    $cart_id = $cart['id'];

    // Update qty
    if (isset($_POST['update_qty'], $_POST['product_id'])) {
        $product_id = $_POST['product_id'];
        $action     = $_POST['update_qty'];

        $stock = $conn->query("SELECT stock FROM product WHERE id='$product_id'")->fetch_assoc()['stock'] ?? 0;
        $item  = $conn->query("SELECT id, quantity FROM cart_item WHERE cart_id='$cart_id' AND product_id='$product_id' LIMIT 1")->fetch_assoc();

        if ($item) {
            $qty = $item['quantity'];
            $qty = $action === 'plus' ? min($qty + 1, $stock) : max($qty - 1, 1);
            $stmt = $conn->prepare("UPDATE cart_item SET quantity=? WHERE id=?");
            $stmt->bind_param("is", $qty, $item['id']);
            $stmt->execute();
        }
    }

    // Hapus item
    if (isset($_POST['remove_item'], $_POST['product_id'])) {
        $stmt = $conn->prepare("DELETE FROM cart_item WHERE cart_id=? AND product_id=?");
        $stmt->bind_param("ss", $cart_id, $_POST['product_id']);
        $stmt->execute();
    }

    // Checkout
    if (isset($_POST['confirm_payment'])) {
        $cart_items = getCartItems($conn, $cart_id);

        if (empty($cart_items)) {
            echo "<script>alert('Keranjang kosong!'); window.location='cart.php';</script>";
            exit;
        }
        if (empty($user['address'])) {
            echo "<script>alert('Isi alamat dulu di profile!'); window.location='profile.php';</script>";
            exit;
        }

        // Validasi stok
        foreach ($cart_items as $item) {
            if ($item['quantity'] > $item['stock']) {
                echo "<script>alert('Stok {$item['name']} tidak cukup!'); window.location='cart.php';</script>";
                exit;
            }
        }

        // Insert transaction
        $transaction_id = uniqid();
        $stmt = $conn->prepare("
            INSERT INTO transaction (id, buyer_id, total_price, shipping_cost, status, created_at) 
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->bind_param("ssdd", $transaction_id, $buyer_id, $total, $delivery);
        $stmt->execute();

        // Insert order_item & update stok
        $stmt = $conn->prepare("
            INSERT INTO order_item (id, transaction_id, product_id, quantity, price_at_purchase) 
            VALUES (UUID(), ?, ?, ?, ?)
        ");
        foreach ($cart_items as $item) {
            $stmt->bind_param("ssid", $transaction_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();

            $update = $conn->prepare("UPDATE product SET stock = stock - ? WHERE id=?");
            $update->bind_param("is", $item['quantity'], $item['product_id']);
            $update->execute();
        }

        // Kosongkan cart
        $stmt = $conn->prepare("DELETE FROM cart_item WHERE cart_id=?");
        $stmt->bind_param("s", $cart_id);
        $stmt->execute();

        $payment_done = true;
    }

    header("Location: cart.php");
    exit;
}

// ===== Generate QR Code =====
$checkoutData = "user:$buyer_id;total:$total";

$options = new QROptions([
    'outputType' => QRCode::OUTPUT_IMAGE_PNG, // harus PNG
    'eccLevel'   => QRCode::ECC_L,
    'scale'      => 4,
]);

$qrcode = new QRCode($options);

// ini return binary PNG
$pngData = $qrcode->render($checkoutData);

// encode ke base64 sekali saja
$qrBase64 = 'data:image/png;base64,' . base64_encode($pngData);

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
          <div class="cart-item">
            <img src="<?= !empty($item['image_url']) ? '../'.$item['image_url'] : '../assets/img/no-image.png' ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
            <div class="item-details">
              <h2 class="item-title"><?= htmlspecialchars($item['name']) ?></h2>
              <p class="item-price">Rp <?= number_format($item['price'],0,',','.') ?></p>
            </div>
            <div class="item-actions">
              <form method="POST" class="qty-form">
                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                <button type="submit" name="update_qty" value="minus">-</button>
                <span class="qty-value"><?= $item['quantity'] ?></span>
                <button type="submit" name="update_qty" value="plus">+</button>
                <button type="submit" name="remove_item" value="1" class="remove-btn">❌</button>
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
      <button 
        class="checkout-btn" 
        id="checkoutBtn" 
        <?= empty($cart_items) ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' ?>>
        Go to Checkout
      </button>
    </aside>

  </div>
</main>

<!-- Checkout Modal -->
<div id="checkoutModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>

    <?php if(!empty($payment_done) && $payment_done): ?>
        <h2>Pembayaran Berhasil ✅</h2>
        <p>Terima kasih telah berbelanja!</p>
    <?php else: ?>
        <h2>Checkout</h2>
        <p>Total: Rp <?= number_format($total,0,',','.') ?></p>
        <div id="qrContainer">
          <img src="<?= $pngData ?>" alt="QR Code" style="width:200px; height:200px;">

        </div>
        <form method="POST" id="checkoutForm">
            <button type="submit" name="confirm_payment" class="confirm-btn">
              Konfirmasi Pembayaran
            </button>
        </form>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<script>
  const modal = document.getElementById('checkoutModal');
  const btn   = document.getElementById('checkoutBtn');
  const close = modal.querySelector('.close');
  const hasAddress = <?= $has_address ? 'true':'false' ?>;

  if(btn){
    btn.addEventListener('click', e => {
      if(!hasAddress){
        alert('Silakan isi alamat dulu di profile!');
        window.location='profile.php';
        return;
      }
      modal.style.display = 'block';
    });
  }

  close.onclick = () => modal.style.display = 'none';
  window.onclick = e => { if(e.target==modal) modal.style.display='none'; };
</script>

</body>
</html>
