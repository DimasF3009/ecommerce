<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Update user
if (isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE user SET name=?, email=?, role=?, status=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $email, $role, $status, $id);
    $stmt->execute();
}

// Hapus user (opsional, lebih baik nonaktifkan saja)
if (isset($_POST['delete_user'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM user WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Ambil semua user
$users = $conn->query("SELECT * FROM user WHERE role != 'admin' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pengguna - SNACK.IDN</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/pengguna.css">
</head>
<body>
  <div class="dashboard-container">

        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main -->
        <main class="main-content">
            <header class="main-header">
                <h1>👥 Kelola Pengguna</h1>
            </header>

            <!-- Daftar user -->
            <section class="content-section user-list">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($row = $users->fetch_assoc()): ?>
                  <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= $row['role'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                      <!-- Tombol Edit -->
                      <button type="button" class="btn-edit" onclick="openEditModal(
                        <?= $row['id'] ?>,
                        '<?= htmlspecialchars($row['name']) ?>',
                        '<?= htmlspecialchars($row['email']) ?>',
                        '<?= $row['role'] ?>',
                        '<?= $row['status'] ?>'
                      )">✏️ Edit</button>

                      <!-- Tombol Delete -->
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus user ini?')">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" name="delete_user" class="btn-delete">🗑️ Hapus</button>
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
      <h3>Edit Pengguna</h3>
      <form method="POST">
        <input type="hidden" name="id" id="edit_id">
        <input type="text" name="name" id="edit_name" required>
        <input type="email" name="email" id="edit_email" required>
        <select name="role" id="edit_role">
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
        <select name="status" id="edit_status">
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
        <button type="submit" name="edit_user" class="btn-save">💾 Simpan</button>
      </form>
    </div>
  </div>

  <script>
    function openEditModal(id, name, email, role, status) {
      document.getElementById("edit_id").value = id;
      document.getElementById("edit_name").value = name;
      document.getElementById("edit_email").value = email;
      document.getElementById("edit_role").value = role;
      document.getElementById("edit_status").value = status;
      document.getElementById("editModal").style.display = "block";
    }
    function closeEditModal() {
      document.getElementById("editModal").style.display = "none";
    }
    window.onclick = function(event) {
      const modal = document.getElementById("editModal");
      if (event.target === modal) {
        modal.style.display = "none";
      }
    }
  </script>
</body>
</html>
