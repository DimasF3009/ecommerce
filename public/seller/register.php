<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';

$userModel = new User($conn);
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    $role     = 'penjual'; // otomatis admin

    $success = $userModel->register($name, $email, $password, $role, $phone, $address);

    if ($success) {
        $_SESSION['flash_success'] = "Registrasi Penjual berhasil! Silahkan login.";
        header("Location: login.php");
        exit;
    } else {
        $message = "Registrasi gagal, email sudah digunakan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Penjual - SNACK.IDN</title>
    <link rel="stylesheet" href="../../assets/css/register.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-image">
            <img src="../../assets/pict/logo.png" alt="Daftar Penjual SNACK.IDN">
        </div>
        <div class="auth-form">
            <h2>Buat Akun Penjual</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Nama Lengkap" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="phone" placeholder="No. Telepon" required>
                <textarea name="address" placeholder="Alamat" required></textarea>

                <button type="submit">Daftar</button>
            </form>

            <?php if ($message): ?>
                <p class="message"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <p class ="register-link">Sudah punya akun? <a href="login.php">Masuk</a></p>
            <p class ="register-link">Login akun pembeli? <a href="../login.php">Masuk</a></p>
        </div>
    </div>
</body>
</html>
