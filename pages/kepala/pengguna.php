<?php
require_once '../../includes/config.php';
requireRole('kepala_loket');
$page_title = 'Kelola Pengguna';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'toggle') {
    $id = intval($_POST['id_pengguna']);
    $status = $_POST['status_akun'] === 'aktif' ? 'nonaktif' : 'aktif';
    db_execute("UPDATE akun_pengguna SET status_login=? WHERE id_akun=?", [$status, $id]);
    $msg = 'Status pengguna diperbarui!';
  }
}

$search = trim($_GET['q'] ?? '');
$params = [];
$where = '';
if ($search !== '') {
  $where = "WHERE p.nama LIKE ? OR a.username LIKE ? OR p.email LIKE ?";
  $like = '%' . $search . '%';
  $params = [$like, $like, $like];
}

$pengguna_list = db_fetch_all("
  SELECT p.*, a.username, a.status_login as status_akun 
  FROM pengguna_parkir p 
  JOIN akun_pengguna a ON p.id_pengguna = a.id_akun 
  $where 
  ORDER BY p.created_at DESC
", $params);

require_once '../../includes/header_staff.php';
require_once '../../includes/sidebar_kepala.php';
?>
<div class="main">
 <div class="topbar">
 <div class="page-title"> Kelola Pengguna</div>
 <form method="GET" style="display:flex;gap:8px;">
  <input type="text" name="q" class="form-control" placeholder="Cari nama / username / email..." value="<?= htmlspecialchars($search) ?>" style="width:250px;background:var(--surface2);">
  <button class="btn btn-outline btn-sm">Cari</button>
 </form>
 </div>
 <div class="content">
 <?php if ($msg): ?><div class="alert alert-success"> <?= $msg ?></div><?php endif; ?>
 <div class="table-card">
 <div class="table-header">
 <h3>Daftar Pengguna Parkir</h3>
 <span style="font-size:13px;color:var(--text-muted);"><?= count($pengguna_list) ?> pengguna</span>
 </div>
 <table>
  <thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>No. HP</th><th>Terdaftar</th><th>Status</th><th>Aksi</th></tr></thead>
 <tbody>
 <?php foreach ($pengguna_list as $p): ?>
 <tr>
  <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
  <td><code style="color:var(--purple); font-size:13px; font-weight:600;">@<?= htmlspecialchars($p['username'] ?? '-') ?></code></td>
  <td style="color:var(--text-muted);"><?= htmlspecialchars($p['email']) ?></td>
 <td><?= htmlspecialchars($p['no_hp'] ?? '-') ?></td>
 <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
 <td><span class="badge <?= $p['status_akun']==='aktif' ? 'badge-green' : 'badge-red' ?>"><?= $p['status_akun'] ?></span></td>
 <td>
 <form method="POST" style="display:inline;">
 <input type="hidden" name="action" value="toggle">
 <input type="hidden" name="id_pengguna" value="<?= $p['id_pengguna'] ?>">
 <input type="hidden" name="status_akun" value="<?= $p['status_akun'] ?>">
 <button class="btn btn-outline btn-sm"><?= $p['status_akun']==='aktif' ? ' Nonaktifkan' : ' Aktifkan' ?></button>
 </form>
 </td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table>
 </div>
 </div>
</div>
</body></html>