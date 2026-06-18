<?php
require_once '../../includes/config.php';
requireRole('pengguna');
$page_title = 'Dashboard';

// Stats
$id = $_SESSION['user_id'];

// Fetch user details for dynamic greeting
$userData = get_user_profile($id);
$nama_user = $userData ? $userData['nama'] : 'Pengguna';

// Greeting based on server time
$hour = intval(date('H'));
if ($hour >= 5 && $hour < 11) {
    $greeting = 'Selamat Pagi';
    $greet_icon = '☀️';
} elseif ($hour >= 11 && $hour < 15) {
    $greeting = 'Selamat Siang';
    $greet_icon = '🌤️';
} elseif ($hour >= 15 && $hour < 18) {
    $greeting = 'Selamat Sore';
    $greet_icon = '⛅';
} else {
    $greeting = 'Selamat Malam';
    $greet_icon = '🌙';
}

// Active parking
$active_parking = get_user_active_parking($id);

// Total transaksi user
$total_tx = db_fetch_value("
    SELECT COUNT(*) 
    FROM transaksi_parkir t 
    JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
    WHERE k.id_pengguna = ?
", [$id]);

// Total pengeluaran
$total_bayar = db_fetch_value("
    SELECT COALESCE(SUM(t.total_biaya), 0) 
    FROM transaksi_parkir t 
    JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
    WHERE k.id_pengguna = ? AND t.status_transaksi = 'selesai'
", [$id]);

// E-wallet balance
$saldo = db_fetch_value("
    SELECT COALESCE(SUM(saldo), 0) 
    FROM e_wallet 
    WHERE id_pengguna = ? AND status_koneksi = 'terhubung'
", [$id]);

// Slot tersedia
$total_slot = db_fetch_value("SELECT SUM(kapasitas) FROM lokasi_parkir");
$slot_terisi = db_fetch_value("SELECT COUNT(*) FROM transaksi_parkir WHERE status_transaksi = 'parkir'");
$slot_tersedia = max(0, $total_slot - $slot_terisi);

// Recent transactions
$recent_tx = db_fetch_all("
    SELECT t.*, k.plat_nomor, k.jenis_kendaraan, s.lokasi 
    FROM transaksi_parkir t 
    JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
    JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi 
    WHERE k.id_pengguna = ? 
    ORDER BY t.waktu_masuk DESC LIMIT 5
", [$id]);

require_once '../../includes/header_user.php';
require_once '../../includes/sidebar_user.php';
?>

<div class="main">
 <div class="topbar">
 <div class="page-title" style="display:flex;align-items:center;gap:6px;font-family:'Syne',sans-serif;">
   <span style="font-size: 15px; font-weight: 500; color: var(--text-muted);"><?= $greeting ?>,</span>
   <span style="font-size: 17px; font-weight: 700; color: var(--text);"><?= htmlspecialchars($nama_user) ?>!</span>
   <span style="font-size: 18px; filter: drop-shadow(0 0 6px rgba(255,255,255,0.35));"><?= $greet_icon ?></span>
 </div>
 <div class="topbar-right">
  <span class="topbar-time" id="clock"></span>
 </div>
 </div>
 <div class="content fade-in-up">

  <?php if ($active_parking): ?>
  <!-- Active Parking Banner -->
  <div class="active-parking-glow" style="margin-bottom:24px;">
    <div class="active-parking-glow-content">
      <div style="display:flex;align-items:center;gap:16px;">
        <div class="stat-icon"></div>
        <div>
          <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:16px;margin-bottom:3px;">Kendaraan Sedang Parkir</div>
          <div style="font-size:13px;color:var(--text-muted);">
            <?= htmlspecialchars($active_parking['plat_nomor']) ?> · Lokasi: <?= htmlspecialchars($active_parking['lokasi']) ?> · Masuk: <?= date('H:i', strtotime($active_parking['waktu_masuk'])) ?>
          </div>
          <div style="font-size:12px;color:var(--success);font-weight:600;margin-top:5px;display:flex;align-items:center;gap:6px;">
            <span class="badge-pulse" style="margin-left:0;width:6px;height:6px;"></span>
            Durasi Parkir: <span id="activeParkingDuration" data-initial-elapsed="<?= time() - strtotime($active_parking['waktu_masuk']) ?>">Menghitung...</span>
          </div>
        </div>
      </div>
      <a href="qrcode.php" class="btn btn-primary" style="z-index: 2;">Lihat QR Keluar</a>
    </div>
  </div>
  <?php endif; ?>

 <!-- Stats -->
 <div class="stat-grid">
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Saldo E-Wallet</div>
 <div class="stat-value" style="color:var(--success);font-size:20px;"><?= formatRupiah($saldo) ?></div>
 <div class="stat-sub"><a href="ewallet.php" style="color:var(--accent);text-decoration:none;">Top Up →</a></div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Slot Tersedia <span class="badge-pulse"></span></div>
 <div class="stat-value" style="color:var(--success);"><?= $slot_tersedia ?></div>
 <div class="stat-sub"><a href="lokasi.php" style="color:var(--accent);text-decoration:none;">Lihat Peta →</a></div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Total Transaksi</div>
 <div class="stat-value"><?= $total_tx ?></div>
 <div class="stat-sub">Sepanjang waktu</div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Total Pembayaran</div>
 <div class="stat-value" style="font-size:18px;"><?= formatRupiah($total_bayar) ?></div>
 <div class="stat-sub">Selesai dibayar</div>
 </div>
 </div>

  <!-- Dashboard Grid Bottom -->
  <div class="dashboard-bottom-layout" style="display:flex;flex-wrap:wrap;gap:20px;margin-bottom:24px;width:100%;align-items:stretch;">
    <!-- Recent Transactions Table -->
    <div class="table-card" style="flex: 2; min-width: 320px; margin-bottom: 0; display: flex; flex-direction: column;">
      <div class="table-header">
      <h3>Riwayat Transaksi Terbaru</h3>
      <a href="transaksi.php" class="btn btn-outline btn-sm">Lihat Semua</a>
      </div>
      <div style="overflow-x: auto; width: 100%;">
        <table>
        <thead>
        <tr>
        <th>ID Transaksi</th>
        <th>Plat Nomor</th>
        <th>Jenis</th>
        <th>Lokasi</th>
        <th>Waktu Masuk</th>
        <th>Durasi</th>
        <th>Biaya</th>
        <th>Status</th>
        </tr>
        </thead>
        <tbody>
         <?php foreach ($recent_tx as $row): ?>
         <tr>
         <td><code style="font-size:12px;color:var(--accent);">TRX-<?= str_pad($row['id_transaksi'], 4, '0', STR_PAD_LEFT) ?></code></td>
         <td><?= htmlspecialchars($row['plat_nomor']) ?></td>
         <td><?= ucfirst($row['jenis_kendaraan']) ?></td>
         <td><?= htmlspecialchars($row['lokasi']) ?></td>
         <td><?= date('d/m H:i', strtotime($row['waktu_masuk'])) ?></td>
         <td><?= $row['durasi'] ? $row['durasi'] . ' menit' : '-' ?></td>
         <td><?= $row['total_biaya'] ? formatRupiah($row['total_biaya']) : '-' ?></td>
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
         <?php endforeach; ?>
         <?php if (count($recent_tx) === 0): ?>
        <tr>
        <td colspan="8" style="text-align:center;color:var(--text-muted);padding:30px;">Belum ada transaksi</td>
        </tr>
        <?php endif; ?>
        </tbody>
        </table>
      </div>
    </div>

    <!-- Weekly Usage Chart Widget -->
    <div class="chart-widget" style="flex: 1; min-width: 280px; display: flex; flex-direction: column; justify-content: space-between;">
       <h3 style="font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
         Aktivitas Parkir 
         <span style="font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">7 Hari Terakhir</span>
       </h3>
       <?php
       $chart_data = [];
       for ($i = 6; $i >= 0; $i--) {
           $date = date('Y-m-d', strtotime("-$i days"));
           $day_name = date('D', strtotime("-$i days"));
           $day_names_id = ['Sun'=>'Min', 'Mon'=>'Sen', 'Tue'=>'Sel', 'Wed'=>'Rab', 'Thu'=>'Kam', 'Fri'=>'Jum', 'Sat'=>'Sab'];
           $day_label = $day_names_id[$day_name] ?? $day_name;
           
           $count = db_fetch_value("
               SELECT COUNT(*) 
               FROM transaksi_parkir t 
               JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
               WHERE k.id_pengguna = ? AND DATE(t.waktu_masuk) = ?
           ", [$id, $date]);
           
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
    const now = new Date();
    document.getElementById('clock').textContent = now.toLocaleString('id-ID', {
      weekday: 'short',
      day: 'numeric',
      month: 'short',
      hour: '2-digit',
      minute: '2-digit'
    });
  }
  updateClock();
  setInterval(updateClock, 1000);

  // Live active parking counter
  const durationEl = document.getElementById('activeParkingDuration');
  if (durationEl) {
    let elapsed = parseInt(durationEl.getAttribute('data-initial-elapsed') || '0');
    
    function updateDuration() {
      const hours = Math.floor(elapsed / 3600);
      const mins = Math.floor((elapsed % 3600) / 60);
      const secs = elapsed % 60;
      
      let durationStr = '';
      if (hours > 0) {
        durationStr += hours + ' jam ';
      }
      durationStr += mins + ' menit ' + secs + ' detik';
      
      durationEl.textContent = durationStr;
      elapsed++;
    }
    
    updateDuration();
    setInterval(updateDuration, 1000);
  }
</script>
</body>
</html>
