<?php
require_once '../../includes/config.php';
requireRole('petugas_keluar');
$page_title = 'Riwayat Hari Ini';

$today = date('Y-m-d');
$tid = $_SESSION['user_id'];

$history = $conn->query("SELECT p.*, t.waktu_masuk, t.waktu_keluar, t.durasi, k.plat_nomor, k.jenis_kendaraan, s.lokasi FROM pembayaran p JOIN transaksi_parkir t ON p.id_transaksi = t.id_transaksi JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi WHERE DATE(p.waktu_bayar) = '$today' AND p.id_petugas = $tid ORDER BY p.waktu_bayar DESC");

require_once '../../includes/header_staff.php';
require_once '../../includes/sidebar_petugas.php';
?>
<div class="main">
 <div class="topbar">
 <div class="page-title">Riwayat Hari Ini</div>
 <div class="topbar-right">
 <span class="topbar-time" id="clock"></span>
 </div>
 </div>
 <div class="content">
 <div class="table-card">
 <div class="table-header">
 <h3>Riwayat Transaksi Hari Ini</h3>
 </div>
 <table>
 <thead>
 <tr><th>Plat Nomor</th><th>Lokasi</th><th>Total Bayar</th><th>Metode</th><th>Waktu Bayar</th><th>Status</th></tr>
 </thead>
 <tbody>
 <?php if ($history->num_rows === 0): ?>
 <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px;">Belum ada transaksi hari ini.</td></tr>
 <?php else: ?>
 <?php while ($row = $history->fetch_assoc()): ?>
 <tr>
 <td><strong><?= htmlspecialchars($row['plat_nomor']) ?></strong></td>
 <td><?= htmlspecialchars($row['lokasi']) ?></td>
 <td style="color:var(--success);font-weight:600;"><?= formatRupiah($row['jumlah_bayar']) ?></td>
 <td><span class="badge <?= $row['metode_pembayaran'] === 'tunai' ? 'badge-yellow' : 'badge-blue' ?>"><?= htmlspecialchars(ucfirst($row['metode_pembayaran'])) ?></span></td>
 <td><?= date('H:i', strtotime($row['waktu_bayar'])) ?></td>
 <td><span class="badge <?= $row['status_pembayaran'] === 'berhasil' ? 'badge-green' : 'badge-red' ?>"><?= htmlspecialchars(ucfirst($row['status_pembayaran'])) ?></span></td>
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
 document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID');
}
updateClock(); setInterval(updateClock, 1000);
</script>
</body>
</html>


