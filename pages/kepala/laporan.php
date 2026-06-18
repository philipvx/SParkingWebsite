<?php
require_once '../../includes/config.php';
requireRole('kepala_loket');
$page_title = 'Laporan Parkir';

$jenis = $_POST['jenis_laporan'] ?? 'harian';
$periode = $_POST['periode'] ?? date('Y-m-d');

$laporan = null;
$detail = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($jenis === 'harian') {
    $laporan['total_transaksi'] = db_fetch_value("SELECT COUNT(*) FROM transaksi_parkir WHERE DATE(waktu_masuk)=?", [$periode]);
    $laporan['total_pendapatan'] = db_fetch_value("SELECT COALESCE(SUM(jumlah_bayar),0) FROM pembayaran WHERE DATE(waktu_bayar)=? AND status_pembayaran='berhasil'", [$periode]);
    $laporan['total_selesai'] = db_fetch_value("SELECT COUNT(*) FROM transaksi_parkir WHERE DATE(waktu_masuk)=? AND status_transaksi='selesai'", [$periode]);
    $laporan['rata_durasi'] = db_fetch_value("SELECT COALESCE(AVG(durasi),0) FROM transaksi_parkir WHERE DATE(waktu_masuk)=? AND durasi IS NOT NULL", [$periode]);
    
    $detail = db_fetch_all("
      SELECT t.*, k.plat_nomor, k.jenis_kendaraan, s.lokasi, p.jumlah_bayar, p.metode_pembayaran
      FROM transaksi_parkir t 
      JOIN kendaraan k ON t.id_kendaraan=k.id_kendaraan
      JOIN lokasi_parkir s ON t.id_lokasi=s.id_lokasi
      LEFT JOIN pembayaran p ON p.id_transaksi=t.id_transaksi AND p.status_pembayaran='berhasil'
      WHERE DATE(t.waktu_masuk)=? 
      ORDER BY t.waktu_masuk DESC
    ", [$periode]);
  } else {
    // Monthly
    $ym = date('Y-m', strtotime($periode));
    $laporan['total_transaksi'] = db_fetch_value("SELECT COUNT(*) FROM transaksi_parkir WHERE DATE_FORMAT(waktu_masuk,'%Y-%m')=?", [$ym]);
    $laporan['total_pendapatan'] = db_fetch_value("SELECT COALESCE(SUM(jumlah_bayar),0) FROM pembayaran WHERE DATE_FORMAT(waktu_bayar,'%Y-%m')=? AND status_pembayaran='berhasil'", [$ym]);
    $laporan['total_selesai'] = db_fetch_value("SELECT COUNT(*) FROM transaksi_parkir WHERE DATE_FORMAT(waktu_masuk,'%Y-%m')=? AND status_transaksi='selesai'", [$ym]);
    $laporan['rata_durasi'] = db_fetch_value("SELECT COALESCE(AVG(durasi),0) FROM transaksi_parkir WHERE DATE_FORMAT(waktu_masuk,'%Y-%m')=? AND durasi IS NOT NULL", [$ym]);
  }
  
  // Save to laporan_parkir
  $id_kepala = $_SESSION['user_id'];
  db_execute(
      "INSERT INTO laporan_parkir (jenis_laporan, periode, total_transaksi, total_pendapatan, id_kepala) VALUES (?,?,?,?,?)",
      [$jenis, $periode, $laporan['total_transaksi'], $laporan['total_pendapatan'], $id_kepala]
  );
}

require_once '../../includes/header_staff.php';
require_once '../../includes/sidebar_kepala.php';
?>
<div class="main">
  <div class="topbar">
    <div class="page-title">Generate Laporan Parkir</div>
    <?php if ($laporan): ?>
      <div class="topbar-right">
        <a href="export_pdf.php?jenis=<?= urlencode($jenis) ?>&periode=<?= urlencode($periode) ?>" target="_blank" class="btn btn-primary" style="text-decoration:none;">Export PDF</a>
      </div>
    <?php endif; ?>
  </div>
  <div class="content">

  <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;">
    <!-- Filter Form -->
    <div class="form-card" style="height:fit-content;">
      <h3 style="font-family:'Syne',sans-serif;font-size:15px;margin-bottom:16px;">Parameter Laporan</h3>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Jenis Laporan</label>
          <select name="jenis_laporan" class="form-control">
            <option value="harian" <?= $jenis==='harian'?'selected':'' ?>>Harian</option>
            <option value="bulanan" <?= $jenis==='bulanan'?'selected':'' ?>>Bulanan</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Periode</label>
          <input type="date" name="periode" class="form-control" value="<?= htmlspecialchars($periode) ?>">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Generate</button>
      </form>
    </div>

    <!-- Results -->
    <div>
      <?php if ($laporan): ?>
        <div class="stat-grid" style="grid-template-columns:1fr 1fr; margin-bottom: 24px;">
          <div class="stat-card"><div class="stat-icon"></div><div class="stat-label">Total Transaksi</div><div class="stat-value"><?= $laporan['total_transaksi'] ?></div></div>
          <div class="stat-card"><div class="stat-icon"></div><div class="stat-label">Selesai</div><div class="stat-value" style="color:var(--success);"><?= $laporan['total_selesai'] ?></div></div>
          <div class="stat-card"><div class="stat-icon"></div><div class="stat-label">Total Pendapatan</div><div class="stat-value" style="font-size:17px;color:var(--success);"><?= formatRupiah($laporan['total_pendapatan']) ?></div></div>
          <div class="stat-card"><div class="stat-icon">⏱️</div><div class="stat-label">Rata-rata Durasi</div><div class="stat-value" style="font-size:18px;"><?= round($laporan['rata_durasi']) ?><span style="font-size:12px;color:var(--text-muted);"> menit</span></div></div>
        </div>

        <?php if ($jenis === 'harian' && $detail): ?>
          <div class="table-card">
            <div class="table-header">
              <h3>Detail Transaksi — <?= htmlspecialchars($periode) ?></h3>
            </div>
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Plat</th>
                  <th>Jenis</th>
                  <th>Slot</th>
                  <th>Masuk</th>
                  <th>Keluar</th>
                  <th>Durasi</th>
                  <th>Bayar</th>
                  <th>Metode</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($detail as $r): ?>
                  <tr>
                    <td><code style="font-size:11px;color:var(--accent);">TRX-<?= str_pad($r['id_transaksi'],4,'0',STR_PAD_LEFT) ?></code></td>
                    <td><?= htmlspecialchars($r['plat_nomor']) ?></td>
                    <td><?= ucfirst($r['jenis_kendaraan']) ?></td>
                    <td><?= htmlspecialchars($r['lokasi']) ?></td>
                    <td><?= date('H:i', strtotime($r['waktu_masuk'])) ?></td>
                    <td><?= $r['waktu_keluar'] ? date('H:i', strtotime($r['waktu_keluar'])) : '-' ?></td>
                    <td><?= $r['durasi'] ? $r['durasi'].' mnt' : '-' ?></td>
                    <td><?= $r['jumlah_bayar'] ? formatRupiah($r['jumlah_bayar']) : '-' ?></td>
                    <td><?= $r['metode_pembayaran'] ? ucfirst($r['metode_pembayaran']) : '-' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php elseif ($jenis === 'bulanan'): ?>
          <div class="form-card" style="text-align:center;padding:24px;color:var(--text-muted);">
            Laporan bulanan berhasil digenerate. Klik <strong>Export PDF</strong> untuk melihat rincian harian.
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="form-card" style="text-align:center;padding:40px;color:var(--text-muted);">
          <div class="stat-icon" style="margin-bottom:12px;"></div>
          <div>Pilih parameter dan klik <strong>Generate</strong> untuk melihat laporan</div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  </div>
</div>
</body>
</html>


