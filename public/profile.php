<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// --- Update Profile ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $address = trim($_POST['address']);

    $stmt = $conn->prepare("UPDATE user SET name=?, email=?, address=? WHERE id=?");
    $stmt->bind_param("ssss", $name, $email, $address, $user_id);

    if ($stmt->execute()) {
        $_SESSION['user']['name']    = $name;
        $_SESSION['user']['email']   = $email;
        $_SESSION['user']['address'] = $address;

        $success = "Profil berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui profil!";
    }
}

// Ambil data terbaru
$stmt = $conn->prepare("SELECT name, email, address FROM user WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Saya - SNACK.IDN</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>

  <!-- Header -->
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
          <a href="cart.php">🛒</a>
          <a href="profile.php"><span class="icon-profile">👤 <?= htmlspecialchars($user['name']) ?></span></a>
          <a href="logout.php">Logout</a>
        </div>
      </div>
    </div>
  </header>

  <!-- Main -->
  <main class="main-content">
    <div class="container">
      <h1>Profil Saya</h1>

      <?php if (!empty($success)): ?>
        <p class="success"><?= $success ?></p>
      <?php elseif (!empty($error)): ?>
        <p class="error"><?= $error ?></p>
      <?php endif; ?>

      <form method="POST" class="profile-form">
        <label for="name">Nama Lengkap</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($profile['name']) ?>" required>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($profile['email']) ?>" required>

        <label for="address">Alamat</label>
        <textarea name="address" id="address" rows="4"><?= htmlspecialchars($profile['address'] ?? '') ?></textarea>

        <button type="submit" class="btn">Simpan Perubahan</button>
      </form>
    </div>
  </main>

  <!-- Footer -->
  <?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
