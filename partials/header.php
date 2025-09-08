<header class="header">
    <div class="container header__content">
      <a href="homepage.html" class="logo">SNACK.IDN</a>
      <!-- Nav pindah ke bawah -->
      <nav class="nav nav--bottom">
        <a href="homepage.html" class="nav__link">Beranda</a>
        <a href="product.html" class="nav__link">Produk</a>
      </nav>
        <div class="header__top">
          <!-- Search lebih besar -->
          <form class="search-bar">
            <input type="text" placeholder="Cari jajanan favoritmu..." />
            <button type="submit">🔍</button>
          </form>

          <div class="header__actions">
            <?php if ($user): ?>
              <a href="public/cart.php">🛒</a>
            <?php else: ?>
              <a href="public/login.php" onclick="return confirm('Silakan login terlebih dahulu untuk melihat keranjang.')">🛒</a>
            <?php endif; ?>
            <a href="public/login.php" class="icon-btn">👤</a>
          </div>
        </div>
    </div>
</header>