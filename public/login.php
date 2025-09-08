<?php
session_start();
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../config/database.php';

$userModel = new User($conn);
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $user = $userModel->login($email, $password);
    if ($user) {
        $_SESSION['user'] = $user;

        // redirect sesuai role
        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } elseif ($user['role'] == 'penjual') {
            header("Location: seller/dashboard.php");
        } else {
            header("Location: homepage.php");
        }
        exit;
    } else {
        $message = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - SNACK.IDN</title>
  <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      
      <!-- Bagian kiri: gambar -->
      <div class="login-left">
        <img src="../assets/pict/logo.png" alt="Login Illustration">
      </div>

      <!-- Bagian kanan: form -->
      <div class="login-right">
        <h2>Login ke <span>SNACK.IDN</span></h2>
        <form method="POST">
          <input type="email" name="email" placeholder="Email" required>
          <input type="password" name="password" placeholder="Password" required>
          <button type="submit">Login</button>
        </form>
        <?php if (!empty($message)): ?>
          <p class="error"><?= $message ?></p>
        <?php endif; ?>
        <p class="register-link">Belum punya akun? <a href="register.php">Daftar</a></p>
        <p class="register-link">Login sebagai penjual? <a href="seller/login.php">Masuk</a></p>

      </div>

    </div>
  </div>
</body>
</html>
