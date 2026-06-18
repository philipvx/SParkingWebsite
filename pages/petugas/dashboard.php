<?php
require_once '../../includes/config.php';
requireRole('petugas_keluar');
$page_title = 'Dashboard Petugas';

// Today stats
$today = date('Y-m-d');
$tid = $_SESSION['user_id'];

$tx_today = $conn->query("SELECT COUNT(*) as c FROM pembayaran WHERE DATE(waktu_bayar)='$today' AND id_petugas=$tid AND status_pembayaran='berhasil'")->fetch_assoc()['c'];
$pendapatan_today = $conn->query("SELECT COALESCE(SUM(jumlah_bayar),0) as total FROM pembayaran WHERE DATE(waktu_bayar)='$today' AND id_petugas=$tid AND status_pembayaran='berhasil' AND metode_pembayaran='tunai'")->fetch_assoc()['total'];
$aktif = $conn->query("SELECT COUNT(*) as c FROM transaksi_parkir WHERE status_transaksi='parkir'")->fetch_assoc()['c'];
$total_slot = $conn->query("SELECT SUM(kapasitas) as c FROM lokasi_parkir")->fetch_assoc()['c'];
$slot_terisi = $conn->query("SELECT COUNT(*) as c FROM transaksi_parkir WHERE status_transaksi='parkir'")->fetch_assoc()['c'];
$slot_tersedia = max(0, $total_slot - $slot_terisi);

// Recent exits today
$recent = $conn->query("SELECT p.*, t.waktu_masuk, t.waktu_keluar, t.durasi, k.plat_nomor, k.jenis_kendaraan, s.lokasi
 FROM pembayaran p
 JOIN transaksi_parkir t ON p.id_transaksi = t.id_transaksi
 JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan
 JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi
 WHERE DATE(p.waktu_bayar) = '$today' AND p.id_petugas = $tid
 ORDER BY p.waktu_bayar DESC LIMIT 8");

require_once '../../includes/header_staff.php';
require_once '../../includes/sidebar_petugas.php';
?>
<div class="main">
 <div class="topbar">
 <div class="page-title">Dashboard Petugas Loket Keluar</div>
 <div class="topbar-right">
 <span class="topbar-time" id="clock"></span>
 <a href="scan_qr.php" class="btn btn-primary"> Scan QR</a>
 </div>
 </div>
 <div class="content">

 <div style="background:linear-gradient(135deg,rgba(249,115,22,0.1),rgba(6,182,212,0.06));border:1px solid rgba(249,115,22,0.2);border-radius:14px;padding:18px 24px;margin-bottom:24px;display:flex;align-items:center;gap:16px;">
 <div style="font-size:32px;"></div>
 <div>
 <div style="font-family:'Syne',sans-serif;font-size:16px;font-weight:700;">Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?>!</div>
 <div style="font-size:13px;color:var(--text-muted);"><?= date('l, d F Y') ?> · Shift aktif</div>
 </div>
 </div>

 <div class="stat-grid">
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Transaksi Hari Ini</div>
 <div class="stat-value" style="color:var(--success);"><?= $tx_today ?></div>
 <div class="stat-sub">Berhasil diproses</div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Tunai Diterima</div>
 <div class="stat-value" style="font-size:18px;color:var(--accent);"><?= formatRupiah($pendapatan_today) ?></div>
 <div class="stat-sub">Pembayaran tunai hari ini</div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Sedang Parkir</div>
 <div class="stat-value" style="color:var(--warning);"><?= $aktif ?></div>
 <div class="stat-sub">Kendaraan aktif</div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Slot Tersedia</div>
 <div class="stat-value" style="color:var(--success);"><?= $slot_tersedia ?></div>
 <div class="stat-sub"><a href="lokasi.php" style="color:var(--accent);text-decoration:none;">Lihat peta</a></div>
 </div>
 </div>

 <!-- Quick Scan Box -->
 <div class="form-card" style="background:linear-gradient(135deg,var(--surface),var(--surface2));">
 <h3 style="font-family:'Syne',sans-serif;margin-bottom:16px;font-size:15px;"> Proses Keluar Cepat</h3>
 <form method="GET" action="scan_qr.php" style="display:flex;gap:10px;">
 <input type="text" name="qr" class="form-control" placeholder="Scan atau ketik kode QR..." style="flex:1;">
 <button type="submit" class="btn btn-primary">Proses →</button>
 </form>
 </div>

  <!-- Today's transactions and chart -->
  <div class="dashboard-bottom-layout" style="display:flex;flex-wrap:wrap;gap:20px;margin-bottom:24px;width:100%;align-items:stretch;">
    <!-- Today's transactions -->
    <div class="table-card" style="flex:2;min-width:320px;margin-bottom:0;display:flex;flex-direction:column;">
      <div class="table-header">
      <h3>Transaksi Hari Ini</h3>
      <a href="riwayat.php" class="btn btn-outline btn-sm">Lihat Semua</a>
      </div>
      <div style="overflow-x:auto;width:100%;">
        <table>
        <thead>
        <tr><th>Plat Nomor</th><th>Lokasi</th><th>Durasi</th><th>Total Bayar</th><th>Metode</th><th>Waktu Keluar</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php while ($row = $recent->fetch_assoc()): ?>
        <tr>
        <td><strong><?= htmlspecialchars($row['plat_nomor']) ?></strong></td>
        <td><?= htmlspecialchars($row['lokasi']) ?></td>
        <td><?= $row['durasi'] ? $row['durasi'].' menit' : '-' ?></td>
        <td style="color:var(--success);font-weight:600;"><?= formatRupiah($row['jumlah_bayar']) ?></td>
        <td><span class="badge <?= $row['metode_pembayaran']==='tunai' ? 'badge-yellow' : 'badge-blue' ?>"><?= ucfirst($row['metode_pembayaran']) ?></span></td>
        <td><?= date('H:i', strtotime($row['waktu_bayar'])) ?></td>
        <td><span class="badge <?= $row['status_pembayaran']==='berhasil' ? 'badge-green' : 'badge-red' ?>"><?= ucfirst($row['status_pembayaran']) ?></span></td>
        </tr>
        <?php endwhile; ?>
        <?php if ($recent->num_rows === 0): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:30px;">Belum ada transaksi hari ini</td></tr>
        <?php endif; ?>
        </tbody>
        </table>
      </div>
    </div>

    <!-- Weekly Activity Chart Widget -->
    <div class="chart-widget" style="flex:1;min-width:280px;display:flex;flex-direction:column;justify-content:space-between;margin-bottom:0;">
       <h3 style="font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
         Aktivitas Petugas
         <span style="font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">7 Hari Terakhir</span>
       </h3>
       <?php
       $chart_data = [];
       for ($i = 6; $i >= 0; $i--) {
           $date = date('Y-m-d', strtotime("-$i days"));
           $day_name = date('D', strtotime("-$i days"));
           $day_names_id = ['Sun'=>'Min', 'Mon'=>'Sen', 'Tue'=>'Sel', 'Wed'=>'Rab', 'Thu'=>'Kam', 'Fri'=>'Jum', 'Sat'=>'Sab'];
           $day_label = $day_names_id[$day_name] ?? $day_name;
           
           $stmt_c = $conn->prepare("SELECT COUNT(*) as c FROM pembayaran WHERE id_petugas = ? AND DATE(waktu_bayar) = ? AND status_pembayaran='berhasil'");
           $stmt_c->bind_param("is", $tid, $date);
           $stmt_c->execute();
           $count = $stmt_c->get_result()->fetch_assoc()['c'];
           
           $chart_data[$day_label] = $count;
       }
       
       $max_val = max(1, max($chart_data));
       ?>
       <div class="chart-container">
         <?php foreach ($chart_data as $label => $val): 
           $percent = round(($val / $max_val) * 100);
           if ($percent == 0) $percent = 5; 
         ?>
         <div class="chart-bar-col">
           <div class="chart-bar-val"><?= $val ?>x</div>
           <div class="chart-bar-wrap">
             <div class="chart-bar" style="height: 0%;" data-height="<?= $percent ?>%"></div>
           </div>
           <div class="chart-label"><?= $label ?></div>
         </div>
         <?php endforeach; ?>
       </div>
    </div>
  </div>

 </div>
</div>
<script>
function updateClock() {
 const n = new Date();
 document.getElementById('clock').textContent = n.toLocaleTimeString('id-ID');
}
updateClock(); setInterval(updateClock, 1000);
</script>
</body></html>


