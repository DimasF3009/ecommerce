<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// jangan ada spasi/echo sebelum header()
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $res = $conn->query("SELECT * FROM user WHERE email='$email' AND role='admin' LIMIT 1");
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");  // redirect ke dashboard
        exit;
    } else {
        $error = "Email / password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - Admin</title>
  <link rel="stylesheet" href="../../assets/css/login.css">
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      
      <!-- Bagian kiri: gambar -->
      <div class="login-left">
        <img src="../../assets/pict/logo.png" alt="Login Illustration">
      </div>

      <!-- Bagian kanan: form -->
      <div class="login-right">
        <h2>Login Admin <span>SNACK.IDN</span></h2>
        <form method="POST">
          <input type="email" name="email" placeholder="Email" required>
          <input type="password" name="password" placeholder="Password" required>
          <button type="submit">Login</button>
        </form>
        <?php if (!empty($message)): ?>
          <p class="error"><?= $message ?></p>
        <?php endif; ?>
        <p class="register-link">Belum punya akun? <a href="register.php">Daftar</a></p>
        <p class="register-link">Login sebagai pembeli <a href="../login.php">Masuk</a></p>
      </div>

    </div>
  </div>
</body>
</html>
