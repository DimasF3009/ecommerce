<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'penjual') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$seller_id = $_SESSION['user']['id'];

// ===== Fungsi Upload Gambar =====
function uploadImage($fileKey) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== 0) {
        return '';
    }
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return '';
    $filename = uniqid() . '.' . $ext;
    $target   = __DIR__ . '/../../assets/img/' . $filename;
    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $target)) {
        return 'assets/img/' . $filename;
    }
    return '';
}

// ===== Tambah Produk =====
if (isset($_POST['add_product'])) {
    $name     = trim($_POST['name']);
    $desc     = trim($_POST['description']);
    $price    = max(0, (int)$_POST['price']);
    $stock    = max(0, (int)$_POST['stock']);
    $category_id = trim($_POST['category']);
    $image_url = uploadImage('image_file');

    $stmt = $conn->prepare("INSERT INTO product (id, seller_id, name, description, price, stock, category_id, image_url, created_at)
                            VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssiiis", $seller_id, $name, $desc, $price, $stock, $category_id, $image_url);
    $stmt->execute();
    $stmt->close();
}

// ===== Update Produk =====
if (isset($_POST['update_product'])) {
    $id         = $_POST['product_id'];
    $name       = trim($_POST['name']);
    $desc       = trim($_POST['description']);
    $price      = max(0, (int)$_POST['price']);
    $stock      = max(0, (int)$_POST['stock']);
    $category_id = trim($_POST['category']);
    $image_url  = uploadImage('image_file');

    if ($image_url) {
        $stmt = $conn->prepare("UPDATE product 
                                SET name=?, description=?, price=?, stock=?, category_id=?, image_url=? 
                                WHERE id=? AND seller_id=?");
        $stmt->bind_param("ssiiisss", $name, $desc, $price, $stock, $category_id, $image_url, $id, $seller_id);
    } else {
        $stmt = $conn->prepare("UPDATE product 
                                SET name=?, description=?, price=?, stock=?, category_id=? 
                                WHERE id=? AND seller_id=?");
        $stmt->bind_param("ssiisss", $name, $desc, $price, $stock, $category_id, $id, $seller_id);
    }
    $stmt->execute();
    $stmt->close();
}

// ===== Hapus Produk =====
if (isset($_POST['delete_product'])) {
    $id = $_POST['delete_product'];
    $stmt = $conn->prepare("DELETE FROM product WHERE id=? AND seller_id=?");
    $stmt->bind_param("ss", $id, $seller_id);
    $stmt->execute();
    $stmt->close();
}

// ===== Ambil Produk Penjual =====
$stmt = $conn->prepare("
    SELECT p.*, c.name AS category_name
    FROM product p
    LEFT JOIN category c ON p.category_id = c.id
    WHERE p.seller_id = ?
    ORDER BY p.created_at DESC
");
$stmt->bind_param("s", $seller_id);
$stmt->execute();
$products = $stmt->get_result();

// ===== Ambil semua kategori untuk dropdown =====
$catStmt = $conn->query("SELECT id, name FROM category ORDER BY name ASC");
$categories = [];
while ($row = $catStmt->fetch_assoc()) {
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Produk - SNACK.IDN</title>
  <link rel="stylesheet" href="assets/css/products.css">
  <link rel="stylesheet" href="assets/css/base.css">
</head>
<body>
<div class="dashboard-container"> 
  <!-- Sidebar -->
  <?php include __DIR__ . '/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content">
    <header class="main-header">
      <h1>Kelola Produk</h1>
    </header>

    <!-- Tambah Produk -->
    <div class="card">
      <h3>Tambah Produk</h3>
      <form method="POST" enctype="multipart/form-data" class="form-product">
        <input type="text" name="name" placeholder="Nama Produk" required>
        <textarea name="description" placeholder="Deskripsi" required></textarea>
        <input type="number" name="price" placeholder="Harga" required>
        <input type="number" name="stock" placeholder="Stok" required>

        <!-- Dropdown kategori -->
        <select name="category" required>
          <option value="">-- Pilih Kategori --</option>
          <?php foreach($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <input type="file" name="image_file" id="add_image_file" accept="image/*">
        <img id="add_preview" src="#" alt="Preview Gambar" style="display:none;width:100px;">
        <button type="submit" name="add_product" class="btn-primary">Tambah Produk</button>
      </form>
    </div>

    <!-- Daftar Produk -->
    <div class="card">
      <h3>Daftar Produk</h3>
      <table class="table-product">
        <thead>
          <tr>
            <th>Gambar</th>
            <th>Nama</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Kategori</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php while($p = $products->fetch_assoc()): ?>
          <tr>
            <td>
              <?php if($p['image_url']): ?>
                <img src="/ecommerce/<?= $p['image_url'] ?>" width="50" alt="<?= htmlspecialchars($p['name']) ?>">
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td>Rp <?= number_format($p['price'],0,',','.') ?></td>
            <td><?= $p['stock'] ?></td>
            <td><?= htmlspecialchars($p['category_name']) ?></td>
            <td>
              <button class="btn-edit" onclick="openEditModal(
                '<?= $p['id'] ?>',
                '<?= htmlspecialchars($p['name'],ENT_QUOTES) ?>',
                '<?= htmlspecialchars($p['description'],ENT_QUOTES) ?>',
                '<?= $p['price'] ?>',
                '<?= $p['stock'] ?>',
                '<?= $p['category_id'] ?>',
                '<?= htmlspecialchars($p['image_url'],ENT_QUOTES) ?>'
              )">Edit</button>
              <form method="POST">
                <input type="hidden" name="delete_product" value="<?= $p['id'] ?>">
                <button type="submit" class="btn-delete" onclick="return confirm('Hapus produk?')">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay"></div>
<div id="editModal" class="modal">
  <h3>Edit Produk</h3>
  <form method="POST" enctype="multipart/form-data" class="form-product">
    <input type="hidden" name="product_id" id="edit_product_id">
    <input type="text" name="name" id="edit_name" required>
    <textarea name="description" id="edit_description" required></textarea>
    <input type="number" name="price" id="edit_price" required>
    <input type="number" name="stock" id="edit_stock" required>

    <!-- Dropdown kategori (edit) -->
    <select name="category" id="edit_category" required>
      <option value="">-- Pilih Kategori --</option>
      <?php foreach($categories as $c): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <input type="file" name="image_file" id="edit_image_file" accept="image/*">
    <img id="edit_preview" src="#" alt="Preview Gambar" style="width:100px;">
    <div class="modal-actions">
      <button type="submit" name="update_product" class="btn-primary">Update</button>
      <button type="button" class="modal-btn-delete" onclick="closeEditModal()">Batal</button>
    </div>
  </form>
</div>

<script>
  // Preview tambah produk
  document.getElementById('add_image_file').addEventListener('change', function(){
      const file = this.files[0];
      if(file){
          const reader = new FileReader();
          reader.onload = function(e){
              const img = document.getElementById('add_preview');
              img.src = e.target.result;
              img.style.display = 'block';
          }
          reader.readAsDataURL(file);
      }
  });

  // Open edit modal
  function openEditModal(id, name, desc, price, stock, categoryId, imageUrl){
      document.getElementById('edit_product_id').value = id;
      document.getElementById('edit_name').value = name;
      document.getElementById('edit_description').value = desc;
      document.getElementById('edit_price').value = price;
      document.getElementById('edit_stock').value = stock;

      // set kategori di dropdown
      document.getElementById('edit_category').value = categoryId;

      const preview = document.getElementById('edit_preview');
      preview.src = imageUrl ? '/ecommerce/' + imageUrl : '#';

      document.getElementById('editModal').style.display = 'block';
      document.getElementById('modalOverlay').style.display = 'block';
  }

  // Preview gambar baru di edit
  document.getElementById('edit_image_file').addEventListener('change', function(){
      const file = this.files[0];
      if(file){
          const reader = new FileReader();
          reader.onload = function(e){
              document.getElementById('edit_preview').src = e.target.result;
          }
          reader.readAsDataURL(file);
      }
  });

  function closeEditModal(){
      document.getElementById('editModal').style.display = 'none';
      document.getElementById('modalOverlay').style.display = 'none';
  }
</script>
</body>
</html>
