<?php
require_once '../../includes/config.php';
requireRole('kepala_loket');
$page_title = 'Semua Transaksi';

$search = trim($_GET['search'] ?? '');
$where = "1=1";
if ($search !== '') {
 $searchEsc = $conn->real_escape_string($search);
 $where = "(k.plat_nomor LIKE '%$searchEsc%' OR pp.nama LIKE '%$searchEsc%' OR s.lokasi LIKE '%$searchEsc%')";
}

$transactions = $conn->query("SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.merk, k.warna, s.lokasi, pp.nama
 FROM transaksi_parkir t
 JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan
 JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi
 LEFT JOIN pengguna_parkir pp ON k.id_pengguna = pp.id_pengguna
 WHERE $where
 ORDER BY t.waktu_masuk DESC");

require_once '../../includes/header_staff.php';
require_once '../../includes/sidebar_kepala.php';
?>
<div class="main">
 <div class="topbar">
 <div class="page-title">Semua Transaksi</div>
  <div class="topbar-right">
  <span class="topbar-time" id="clock"></span>
  <form method="GET" style="display:flex;gap:8px;align-items:center;">
  <input type="text" name="search" class="form-control" placeholder="Cari plat, pengguna, atau slot" value="<?= htmlspecialchars($search) ?>" style="min-width:220px;">
  <button type="submit" class="btn btn-primary">Cari</button>
  </form>
  </div>
 </div>
 <div class="content">
 <div class="table-card">
 <div class="table-header">
 <h3>Daftar Semua Transaksi</h3>
 </div>
 <table>
 <thead>
 <tr>
 <th>ID</th>
 <th>Pengguna</th>
 <th>Plat Nomor</th>
 <th>Jenis</th>
 <th>Slot</th>
 <th>Masuk</th>
 <th>Keluar</th>
 <th>Total</th>
 <th>Status</th>
 </tr>
 </thead>
 <tbody>
 <?php if ($transactions->num_rows === 0): ?>
 <tr>
 <td colspan="9" style="text-align:center;color:var(--text-muted);padding:30px;">Belum ada transaksi yang memenuhi kriteria.</td>
 </tr>
 <?php else: ?>
 <?php while ($row = $transactions->fetch_assoc()): ?>
 <tr>
 <td><code style="font-size:11px;color:var(--accent);">TRX-<?= str_pad($row['id_transaksi'], 4, '0', STR_PAD_LEFT) ?></code></td>
 <td><?= htmlspecialchars($row['nama'] ?: '-') ?></td>
 <td><strong><?= htmlspecialchars($row['plat_nomor']) ?></strong></td>
 <td><?= htmlspecialchars(ucfirst($row['jenis_kendaraan'])) ?></td>
 <td><?= htmlspecialchars($row['lokasi']) ?></td>
 <td><?= date('d/m/Y H:i', strtotime($row['waktu_masuk'])) ?></td>
 <td><?= $row['waktu_keluar'] ? date('d/m/Y H:i', strtotime($row['waktu_keluar'])) : '<span style="color:var(--text-muted);">-</span>' ?></td>
 <td><?= $row['total_biaya'] !== null ? formatRupiah($row['total_biaya']) : '<span style="color:var(--text-muted);">-</span>' ?></td>
 <td>
 <?php if ($row['status_transaksi'] === 'parkir'): ?>
 <span class="badge badge-blue">Parkir</span>
 <?php elseif ($row['status_transaksi'] === 'selesai'): ?>
 <span class="badge badge-green">Selesai</span>
 <?php else: ?>
 <span class="badge badge-red">Batal</span>
 <?php endif; ?>
 </td>
 </tr>
 <?php endwhile; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
 </div>
</div>
<script>
function updateClock() {
  const clockEl = document.getElementById('clock');
  if (clockEl) {
    clockEl.textContent = new Date().toLocaleTimeString('id-ID');
  }
}
updateClock(); setInterval(updateClock, 1000);
</script>
</body>
</html>


