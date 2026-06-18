<?php
require_once '../../includes/config.php';
requireRole('kepala_loket');
$page_title = 'Kelola Petugas';

$msg = '';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'toggle') {
    $id = intval($_POST['id_petugas']);
    $status = $_POST['status_aktif'] === 'aktif' ? 'nonaktif' : 'aktif';
    $conn->query("UPDATE akun_pengguna SET status_login='$status' WHERE id_akun=$id");
    $msg = 'Status petugas berhasil diperbarui.';
  } elseif ($_POST['action'] === 'add') {
    $nama = trim($_POST['nama_petugas'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($nama === '' || $username === '' || $password === '' || $konfirmasi === '') {
      $err = 'Semua field wajib diisi.';
    } elseif ($password !== $konfirmasi) {
      $err = 'Password dan konfirmasi tidak cocok.';
    } else {
      $usernameEsc = $conn->real_escape_string($username);
      $exists = $conn->query("SELECT id_akun FROM akun_pengguna WHERE username='$usernameEsc'");
      if ($exists->num_rows > 0) {
        $err = 'Username sudah digunakan.';
      } else {
        $conn->begin_transaction();
        try {
          $hash = password_hash($password, PASSWORD_DEFAULT);
          $stmt1 = $conn->prepare("INSERT INTO akun_pengguna (username, password_hash, role, status_login) VALUES (?, ?, 'petugas_keluar', 'aktif')");
          $stmt1->bind_param('ss', $username, $hash);
          $stmt1->execute();
          $new_id = $conn->insert_id;
          
          $stmt2 = $conn->prepare("INSERT INTO petugas_loket_keluar (id_petugas, nama_petugas) VALUES (?, ?)");
          $stmt2->bind_param('is', $new_id, $nama);
          $stmt2->execute();
          
          $conn->commit();
          $msg = 'Petugas baru berhasil ditambahkan.';
        } catch (Exception $e) {
          $conn->rollback();
          $err = 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage();
        }
      }
    }
  }
}

$search = trim($_GET['q'] ?? '');
$where = '';
if ($search !== '') {
  $searchEsc = $conn->real_escape_string($search);
  $where = "WHERE p.nama_petugas LIKE '%$searchEsc%' OR a.username LIKE '%$searchEsc%'";
}
$petugas_list = $conn->query("SELECT p.*, a.username, a.status_login as status_aktif FROM petugas_loket_keluar p JOIN akun_pengguna a ON p.id_petugas = a.id_akun $where ORDER BY p.id_petugas DESC");

require_once '../../includes/header_staff.php';
require_once '../../includes/sidebar_kepala.php';
?>
<div class="main">
 <div class="topbar">
 <div class="page-title"> Kelola Petugas</div>
 <form method="GET" style="display:flex;gap:8px;align-items:center;">
 <input type="text" name="q" class="form-control" placeholder="Cari nama / username..." value="<?= htmlspecialchars($search) ?>" style="width:240px;background:var(--surface2);">
 <button class="btn btn-outline btn-sm" type="submit">Cari</button>
 </form>
 </div>
 <div class="content">
 <?php if ($msg): ?><div class="alert alert-success"> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
 <?php if ($err): ?><div class="alert alert-error"> <?= htmlspecialchars($err) ?></div><?php endif; ?>

 <div class="two-col">
 <div class="form-card">
 <h3 style="font-family:'Poppins',sans-serif;font-size:15px;margin-bottom:16px;">Tambah Petugas Baru</h3>
 <form method="POST">
 <input type="hidden" name="action" value="add">
 <div class="form-group">
 <label class="form-label">Nama Petugas</label>
 <input type="text" name="nama_petugas" class="form-control" placeholder="Nama lengkap" required value="<?= htmlspecialchars($_POST['nama_petugas'] ?? '') ?>">
 </div>
 <div class="form-group">
 <label class="form-label">Username</label>
 <input type="text" name="username" class="form-control" placeholder="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
 </div>
 <div class="form-group">
 <label class="form-label">Password</label>
 <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
 </div>
 <div class="form-group">
 <label class="form-label">Konfirmasi Password</label>
 <input type="password" name="konfirmasi" class="form-control" placeholder="Ulangi password" required>
 </div>
 <button type="submit" class="btn btn-primary">Tambah Petugas</button>
 </form>
 </div>

 <div class="table-card">
 <div class="table-header">
 <h3>Daftar Petugas Loket</h3>
 <span style="font-size:13px;color:var(--text-muted);"><?= $petugas_list->num_rows ?> petugas</span>
 </div>
 <table>
 <thead>
 <tr>
 <th>Nama</th>
 <th>Username</th>
 <th>Status</th>
 <th>Aksi</th>
 </tr>
 </thead>
 <tbody>
 <?php while ($petugas = $petugas_list->fetch_assoc()): ?>
 <tr>
 <td><strong><?= htmlspecialchars($petugas['nama_petugas']) ?></strong></td>
 <td style="color:var(--text-muted);"><?= htmlspecialchars($petugas['username']) ?></td>
 <td><span class="badge <?= $petugas['status_aktif'] === 'aktif' ? 'badge-green' : 'badge-red' ?>"><?= htmlspecialchars($petugas['status_aktif']) ?></span></td>
 <td>
 <form method="POST" style="display:inline;">
 <input type="hidden" name="action" value="toggle">
 <input type="hidden" name="id_petugas" value="<?= $petugas['id_petugas'] ?>">
 <input type="hidden" name="status_aktif" value="<?= htmlspecialchars($petugas['status_aktif']) ?>">
 <button class="btn btn-outline btn-sm"><?= $petugas['status_aktif'] === 'aktif' ? ' Nonaktifkan' : ' Aktifkan' ?></button>
 </form>
 </td>
 </tr>
 <?php endwhile; ?>
 </tbody>
 </table>
 </div>
 </div>
 </div>
</div>
</body>

</html>