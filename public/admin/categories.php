<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Tambah kategori
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if ($name != '') {
        $stmt = $conn->prepare("INSERT INTO category (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
    }
}

// Update kategori
if (isset($_POST['edit_category'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    if ($name != '') {
        $stmt = $conn->prepare("UPDATE category SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
    }
}

// Hapus kategori
if (isset($_POST['delete_category'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM category WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Ambil data kategori
$categories = $conn->query("SELECT * FROM category ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Kategori - SNACK.IDN</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/categories.css">
</head>
<body>
  <div class="dashboard-container">

        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main -->
        <main class="main-content">
            <header class="main-header">
                <h1>📂 Kelola Kategori</h1>
            </header>

            <!-- Form tambah kategori -->
            <section class="content-section">
              <form method="POST" class="category-form">
                <input type="text" name="name" placeholder="Nama kategori baru" required>
                <button type="submit" name="add_category" class="btn-add">➕ Tambah</button>
              </form>
            </section>

            <!-- Daftar kategori -->
            <section class="content-section category-list">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nama Kategori</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($row = $categories->fetch_assoc()): ?>
                  <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                      <!-- Tombol Edit -->
                      <button type="button" class="btn-edit" onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>')">✏️ Edit</button>

                      <!-- Tombol Delete -->
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus kategori ini?')">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" name="delete_category" class="btn-delete">🗑️ Hapus</button>
                      </form>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </section>
        </main>
  </div>

  <!-- Modal Edit -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeEditModal()">&times;</span>
      <h3>Edit Kategori</h3>
      <form method="POST">
        <input type="hidden" name="id" id="edit_id">
        <input type="text" name="name" id="edit_name" required>
        <button type="submit" name="edit_category" class="btn-save">💾 Simpan</button>
      </form>
    </div>
  </div>

  <script>
    function openEditModal(id, name) {
      document.getElementById("edit_id").value = id;
      document.getElementById("edit_name").value = name;
      document.getElementById("editModal").style.display = "block";
    }
    function closeEditModal() {
      document.getElementById("editModal").style.display = "none";
    }
    // Tutup modal jika klik di luar area modal
    window.onclick = function(event) {
      const modal = document.getElementById("editModal");
      if (event.target === modal) {
        modal.style.display = "none";
      }
    }
  </script>
</body>
</html>
