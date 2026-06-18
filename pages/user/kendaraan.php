<?php
require_once '../../includes/config.php';
requireRole('pengguna');
$page_title = 'Kendaraan Saya';

$user_id = $_SESSION['user_id'];
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kendaraan'])) {
 $plat = trim($_POST['plat_nomor'] ?? '');
 $jenis = trim($_POST['jenis_kendaraan'] ?? '');

 if ($plat === '' || $jenis === '') {
 $err = 'Plat nomor dan jenis kendaraan harus diisi.';
 } else {
 $stmt = $conn->prepare("INSERT INTO kendaraan (id_pengguna, plat_nomor, jenis_kendaraan) VALUES (?,?,?)");
 $stmt->bind_param('iss', $user_id, $plat, $jenis);
 if ($stmt->execute()) {
 $msg = 'Kendaraan berhasil ditambahkan.';
 } else {
 $err = 'Gagal menambahkan kendaraan. Silakan coba lagi.';
 }
 }
}

$stmt = $conn->prepare("SELECT * FROM kendaraan WHERE id_pengguna = ? ORDER BY id_kendaraan DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$vehicles = $stmt->get_result();

require_once '../../includes/header_user.php';
require_once '../../includes/sidebar_user.php';
?>
<div class="main">
 <div class="topbar">
 <div class="page-title">Kendaraan Saya</div>
 <div class="topbar-right">
 <button class="btn btn-outline btn-sm" onclick="location.reload()">?? Refresh</button>
 </div>
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

 <div class="form-card">
 <h3 style="font-family:'Syne',sans-serif;margin-bottom:16px;">Tambah Kendaraan Baru</h3>
 <form method="POST" style="display:grid;gap:16px;">
 <input type="hidden" name="tambah_kendaraan" value="1">
 <div class="form-group">
 <label class="form-label">Plat Nomor</label>
 <input type="text" name="plat_nomor" class="form-control" placeholder="B 1234 ABC" required>
 </div>
 <div class="form-group">
 <label class="form-label">Jenis Kendaraan</label>
 <input type="text" name="jenis_kendaraan" class="form-control" placeholder="Motor / Mobil" required>
 </div>
 <button type="submit" class="btn btn-primary">Tambah Kendaraan</button>
 </form>
 </div>

 <div class="table-card">
 <div class="table-header">
 <h3>Daftar Kendaraan Saya</h3>
 </div>
 <table>
 <thead>
 <tr>
 <th>Plat Nomor</th>
 <th>Jenis</th>
 </tr>
 </thead>
 <tbody>
 <?php if ($vehicles->num_rows === 0): ?>
 <tr>
 <td colspan="2" style="text-align:center;color:var(--text-muted);padding:30px;">Belum ada kendaraan terdaftar.</td>
 </tr>
 <?php else: ?>
 <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
 <tr>
 <td><strong><?= htmlspecialchars($vehicle['plat_nomor']) ?></strong></td>
 <td><?= htmlspecialchars(ucfirst($vehicle['jenis_kendaraan'])) ?></td>
 </tr>
 <?php endwhile; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
 </div>
</div>
</body>
</html>
