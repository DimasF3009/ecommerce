<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'penjual') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$seller_id = $_SESSION['user']['id'];

// Update status pesanan
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $check = $conn->query("SELECT * FROM order_item oi 
                           JOIN product p ON oi.product_id=p.id 
                           WHERE oi.transaction_id='$order_id' AND p.seller_id='$seller_id'");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE transaction SET status='$status' WHERE id='$order_id'");
    }
}

// Ambil transaksi
$transactions = $conn->query("
    SELECT t.*, u.name AS buyer_name 
    FROM transaction t
    JOIN user u ON t.buyer_id = u.id
    WHERE t.id IN (
        SELECT DISTINCT oi.transaction_id 
        FROM order_item oi
        JOIN product p ON oi.product_id = p.id
        WHERE p.seller_id='$seller_id'
    )
    ORDER BY t.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pesanan - SNACK.IDN</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/orders.css">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Pesanan Masuk</h2>

        <div class="card">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Pembeli</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($transactions->num_rows > 0): ?>
                    <?php while($t = $transactions->fetch_assoc()): ?>
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td><?= htmlspecialchars($t['buyer_name']) ?></td>
                            <td>Rp <?= number_format($t['total_price'],0,',','.') ?></td>
                            <td><span class="status <?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
                            <td>
                                <form method="POST" class="form-inline">
                                    <input type="hidden" name="order_id" value="<?= $t['id'] ?>">
                                    <select name="status">
                                        <option value="pending" <?= $t['status']=='pending'?'selected':'' ?>>Pending</option>
                                        <option value="dibayar" <?= $t['status']=='dibayar'?'selected':'' ?>>Dibayar</option>
                                        <option value="dikirim" <?= $t['status']=='dikirim'?'selected':'' ?>>Dikirim</option>
                                        <option value="selesai" <?= $t['status']=='selesai'?'selected':'' ?>>Selesai</option>
                                        <option value="dibatalkan" <?= $t['status']=='dibatalkan'?'selected':'' ?>>Dibatalkan</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">Belum ada pesanan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
