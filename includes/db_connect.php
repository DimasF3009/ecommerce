<?php
// Konfigurasi koneksi database untuk hosting InfinityFree

define('DB_SERVER', 'localhost');          // Host dari InfinityFree
define('DB_USERNAME', 'root');                   // Username database
define('DB_PASSWORD', '');      // GANTI dengan password database kamu
define('DB_NAME', 'ecommerce_db');          // GANTI dengan nama database kamu sesuai MySQL Database

// Membuat koneksi ke database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Mengecek koneksi
if (!$conn) {
    die("ERROR: Tidak bisa terhubung ke database. " . mysqli_connect_error());
}

// Jika butuh debug (boleh dihapus nanti)
// echo "Koneksi berhasil!";
?>