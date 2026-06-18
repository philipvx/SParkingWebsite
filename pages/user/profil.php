<?php
require_once '../../includes/config.php';
requireRole('pengguna');
$page_title = 'Profil Saya';

$user_id = intval($_SESSION['user_id']);
$msg = '';
$err = '';

$columnCheck = $conn->query("SHOW COLUMNS FROM pengguna_parkir LIKE 'foto_profil'");
if (!$columnCheck->fetch_assoc()) {
  $conn->query("ALTER TABLE pengguna_parkir ADD COLUMN foto_profil VARCHAR(255) DEFAULT NULL");
}

$columnCheckBorder = $conn->query("SHOW COLUMNS FROM pengguna_parkir LIKE 'foto_border_warna'");
if (!$columnCheckBorder->fetch_assoc()) {
  $conn->query("ALTER TABLE pengguna_parkir ADD COLUMN foto_border_warna VARCHAR(255) DEFAULT NULL");
}


$stmtUser = $conn->prepare("SELECT p.*, a.username, a.password_hash as password, a.status_login as status_akun FROM pengguna_parkir p JOIN akun_pengguna a ON p.id_pengguna = a.id_akun WHERE p.id_pengguna = ? LIMIT 1");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();

if (!$user) {
 header("Location: " . appUrl('logout.php'));
 exit();
}

function uploadProfilePhoto($file, $oldPhoto = null) {
 if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) return $oldPhoto;
 if ($file['error'] !== UPLOAD_ERR_OK) throw new Exception('Upload foto gagal.');
 if ($file['size'] > 2 * 1024 * 1024) throw new Exception('Ukuran foto maksimal 2MB.');

 $allowed = [
 'image/jpeg' => 'jpg',
 'image/png' => 'png',
 'image/webp' => 'webp'
 ];
 $info = @getimagesize($file['tmp_name']);
 $mime = $info['mime'] ?? '';
 if (!isset($allowed[$mime])) {
 throw new Exception('Format foto harus JPG, PNG, atau WEBP.');
 }

 $uploadDir = __DIR__ . '/../../uploads/profil';
 if (!is_dir($uploadDir)) {
 mkdir($uploadDir, 0775, true);
 }

 $filename = 'user-' . intval($_SESSION['user_id']) . '-' . time() . '.' . $allowed[$mime];
 $target = $uploadDir . '/' . $filename;
 if (!move_uploaded_file($file['tmp_name'], $target)) {
 throw new Exception('Gagal menyimpan foto profil.');
 }

 if ($oldPhoto && strpos($oldPhoto, 'uploads/profil/') === 0) {
 $oldPath = __DIR__ . '/../../' . $oldPhoto;
 if (is_file($oldPath)) @unlink($oldPath);
 }

 return 'uploads/profil/' . $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $action = $_POST['action'] ?? '';

 if ($action === 'update_profile') {
 try {
 $nama = trim($_POST['nama'] ?? '');
 $username = trim($_POST['username'] ?? '');
 $email = trim($_POST['email'] ?? '');
 $no_hp = trim($_POST['no_hp'] ?? '');

 if ($nama === '' || $username === '' || $email === '') {
 throw new Exception('Nama, username, dan email wajib diisi.');
 }
 if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
 throw new Exception('Format email tidak valid.');
 }

  $checkUsername = $conn->prepare("SELECT id_akun FROM akun_pengguna WHERE username = ? AND id_akun <> ? LIMIT 1");
  $checkUsername->bind_param("si", $username, $user_id);
  $checkUsername->execute();
  if ($checkUsername->get_result()->fetch_assoc()) {
    throw new Exception('Username sudah digunakan akun lain.');
  }

  $checkEmail = $conn->prepare("SELECT id_pengguna FROM pengguna_parkir WHERE email = ? AND id_pengguna <> ? LIMIT 1");
  $checkEmail->bind_param("si", $email, $user_id);
  $checkEmail->execute();
  if ($checkEmail->get_result()->fetch_assoc()) {
    throw new Exception('Email sudah digunakan akun lain.');
  }

  $foto = uploadProfilePhoto($_FILES['foto_profil'] ?? null, $user['foto_profil'] ?? null);
  $border = trim($_POST['foto_border_warna'] ?? '');

  $conn->begin_transaction();
  try {
    $stmt1 = $conn->prepare("UPDATE akun_pengguna SET username = ? WHERE id_akun = ?");
    $stmt1->bind_param("si", $username, $user_id);
    $stmt1->execute();

    $stmt2 = $conn->prepare("UPDATE pengguna_parkir SET nama = ?, email = ?, no_hp = ?, foto_profil = ?, foto_border_warna = ? WHERE id_pengguna = ?");
    $stmt2->bind_param("sssssi", $nama, $email, $no_hp, $foto, $border, $user_id);
    $stmt2->execute();

    $conn->commit();

    $_SESSION['nama'] = $nama;
    $_SESSION['email'] = $email;
    $_SESSION['foto_profil'] = $foto;
    $_SESSION['foto_border_warna'] = $border;
    $msg = 'Profil berhasil diperbarui.';
    
    // Refresh user array
    $user['username'] = $username;
    $user['nama'] = $nama;
    $user['email'] = $email;
    $user['no_hp'] = $no_hp;
    $user['foto_profil'] = $foto;
    $user['foto_border_warna'] = $border;
  } catch (Exception $e) {
    $conn->rollback();
    throw $e;
  }
  } catch (Exception $e) {
    $err = $e->getMessage();
  }
  } elseif ($action === 'change_password') {
    $current = $_POST['password_lama'] ?? '';
    $new = $_POST['password_baru'] ?? '';
    $confirm = $_POST['konfirmasi_password'] ?? '';

    if (!password_verify($current, $user['password'])) {
      $err = 'Password lama tidak sesuai.';
    } elseif (strlen($new) < 6) {
      $err = 'Password baru minimal 6 karakter.';
    } elseif ($new !== $confirm) {
      $err = 'Konfirmasi password baru tidak cocok.';
    } else {
      $hash = password_hash($new, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("UPDATE akun_pengguna SET password_hash = ? WHERE id_akun = ?");
      $stmt->bind_param("si", $hash, $user_id);
      $stmt->execute();
      $msg = 'Password berhasil diubah.';
      $user['password'] = $hash;
    }
 } elseif ($action === 'remove_photo') {
 $oldPhoto = $user['foto_profil'] ?? null;
 if ($oldPhoto && strpos($oldPhoto, 'uploads/profil/') === 0) {
 $oldPath = __DIR__ . '/../../' . $oldPhoto;
 if (is_file($oldPath)) @unlink($oldPath);
 }
 $stmt = $conn->prepare("UPDATE pengguna_parkir SET foto_profil = NULL WHERE id_pengguna = ?");
 $stmt->bind_param("i", $user_id);
 $stmt->execute();
 $_SESSION['foto_profil'] = null;
 $msg = 'Foto profil dihapus.';
 }

 $stmtUser = $conn->prepare("SELECT * FROM pengguna_parkir WHERE id_pengguna = ? LIMIT 1");
 $stmtUser->bind_param("i", $user_id);
 $stmtUser->execute();
 $user = $stmtUser->get_result()->fetch_assoc();
}

$photoUrl = !empty($user['foto_profil']) ? appUrl($user['foto_profil']) : '';

require_once '../../includes/header_user.php';
require_once '../../includes/sidebar_user.php';
?>
<div class="main">
 <div class="topbar">
 <div class="page-title">Profil Saya</div>
 </div>
  <div class="content fade-in-up">
  <?php if ($msg): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      showToast(<?= json_encode($msg) ?>, 'success');
    });
  </script>
  <?php endif; ?>
  <?php if ($err): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      showToast(<?= json_encode($err) ?>, 'error');
    });
  </script>
  <?php endif; ?>

 <div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;">
 <div class="form-card" style="text-align:center;">
  <div id="preview-avatar-container" style="width:120px;height:120px;border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;padding:<?= $user['foto_border_warna'] ? '4px' : '0' ?>;background:<?= $user['foto_border_warna'] ?: 'transparent' ?>;overflow:hidden;transition:background 0.2s, padding 0.2s;">
    <div id="preview-avatar-inner" style="width:100%;height:100%;border-radius:50%;background:var(--surface2);border:<?= $user['foto_border_warna'] ? 'none' : '1px solid var(--border)' ?>;display:flex;align-items:center;justify-content:center;overflow:hidden;font-size:42px;font-weight:700;">
    <?php if ($photoUrl): ?>
    <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Foto profil" style="width:100%;height:100%;object-fit:cover;">
    <?php else: ?>
    <?= htmlspecialchars(strtoupper(substr($user['nama'], 0, 1))) ?>
    <?php endif; ?>
    </div>
  </div>
 <h3 style="font-family:'Poppins',sans-serif;font-size:18px;margin-bottom:4px;"><?= htmlspecialchars($user['nama']) ?></h3>
 <div style="color:var(--text-muted);font-size:13px;margin-bottom:4px;">@<?= htmlspecialchars($user['username'] ?? '') ?></div>
 <div style="color:var(--text-muted);font-size:13px;margin-bottom:16px;"><?= htmlspecialchars($user['email']) ?></div>
 <div class="badge badge-green">Akun <?= htmlspecialchars($user['status_akun']) ?></div>
 <?php if ($photoUrl): ?>
 <form method="POST" style="margin-top:16px;">
 <input type="hidden" name="action" value="remove_photo">
 <button type="submit" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">Hapus Foto</button>
 </form>
 <?php endif; ?>
 </div>

 <div>
 <div class="form-card">
 <h3 style="font-family:'Poppins',sans-serif;font-size:16px;margin-bottom:18px;">Informasi Profil</h3>
 <form method="POST" enctype="multipart/form-data">
 <input type="hidden" name="action" value="update_profile">
 <div class="form-grid">
 <div class="form-group">
 <label class="form-label">Nama Lengkap</label>
 <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
 </div>
 <div class="form-group">
 <label class="form-label">Username</label>
 <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
 </div>
 </div>
 <div class="form-grid">
 <div class="form-group">
 <label class="form-label">Email</label>
 <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
 </div>
 <div class="form-group">
 <label class="form-label">No. HP</label>
 <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>" placeholder="08xx...">
 </div>
 </div>
  <div class="form-group">
  <label class="form-label">Foto Profil</label>
  <input type="file" name="foto_profil" class="form-control" accept="image/jpeg,image/png,image/webp">
  <div style="font-size:12px;color:var(--text-muted);margin-top:6px;">JPG, PNG, atau WEBP. Maksimal 2MB.</div>
  </div>

  <div class="form-group" style="margin-bottom: 24px;">
    <label class="form-label">Warna Border Foto Profil</label>
    <input type="hidden" name="foto_border_warna" id="foto_border_warna" value="<?= htmlspecialchars($user['foto_border_warna'] ?? '') ?>">
    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 8px;">
      <!-- None -->
      <div onclick="selectBorderColor(this, '')" class="color-opt <?= empty($user['foto_border_warna']) ? 'active' : '' ?>" style="width: 34px; height: 34px; border-radius: 50%; border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; cursor: pointer; background: transparent;" title="Tanpa Border">
        <span style="font-size: 14px; color: var(--text-muted); font-weight: bold;">✕</span>
      </div>
      <!-- Indigo Cool -->
      <div onclick="selectBorderColor(this, 'linear-gradient(135deg, #6366f1, #8b5cf6)')" class="color-opt <?= $user['foto_border_warna'] === 'linear-gradient(135deg, #6366f1, #8b5cf6)' ? 'active' : '' ?>" style="width: 34px; height: 34px; border-radius: 50%; cursor: pointer; background: linear-gradient(135deg, #6366f1, #8b5cf6);" title="Indigo Cool"></div>
      <!-- Emerald Green -->
      <div onclick="selectBorderColor(this, 'linear-gradient(135deg, #10b981, #059669)')" class="color-opt <?= $user['foto_border_warna'] === 'linear-gradient(135deg, #10b981, #059669)' ? 'active' : '' ?>" style="width: 34px; height: 34px; border-radius: 50%; cursor: pointer; background: linear-gradient(135deg, #10b981, #059669);" title="Emerald Green"></div>
      <!-- Sky Blue -->
      <div onclick="selectBorderColor(this, 'linear-gradient(135deg, #0ea5e9, #2563eb)')" class="color-opt <?= $user['foto_border_warna'] === 'linear-gradient(135deg, #0ea5e9, #2563eb)' ? 'active' : '' ?>" style="width: 34px; height: 34px; border-radius: 50%; cursor: pointer; background: linear-gradient(135deg, #0ea5e9, #2563eb);" title="Sky Blue"></div>
      <!-- Neon Orange -->
      <div onclick="selectBorderColor(this, 'linear-gradient(135deg, #f97316, #db2777)')" class="color-opt <?= $user['foto_border_warna'] === 'linear-gradient(135deg, #f97316, #db2777)' ? 'active' : '' ?>" style="width: 34px; height: 34px; border-radius: 50%; cursor: pointer; background: linear-gradient(135deg, #f97316, #db2777);" title="Neon Orange"></div>
      <!-- Rose Gold -->
      <div onclick="selectBorderColor(this, 'linear-gradient(135deg, #f43f5e, #fb7185)')" class="color-opt <?= $user['foto_border_warna'] === 'linear-gradient(135deg, #f43f5e, #fb7185)' ? 'active' : '' ?>" style="width: 34px; height: 34px; border-radius: 50%; cursor: pointer; background: linear-gradient(135deg, #f43f5e, #fb7185);" title="Rose Gold"></div>
      <!-- Golden Yellow -->
      <div onclick="selectBorderColor(this, 'linear-gradient(135deg, #eab308, #ca8a04)')" class="color-opt <?= $user['foto_border_warna'] === 'linear-gradient(135deg, #eab308, #ca8a04)' ? 'active' : '' ?>" style="width: 34px; height: 34px; border-radius: 50%; cursor: pointer; background: linear-gradient(135deg, #eab308, #ca8a04);" title="Golden Yellow"></div>
    </div>
  </div>
 <button type="submit" class="btn btn-primary">Simpan Profil</button>
 </form>
 </div>

 <div class="form-card">
 <h3 style="font-family:'Poppins',sans-serif;font-size:16px;margin-bottom:18px;">Ubah Password</h3>
 <form method="POST">
 <input type="hidden" name="action" value="change_password">
 <div class="form-grid">
 <div class="form-group">
 <label class="form-label">Password Lama</label>
 <input type="password" name="password_lama" class="form-control" required>
 </div>
 <div class="form-group">
 <label class="form-label">Password Baru</label>
 <input type="password" name="password_baru" class="form-control" minlength="6" required>
 </div>
 </div>
 <div class="form-group">
 <label class="form-label">Konfirmasi Password Baru</label>
 <input type="password" name="konfirmasi_password" class="form-control" minlength="6" required>
 </div>
 <button type="submit" class="btn btn-primary">Ubah Password</button>
 </form>
 </div>
 </div>
 </div>
 </div>
</div>
<style>
.color-opt {
  transition: transform 0.2s, box-shadow 0.2s;
}
.color-opt:hover {
  transform: scale(1.15);
}
.color-opt.active {
  box-shadow: 0 0 0 3px var(--bg), 0 0 0 5px var(--accent);
  transform: scale(1.1);
}

@media (max-width: 860px) {
  .content > div[style*="grid-template-columns:280px"] {
    grid-template-columns: 1fr !important;
  }
}
</style>
<script>
function selectBorderColor(element, color) {
  document.getElementById('foto_border_warna').value = color;
  document.querySelectorAll('.color-opt').forEach(el => el.classList.remove('active'));
  element.classList.add('active');
  
  const previewContainer = document.getElementById('preview-avatar-container');
  const previewInner = document.getElementById('preview-avatar-inner');
  if (color === '') {
    previewContainer.style.padding = '0';
    previewContainer.style.background = 'transparent';
    previewInner.style.border = '1px solid var(--border)';
  } else {
    previewContainer.style.padding = '4px';
    previewContainer.style.background = color;
    previewInner.style.border = 'none';
  }
}
</script>
</body>
</html>
