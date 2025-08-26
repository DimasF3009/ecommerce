<?php
// products.php (Admin Product Management)

require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

redirectIfNotAdmin('../pages/login.php');

$message = '';
$product_to_edit = null;

// ==================================================
// Utility Functions
// ==================================================
function fetch_categories($conn)
{
    $categories = [];
    $result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

function handle_image_upload($file, $current_image = null)
{
    $target_dir = "../public/uploads/";
    $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return $current_image; // keep old image
    }

    $image_file_name = uniqid() . '_' . basename($file["name"]);
    $target_file = $target_dir . $image_file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (!in_array($imageFileType, $allowed_types)) {
        throw new Exception("Format file tidak valid. Hanya JPG, JPEG, PNG, GIF yang diizinkan.");
    }
    if ($file["size"] > 5000000) {
        throw new Exception("Ukuran file terlalu besar. Maks 5MB.");
    }
    if (!move_uploaded_file($file["tmp_name"], $target_file)) {
        throw new Exception("Gagal mengunggah gambar.");
    }

    // Hapus gambar lama jika ada
    if ($current_image && file_exists($target_dir . $current_image)) {
        unlink($target_dir . $current_image);
    }

    return $image_file_name;
}

function delete_product($conn, $id)
{
    // Ambil gambar lama
    $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old_image = $result->fetch_assoc()['image_url'] ?? null;
    $stmt->close();

    // Delete row
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();

    if ($success && $old_image && file_exists('../public/uploads/' . $old_image)) {
        unlink('../public/uploads/' . $old_image);
    }

    return $success;
}

// ==================================================
// Handle Actions
// ==================================================
if ($_GET['action'] ?? null) {
    $id = filter_var($_GET['id'] ?? null, FILTER_SANITIZE_NUMBER_INT);

    if ($_GET['action'] === 'delete' && $id) {
        if (delete_product($conn, $id)) {
            $message = '<p class="success">Produk berhasil dihapus!</p>';
        } else {
            $message = '<p class="error">Gagal menghapus produk.</p>';
        }
    }

    if ($_GET['action'] === 'edit' && $id) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $product_to_edit = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// --- Handle ADD/UPDATE ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_product'])) {
    try {
        $product_id    = filter_var($_POST['product_id'] ?? null, FILTER_SANITIZE_NUMBER_INT);
        $name          = sanitize_input($_POST['name']);
        $description   = sanitize_input($_POST['description']);
        $price         = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $stock         = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
        $category_id   = filter_var($_POST['category_id'], FILTER_SANITIZE_NUMBER_INT);
        $brand         = sanitize_input($_POST['brand']);
        $size_available= sanitize_input($_POST['size_available']);
        $image_url     = $_POST['current_image_url'] ?? null;

        if (!$name || $price === false || $stock === false || !$category_id || !$brand) {
            throw new Exception("Nama, harga, stok, kategori, dan brand wajib diisi!");
        }

        // Handle upload
        if (isset($_FILES['image'])) {
            $image_url = handle_image_upload($_FILES['image'], $image_url);
        }

        if ($product_id) {
            // UPDATE
            $stmt = $conn->prepare("UPDATE products 
                SET name=?, description=?, price=?, stock=?, image_url=?, category_id=?, brand=?, size_available=?, updated_at=NOW()
                WHERE id=?");
            $stmt->bind_param("ssdissssi", 
                $name, $description, $price, $stock, $image_url, $category_id, $brand, $size_available, $product_id
            );
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO products 
                (name, description, price, stock, image_url, category_id, brand, size_available) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdissss", 
                $name, $description, $price, $stock, $image_url, $category_id, $brand, $size_available
            );
        }

        if ($stmt->execute()) {
            $message = '<p class="success">Produk berhasil ' . ($product_id ? 'diperbarui' : 'ditambahkan') . '!</p>';
            if (!$product_id) $_POST = []; // reset form
        } else {
            throw new Exception("DB Error: " . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $message = '<p class="error">' . $e->getMessage() . '</p>';
    }
}

// ==================================================
// Fetch Data for View
// ==================================================
$categories_for_dropdown = fetch_categories($conn);
$products = [];
$result = $conn->query("SELECT p.*, c.name AS category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.name ASC");
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/global.css">
    <link rel="stylesheet" href="../public/css/admin.css">
</head>

<body>
    <?php include 'header-admin.php'; ?>

    <main class="admin-main">
        <div class="admin-sidebar">
            <h3>Admin Menu</h3>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="products.php">Kelola Produk</a></li>
                <li><a href="categories.php">Kelola Kategori</a></li>
                <li><a href="orders.php">Kelola Pesanan</a></li>
                <li><a href="users.php">Kelola Pengguna</a></li>
                <li><a href="../pages/logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="admin-content">
            <h2>Kelola Produk</h2>
            <?php echo $message; // Menampilkan pesan sukses/error ?>

            <h3><?php echo ($product_to_edit ? 'Edit Produk' : 'Tambah Produk Baru'); ?></h3>
            <form action="products.php" method="POST" enctype="multipart/form-data">
                <?php if ($product_to_edit): ?>
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_to_edit['id']); ?>">
                    <input type="hidden" name="current_image_url"
                        value="<?php echo htmlspecialchars($product_to_edit['image_url']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Nama Produk:</label>
                    <input type="text" id="name" name="name"
                        value="<?php echo htmlspecialchars($product_to_edit['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi Produk:</label>
                    <textarea id="description"
                        name="description"><?php echo htmlspecialchars($product_to_edit['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Harga:</label>
                    <input type="number" step="0.01" id="price" name="price"
                        value="<?php echo htmlspecialchars($product_to_edit['price'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stok:</label>
                    <input type="number" id="stock" name="stock"
                        value="<?php echo htmlspecialchars($product_to_edit['stock'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="category_id">Kategori:</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories_for_dropdown as $cat_opt): ?>
                            <option value="<?php echo htmlspecialchars($cat_opt['id']); ?>" <?php echo ($product_to_edit && $product_to_edit['category_id'] == $cat_opt['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat_opt['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="brand">Brand:</label>
                    <input type="text" id="brand" name="brand"
                        value="<?php echo htmlspecialchars($product_to_edit['brand'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="size_available">Ukuran Tersedia (Pisahkan dengan koma, misal: 38,39,40):</label>
                    <input type="text" id="size_available" name="size_available"
                        value="<?php echo htmlspecialchars($product_to_edit['size_available'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="image">Gambar Produk:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if ($product_to_edit && $product_to_edit['image_url']): ?>
                        <p>Gambar saat ini: <img
                                src="../public/uploads/<?php echo htmlspecialchars($product_to_edit['image_url']); ?>"
                                alt="Gambar Produk" style="max-width: 100px; vertical-align: middle;"></p>
                    <?php endif; ?>
                </div>
                <button type="submit"
                    name="submit_product"><?php echo ($product_to_edit ? 'Update Produk' : 'Tambah Produk'); ?></button>
                <?php if ($product_to_edit): ?>
                    <a href="products.php" class="button button-secondary">Batal Edit</a>
                <?php endif; ?>
            </form>

            <hr>

            <h3>Daftar Produk</h3>
            <?php if (empty($products)): ?>
                <p>Belum ada produk yang ditambahkan.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Brand</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Ukuran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod['id']); ?></td>
                                <td>
                                    <?php if ($prod['image_url']): ?>
                                        <img src="../public/uploads/<?php echo htmlspecialchars($prod['image_url']); ?>"
                                            alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                            style="max-width: 80px; height: auto;">
                                    <?php else: ?>
                                        <img src="../public/images/placeholder.jpg" alt="No Image"
                                            style="max-width: 100px; height: auto;">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($prod['name']); ?></td>
                                <td><?php echo htmlspecialchars($prod['brand']); ?></td>
                                <td><?php echo htmlspecialchars($prod['category_name'] ?? 'N/A'); ?></td>
                                <td>Rp <?php echo number_format($prod['price'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($prod['stock']); ?></td>
                                <td><?php echo htmlspecialchars($prod['size_available'] ?: 'N/A'); ?></td>
                                <td>
                                    <a href="products.php?action=edit&id=<?php echo htmlspecialchars($prod['id']); ?>"
                                        class="button button-small">Edit</a>
                                    <a href="products.php?action=delete&id=<?php echo htmlspecialchars($prod['id']); ?>"
                                        class="button button-small button-danger"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="../public/js/script.js"></script>
</body>

</html>