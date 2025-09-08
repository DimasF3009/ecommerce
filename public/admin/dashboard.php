<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// --- Ambil total pengguna ---
$stmt = $conn->prepare("SELECT COUNT(*) AS total_users FROM user where role != 'admin'");
$stmt->execute();
$totalUsers = $stmt->get_result()->fetch_assoc()['total_users'];
$stmt->close();

// --- Ambil total transaksi ---
$stmt = $conn->prepare("SELECT COUNT(*) AS total_trx FROM transaction");
$stmt->execute();
$totalTrx = $stmt->get_result()->fetch_assoc()['total_trx'];
$stmt->close();

// --- Ambil total kategori produk ---
$stmt = $conn->prepare("SELECT COUNT(*) AS total_categories FROM category");
$stmt->execute();
$totalCategories = $stmt->get_result()->fetch_assoc()['total_categories'];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - SNACK.IDN</title>
  <link rel="stylesheet" href="assets/css/base.css">
</head>
<body>
  <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main -->
        <main class="main-content">
            <header class="main-header">
                <h1>Admin Panel</h1>
            </header>

            <section class="cards">
                <div class="card">
                    <h3>Total Pengguna</h3>
                    <p><?= htmlspecialchars($totalUsers) ?></p>
                </div>
                <div class="card">
                    <h3>Total Transaksi</h3>
                    <p><?= htmlspecialchars($totalTrx) ?></p>
                </div>
                <div class="card">
                    <h3>Kategori Produk</h3>
                    <p><?= htmlspecialchars($totalCategories) ?></p>
                </div>
            </section>
        </main>
  </div>
</body>
</html>
