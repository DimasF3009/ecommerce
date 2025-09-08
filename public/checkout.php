<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pembeli') {
    header("Location: login.php");
    exit;
}

$buyer_id = $_SESSION['user']['id'];

// ambil cart
$cart = $conn->query("SELECT * FROM cart WHERE buyer_id='$buyer_id'")->fetch_assoc();
if (!$cart) {
    echo "Keranjang kosong!";
    exit;
}

$cart_id = $cart['id'];
$cart_items = $conn->query("SELECT ci.*, p.price FROM cart_item ci JOIN product p ON ci.product_id=p.id WHERE ci.cart_id='$cart_id'");

$total_price = 0;
while ($row = $cart_items->fetch_assoc()) {
    $total_price += $row['price'] * $row['quantity'];
}

// simpan transaksi
$conn->query("INSERT INTO transaction (id, buyer_id, total_price, status, created_at) VALUES (UUID(), '$buyer_id', $total_price, 'pending', NOW())");
$transaction_id = $conn->insert_id;

// simpan order_item
$cart_items = $conn->query("SELECT ci.*, p.price FROM cart_item ci JOIN product p ON ci.product_id=p.id WHERE ci.cart_id='$cart_id'");
while ($row = $cart_items->fetch_assoc()) {
    $price_at_purchase = $row['price'];
    $conn->query("INSERT INTO order_item (id, transaction_id, product_id, quantity, price_at_purchase) VALUES (UUID(), '$transaction_id', '".$row['product_id']."', ".$row['quantity'].", $price_at_purchase)");
    
    // update stok produk
    $conn->query("UPDATE product SET stock = stock - ".$row['quantity']." WHERE id='".$row['product_id']."'");
}

// hapus cart & cart_item
$conn->query("DELETE FROM cart_item WHERE cart_id='$cart_id'");
$conn->query("DELETE FROM cart WHERE id='$cart_id'");

echo "Checkout berhasil! Total bayar: Rp " . number_format($total_price,0,',','.');
echo "<br><a href='index.php'>Kembali ke Home</a>";
