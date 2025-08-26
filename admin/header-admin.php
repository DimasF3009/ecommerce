<?php
// Pastikan session_start() sudah dipanggil di config.php
require_once '../includes/config.php'; // Jika belum di config.php
require_once '../includes/auth.php'; // Sertakan file auth.php
?>
<header>
    <nav>
        <div class="logo">
            <a href="#">Toko Sepatu</a>
        </div>
        <ul class="nav-links">
            <?php if (isLoggedIn()): ?>
                <li><a href="my_account-admin.php">Akun Saya (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
                </li>
                <?php if (isAdmin()): ?>
                    <li><a href="index.php">Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="../pages/logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>