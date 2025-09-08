<?php
session_start();
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../config/database.php';

$userModel = new User($conn);
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $role     = 'pembeli'; // default role

    $success = $userModel->register($name, $email, $password, $role);

    if ($success) {
        header("Location: login.php");
        exit;
    } else {
        $message = "Registrasi gagal, email mungkin sudah digunakan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SNACK.IDN</title>
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-image">
            <img src="../assets/pict/logo.png" alt="Daftar SNACK.IDN">
        </div>
        <div class="auth-form">
            <h2>Buat Akun</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Nama Lengkap" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Daftar</button>
            </form>
            <p class="message"><?= $message ?></p>
            <p>Sudah punya akun? <a href="login.php">Login</a></p>
        </div>
    </div>
</body>
</html>
