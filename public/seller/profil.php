<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'penjual') {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$seller_id = $_SESSION['user']['id'];

$user_res = $conn->query("SELECT * FROM user WHERE id='$seller_id'");
$user = $user_res->fetch_assoc();

$success = false;

// Update profil
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $description = $_POST['store_description'];

    $image_sql = '';
    if (isset($_FILES['store_logo']) && $_FILES['store_logo']['error'] == 0) {
        $ext = pathinfo($_FILES['store_logo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $target = __DIR__ . '/../../assets/img/store/' . $filename;
        if (!is_dir(__DIR__ . '/../../assets/img/store/')) {
            mkdir(__DIR__ . '/../../assets/img/store/', 0777, true);
        }
        if (move_uploaded_file($_FILES['store_logo']['tmp_name'], $target)) {
            $image_sql = ", store_logo='assets/img/store/$filename'";
        }
    }

    $conn->query("UPDATE user SET 
                    name='$name',
                    phone='$phone',
                    address='$address',
                    store_description='$description'
                    $image_sql
                  WHERE id='$seller_id'");

    $success = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Toko - SNACK.IDN</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/profil.css">
  <style>
    .alert-success {
      background: #d1fae5;
      border: 1px solid #10b981;
      color: #065f46;
      padding: 10px 15px;
      border-radius: 8px;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      animation: fadeIn 0.3s ease-in-out;
    }
    .alert-success.fade-out {
      opacity: 0;
      transition: opacity 0.5s ease-out;
    }
    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(-5px);}
      to {opacity: 1; transform: translateY(0);}
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
      <h2>Profil Toko</h2>

      <?php if ($success): ?>
        <div class="alert-success" id="successAlert">
          ✅ Profil berhasil diperbarui
        </div>
      <?php endif; ?>

      <div class="card">
        <form method="POST" enctype="multipart/form-data">
          <label for="name">Nama Toko</label>
          <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

          <label for="phone">No. Telepon</label>
          <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">

          <label for="address">Alamat</label>
          <textarea id="address" name="address"><?= htmlspecialchars($user['address']) ?></textarea>

          <label for="store_description">Deskripsi Toko</label>
          <textarea id="store_description" name="store_description"><?= htmlspecialchars($user['store_description'] ?? '') ?></textarea>

          <label for="store_logo">Logo Toko</label>
          <div class="profile-preview">
            <img id="profile_preview" src="<?= $user['store_logo'] ? '/ecommerce/' . $user['store_logo'] : '/ecommerce/assets/img/default_profile.png' ?>" alt="Logo Toko">
            <input type="file" name="store_logo" id="store_logo" accept="image/*">
          </div>

          <button type="submit" name="update_profile">Simpan Perubahan</button>
        </form>
      </div>
    </main>
  </div>
</body>
<script>
  // Preview gambar
  document.getElementById('store_logo').addEventListener('change', function(){
    const file = this.files[0];
    if(file){
      const reader = new FileReader();
      reader.onload = function(e){
        document.getElementById('profile_preview').src = e.target.result;
      }
      reader.readAsDataURL(file);
    }
  });

  // Auto hide alert sukses
  const alert = document.getElementById('successAlert');
  if(alert){
    setTimeout(() => {
      alert.classList.add('fade-out');
    }, 3000);
    setTimeout(() => {
      alert.remove();
    }, 3500);
  }
</script>
</html>
