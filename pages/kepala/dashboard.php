<?php
require_once '../../includes/config.php';
requireRole('kepala_loket');
$page_title = 'Dashboard Kepala Loket';

$today = date('Y-m-d');
$month = date('Y-m');

// Stats
$total_slot = $conn->query("SELECT SUM(kapasitas) as c FROM lokasi_parkir")->fetch_assoc()['c'];
$slot_terisi = $conn->query("SELECT COUNT(*) as c FROM transaksi_parkir WHERE status_transaksi='parkir'")->fetch_assoc()['c'];
$slot_tersedia = max(0, $total_slot - $slot_terisi);
$total_pengguna = $conn->query("SELECT COUNT(*) as c FROM pengguna_parkir p JOIN akun_pengguna a ON p.id_pengguna = a.id_akun WHERE a.status_login='aktif'")->fetch_assoc()['c'];
$tx_today = $conn->query("SELECT COUNT(*) as c FROM transaksi_parkir WHERE DATE(waktu_masuk)='$today'")->fetch_assoc()['c'];
$pendapatan_today = $conn->query("SELECT COALESCE(SUM(jumlah_bayar),0) as t FROM pembayaran WHERE DATE(waktu_bayar)='$today' AND status_pembayaran='berhasil'")->fetch_assoc()['t'];
$pendapatan_month = $conn->query("SELECT COALESCE(SUM(jumlah_bayar),0) as t FROM pembayaran WHERE DATE_FORMAT(waktu_bayar,'%Y-%m')='$month' AND status_pembayaran='berhasil'")->fetch_assoc()['t'];
$tx_aktif = $conn->query("SELECT COUNT(*) as c FROM transaksi_parkir WHERE status_transaksi='parkir'")->fetch_assoc()['c'];

// 7 days chart data
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
 $d = date('Y-m-d', strtotime("-$i days"));
 $label = date('D', strtotime($d));
 $tx = $conn->query("SELECT COUNT(*) as c FROM transaksi_parkir WHERE DATE(waktu_masuk)='$d'")->fetch_assoc()['c'];
 $pendapatan = $conn->query("SELECT COALESCE(SUM(jumlah_bayar),0) as t FROM pembayaran WHERE DATE(waktu_bayar)='$d' AND status_pembayaran='berhasil'")->fetch_assoc()['t'];
 $chart_data[] = ['label'=>$label, 'tx'=>$tx, 'pendapatan'=>$pendapatan];
}

// Recent transactions
$recent = $conn->query("SELECT t.*, k.plat_nomor, k.jenis_kendaraan, s.lokasi, pp.nama 
 FROM transaksi_parkir t 
 JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
 JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi 
 LEFT JOIN pengguna_parkir pp ON k.id_pengguna = pp.id_pengguna
 ORDER BY t.waktu_masuk DESC LIMIT 6");

require_once '../../includes/header_staff.php';
require_once '../../includes/sidebar_kepala.php';
?>
<div class="main">
 <div class="topbar">
 <div class="page-title"> Dashboard Kepala Loket</div>
 <div class="topbar-right">
 <span class="topbar-time" id="clock"></span>
 <a href="laporan.php" class="btn btn-primary"> Generate Laporan</a>
 </div>
 </div>
 <div class="content">

 <!-- Main Stats -->
 <div class="stat-grid">
 <div class="stat-card" style="border-color:rgba(6,182,212,0.3);">
 <div class="stat-icon"></div>
 <div class="stat-label">Pendapatan Hari Ini</div>
 <div class="stat-value" style="font-size:18px;color:var(--success);"><?= formatRupiah($pendapatan_today) ?></div>
 <div class="stat-sub">Bulan ini: <?= formatRupiah($pendapatan_month) ?></div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Transaksi Hari Ini</div>
 <div class="stat-value" style="color:var(--accent);"><?= $tx_today ?></div>
 <div class="stat-sub">Aktif sekarang: <strong style="color:var(--warning);"><?= $tx_aktif ?></strong></div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Kapasitas Parkir</div>
 <div class="stat-value"><?= $slot_tersedia ?><span style="font-size:14px;color:var(--text-muted);">/<?= $total_slot ?></span></div>
 <div class="stat-sub">Tersedia · Terisi: <strong style="color:var(--danger);"><?= $slot_terisi ?></strong></div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Pengguna Aktif</div>
 <div class="stat-value"><?= $total_pengguna ?></div>
 <div class="stat-sub"><a href="pengguna.php" style="color:var(--accent);text-decoration:none;">Kelola →</a></div>
 </div>
 </div>

 <div class="two-col">
 <!-- Chart 7 hari -->
 <div class="form-card">
 <h3 style="font-family:'Syne',sans-serif;font-size:14px;margin-bottom:16px;"> Transaksi 7 Hari Terakhir</h3>
 <?php $max_tx = max(array_column($chart_data, 'tx')) ?: 1; ?>
 <div style="display:flex;align-items:flex-end;gap:8px;height:120px;margin-bottom:8px;">
 <?php foreach ($chart_data as $d): ?>
 <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;height:100%;">
 <div style="font-size:11px;color:var(--success);"><?= $d['tx'] ?></div>
 <div style="flex:1;width:100%;display:flex;align-items:flex-end;">
 <div style="width:100%;height:<?= $max_tx > 0 ? round(($d['tx']/$max_tx)*100) : 0 ?>%;background:var(--accent);border-radius:4px 4px 0 0;min-height:4px;opacity:0.8;transition:opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8"></div>
 </div>
 </div>
 <?php endforeach; ?>
 </div>
 <div style="display:flex;gap:8px;">
 <?php foreach ($chart_data as $d): ?>
 <div style="flex:1;text-align:center;font-size:11px;color:var(--text-muted);"><?= $d['label'] ?></div>
 <?php endforeach; ?>
 </div>
 </div>

 <!-- Quick Actions -->
 <div class="form-card">
 <h3 style="font-family:'Syne',sans-serif;font-size:14px;margin-bottom:16px;"> Aksi Cepat</h3>
 <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
 <a href="pengguna.php" class="btn btn-outline" style="justify-content:center;padding:14px 8px;flex-direction:column;gap:4px;text-align:center;">
 <span class="stat-icon" style="margin-bottom:0;"></span><span style="font-size:12px;">Kelola Pengguna</span>
 </a>
 <a href="tarif.php" class="btn btn-outline" style="justify-content:center;padding:14px 8px;flex-direction:column;gap:4px;text-align:center;">
 <span class="stat-icon" style="margin-bottom:0;"></span><span style="font-size:12px;">Kelola Tarif</span>
 </a>
 <a href="lokasi_mgmt.php" class="btn btn-outline" style="justify-content:center;padding:14px 8px;flex-direction:column;gap:4px;text-align:center;">
 <span class="stat-icon" style="margin-bottom:0;"></span><span style="font-size:12px;">Kelola Lokasi</span>
 </a>
 <a href="laporan.php" class="btn btn-outline" style="justify-content:center;padding:14px 8px;flex-direction:column;gap:4px;text-align:center;">
 <span class="stat-icon" style="margin-bottom:0;"></span><span style="font-size:12px;">Laporan</span>
 </a>
 </div>
 </div>
 </div>

 <!-- Recent Transactions -->
 <div class="table-card">
 <div class="table-header">
 <h3>Transaksi Terbaru</h3>
 <a href="transaksi_all.php" class="btn btn-outline btn-sm">Lihat Semua</a>
 </div>
 <table>
 <thead>
 <tr><th>ID</th><th>Pengguna</th><th>Plat Nomor</th><th>Jenis</th><th>Lokasi</th><th>Waktu Masuk</th><th>Total Biaya</th><th>Status</th></tr>
 </thead>
 <tbody>
 <?php while ($row = $recent->fetch_assoc()): ?>
 <tr>
 <td><code style="font-size:11px;color:var(--accent);">TRX-<?= str_pad($row['id_transaksi'],4,'0',STR_PAD_LEFT) ?></code></td>
 <td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
 <td><strong><?= htmlspecialchars($row['plat_nomor']) ?></strong></td>
 <td><?= ucfirst($row['jenis_kendaraan']) ?></td>
 <td><?= htmlspecialchars($row['lokasi']) ?></td>
 <td><?= date('d/m H:i', strtotime($row['waktu_masuk'])) ?></td>
 <td><?= $row['total_biaya'] ? formatRupiah($row['total_biaya']) : '<span style="color:var(--text-muted)">-</span>' ?></td>
 <td>
 <?php if ($row['status_transaksi']==='parkir'): ?>
 <span class="badge badge-blue">Parkir</span>
 <?php elseif ($row['status_transaksi']==='selesai'): ?>
 <span class="badge badge-green">Selesai</span>
 <?php else: ?>
 <span class="badge badge-red">Batal</span>
 <?php endif; ?>
 </td>
 </tr>
 <?php endwhile; ?>
 </tbody>
 </table>
 </div>

 </div>
</div>
<script>
function updateClock() {
 document.getElementById('clock').textContent = new Date().toLocaleString('id-ID',{weekday:'short',day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'});
}
updateClock(); setInterval(updateClock, 1000);
</script>
</body></html>
