<?php
// search.php
// --- Global function untuk search produk ---
require_once __DIR__ . '/../config/database.php';
function searchProducts($conn, $search = '') {
    $sql = "SELECT p.*, c.name AS category_name
            FROM product p
            LEFT JOIN category c ON p.category_id = c.id";
    
    if ($search) {
        $searchEscaped = $conn->real_escape_string($search);
        $sql .= " WHERE p.name LIKE '%$searchEscaped%'";
    }
    elseif (!$search) {
        // Jika tidak ada pencarian, kembalikan semua produk
        echo "<script>console.log('No search query provided, returning all products.');</script>";
    }

    return $conn->query($sql);
}

// --- Partial search bar ---
$searchQuery = $_GET['search'] ?? '';
?>
<form class="search-bar" method="get" action="public/product.php">
    <input type="text" name="search" placeholder="Cari jajanan favoritmu..."
           value="<?= htmlspecialchars($searchQuery) ?>">
    <button type="submit">🔍</button>
</form>
