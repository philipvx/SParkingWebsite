<?php
require_once '../../includes/config.php';
requireRole('petugas_keluar');
$page_title = 'Transaksi Aktif';

$active = $conn->query("SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.merk, k.warna, s.lokasi
 FROM transaksi_parkir t
 JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan
 JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi
 WHERE t.status_transaksi = 'parkir'
 ORDER BY t.waktu_masuk DESC");

require_once '../../includes/header_staff.php';
require_once '../../includes/sidebar_petugas.php';
?>
<div class="main">
 <div class="topbar">
 <div class="page-title">Transaksi Aktif</div>
 <div class="topbar-right">
 <span class="topbar-time" id="clock"></span>
 </div>
 </div>
 <div class="content">
 <div class="table-card">
 <div class="table-header">
 <h3>Data Kendaraan Sedang Parkir</h3>
 </div>
 <table>
 <thead>
 <tr>
 <th>Plat Nomor</th>
 <th>Jenis Kendaraan</th>
 <th>Merk / Warna</th>
 <th>Lokasi</th>
 <th>Waktu Masuk</th>
 <th>Status</th>
 </tr>
 </thead>
 <tbody>
 <?php while ($row = $active->fetch_assoc()): ?>
 <tr>
 <td><strong><?= htmlspecialchars($row['plat_nomor']) ?></strong></td>
 <td><?= ucfirst($row['jenis_kendaraan']) ?></td>
 <td><?= htmlspecialchars($row['merk']) ?> / <?= htmlspecialchars($row['warna']) ?></td>
 <td><?= htmlspecialchars($row['lokasi']) ?></td>
 <td><?= date('d/m/Y H:i', strtotime($row['waktu_masuk'])) ?></td>
 <td><span class="badge badge-blue">Parkir</span></td>
 </tr>
 <?php endwhile; ?>
 <?php if ($active->num_rows === 0): ?>
 <tr>
 <td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px;">Belum ada kendaraan parkir aktif saat ini.</td>
 </tr>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
 </div>
</div>
<script>
 function updateClock() {
 const now = new Date();
 document.getElementById('clock').textContent = now.toLocaleTimeString('id-ID');
 }
 updateClock();
 setInterval(updateClock, 1000);
</script>
</body>

</html>

