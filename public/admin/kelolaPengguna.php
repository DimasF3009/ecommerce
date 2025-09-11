<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

/* ========= Update User ========= */
if (isset($_POST['edit_user'])) {
    $id      = $_POST['id'];
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $status  = $_POST['status'];
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user 
            SET name=?, email=?, status=?, phone=?, address=?, password=? 
            WHERE id=?");
        $stmt->bind_param("sssssss", $name, $email, $status, $phone, $address, $hashed, $id);
    } else {
        $stmt = $conn->prepare("UPDATE user 
            SET name=?, email=?, status=?, phone=?, address=? 
            WHERE id=?");
        $stmt->bind_param("ssssss", $name, $email, $status, $phone, $address, $id);
    }
    $stmt->execute();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

/* ========= Nonaktifkan / Aktifkan User ========= */
if (isset($_POST['toggle_status'])) {
    $id = $_POST['id'];
    $new_status = $_POST['new_status']; // 'active' atau 'suspended'
    $stmt = $conn->prepare("UPDATE user SET status=? WHERE id=?");
    $stmt->bind_param("ss", $new_status, $id);
    $stmt->execute();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

/* ========= Hapus User ========= */
if (isset($_POST['delete_user'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM user WHERE id=?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

/* ========= Ambil semua user (kecuali admin utama) ========= */
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
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="main-header">
            <h1>👥 Kelola Pengguna</h1>
        </header>

        <section class="content-section user-list">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Phone</th>
                        <th>Alamat</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= $row['role'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <!-- Tombol Edit -->
                            <button type="button" class="btn-small btn-edit"
                                data-id="<?= $row['id'] ?>"
                                data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>"
                                data-email="<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>"
                                data-status="<?= $row['status'] ?>"
                                data-phone="<?= htmlspecialchars($row['phone'], ENT_QUOTES) ?>"
                                data-address="<?= htmlspecialchars($row['address'], ENT_QUOTES) ?>"
                            >
                            Edit ✏️
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" name="delete_user" class="btn-delete">Hapus 🗑</button>
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
      <input type="text" name="name" id="edit_name" placeholder="Nama" required>
      <input type="email" name="email" id="edit_email" placeholder="Email" required>
      <select name="status" id="edit_status">
        <option value="active">Active</option>
        <option value="suspended">Suspended</option>
      </select>
      <input type="text" name="phone" id="edit_phone" placeholder="Phone">
      <textarea name="address" id="edit_address" placeholder="Alamat"></textarea>
      <input type="password" name="password" id="edit_password" placeholder="Password baru (kosongkan jika tidak diubah)">
      <button type="submit" name="edit_user" class="btn-save">💾 Simpan</button>
    </form>
  </div>
</div>
</body>

<script>
document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('edit_id').value = btn.dataset.id;
    document.getElementById('edit_name').value = btn.dataset.name;
    document.getElementById('edit_email').value = btn.dataset.email;
    document.getElementById('edit_status').value = btn.dataset.status;
    document.getElementById('edit_phone').value = btn.dataset.phone;
    document.getElementById('edit_address').value = btn.dataset.address;

    document.getElementById('editModal').style.display = 'block';
  });
});

function closeEditModal(){
    document.getElementById('editModal').style.display='none';
}

window.onclick = function(event){
    const modal = document.getElementById('editModal');
    if(event.target===modal) modal.style.display='none';
}
</script>
</html>
