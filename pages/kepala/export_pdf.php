<?php
require_once '../../includes/config.php';
requireRole('kepala_loket');

$months = [
  1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
  7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$tanggal_cetak = date('d') . ' ' . $months[intval(date('m'))] . ' ' . date('Y');

$jenis = $_GET['jenis'] ?? 'harian';
$periode = $_GET['periode'] ?? date('Y-m-d');

$laporan = null;
$detail = [];

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
  
  // For monthly, fetch daily summaries
  $detail = db_fetch_all("
    SELECT DATE(t.waktu_masuk) as tgl, COUNT(t.id_transaksi) as c, COALESCE(SUM(p.jumlah_bayar),0) as t
    FROM transaksi_parkir t
    LEFT JOIN pembayaran p ON p.id_transaksi=t.id_transaksi AND p.status_pembayaran='berhasil'
    WHERE DATE_FORMAT(t.waktu_masuk,'%Y-%m')=?
    GROUP BY DATE(t.waktu_masuk)
    ORDER BY DATE(t.waktu_masuk) ASC
  ", [$ym]);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Parkir UTN - <?= ucfirst($jenis) ?> (<?= htmlspecialchars($periode) ?>)</title>
  <style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 20px; font-size: 13px; line-height: 1.4; }
    .header { position: relative; min-height: 65px; text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .header h1 { margin: 0 0 5px; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
    .header p { margin: 0; color: #555; font-size: 12px; }
    .meta-info { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 11px; color: #555; }
    .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
    .summary-card { border: 1px solid #ddd; border-radius: 6px; padding: 10px; text-align: center; background: #fafafa; }
    .summary-label { font-size: 10px; text-transform: uppercase; color: #777; margin-bottom: 4px; font-weight: bold; }
    .summary-value { font-size: 15px; font-weight: bold; color: #111; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
    th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
    th { background-color: #f5f5f5; font-weight: bold; text-transform: uppercase; font-size: 10px; }
    tr:nth-child(even) { background-color: #fafafa; }
    .footer { text-align: center; margin-top: 35px; font-size: 10px; color: #888; border-top: 1px solid #eee; padding-top: 10px; }
    @media print {
      body { margin: 0; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>
  <div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; background: #eef2ff; padding: 10px 15px; border-radius: 8px; border: 1px solid #c7d2fe;">
    <div style="font-size: 12px; font-weight: 500; color: #4338ca;">Laporan siap diexport. Gunakan menu printer browser untuk cetak atau simpan sebagai PDF.</div>
    <div>
      <button onclick="window.print()" style="background: #4f46e5; color: white; border: none; padding: 6px 12px; border-radius: 5px; font-size: 12px; font-weight: 600; cursor: pointer; margin-right: 6px;">Cetak / PDF</button>
      <button onclick="window.close()" style="background: #white; border: 1px solid #d1d5db; padding: 6px 12px; border-radius: 5px; font-size: 12px; cursor: pointer; color: #374151;">Tutup</button>
    </div>
  </div>

  <div class="header">
    <img src="<?= htmlspecialchars(appUrl('logo_utn_nobg.png')) ?>" alt="Logo UTN" style="position: absolute; left: 0; top: 0; height: 60px; object-fit: contain;">
    <h1 style="padding-left: 70px; padding-right: 70px;">Universitas Teknologi Nusantara</h1>
    <p style="padding-left: 70px; padding-right: 70px;">Sistem Informasi Parkir Terintegrasi (SParking UTN)</p>
    <p style="font-size: 13px; font-weight: bold; margin-top: 8px; color: #111;">LAPORAN DATA PARKIR <?= strtoupper($jenis) ?></p>
  </div>

  <div class="meta-info">
    <div>
      <strong>Periode:</strong> <?= htmlspecialchars($periode) ?><br>
      <strong>Jenis Laporan:</strong> Laporan <?= ucfirst($jenis) ?>
    </div>
    <div style="text-align: right;">
      <strong>Dicetak Oleh:</strong> <?= htmlspecialchars($_SESSION['nama']) ?> (Kepala Loket)<br>
      <strong>Waktu Cetak:</strong> <?= date('d/m/Y H:i:s') ?>
    </div>
  </div>

  <div class="summary-grid">
    <div class="summary-card">
      <div class="summary-label">Total Transaksi</div>
      <div class="summary-value"><?= $laporan['total_transaksi'] ?></div>
    </div>
    <div class="summary-card">
      <div class="summary-label">Transaksi Selesai</div>
      <div class="summary-value"><?= $laporan['total_selesai'] ?></div>
    </div>
    <div class="summary-card">
      <div class="summary-label">Rata-rata Durasi</div>
      <div class="summary-value"><?= round($laporan['rata_durasi']) ?> mnt</div>
    </div>
    <div class="summary-card">
      <div class="summary-label">Total Pendapatan</div>
      <div class="summary-value"><?= formatRupiah($laporan['total_pendapatan']) ?></div>
    </div>
  </div>

  <?php if ($jenis === 'harian'): ?>
    <?php if ($detail): ?>
      <h3 style="font-size: 12px; margin-bottom: 8px;">Daftar Detail Transaksi Parkir</h3>
      <table>
        <thead>
          <tr>
            <th>ID Transaksi</th>
            <th>Plat Nomor</th>
            <th>Jenis Kendaraan</th>
            <th>Lokasi Parkir</th>
            <th>Waktu Masuk</th>
            <th>Waktu Keluar</th>
            <th>Durasi</th>
            <th>Biaya</th>
            <th>Metode</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($detail as $r): ?>
            <tr>
              <td>TRX-<?= str_pad($r['id_transaksi'],4,'0',STR_PAD_LEFT) ?></td>
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
    <?php endif; ?>
  <?php else: ?>
    <?php if ($detail): ?>
      <h3 style="font-size: 12px; margin-bottom: 8px;">Rincian Transaksi & Pendapatan Harian</h3>
      <table>
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Jumlah Transaksi</th>
            <th>Total Pendapatan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($detail as $r): ?>
            <tr>
              <td><?= date('d/m/Y', strtotime($r['tgl'])) ?></td>
              <td><?= $r['c'] ?> kali</td>
              <td><?= formatRupiah($r['t']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  <?php endif; ?>

  <div style="margin-top: 45px; display: flex; justify-content: flex-end; page-break-inside: avoid; margin-bottom: 30px;">
    <div style="text-align: center; width: 220px; font-size: 12px; line-height: 1.5;">
      <p style="margin-bottom: 70px;">
        Jakarta, <?= $tanggal_cetak ?><br>
        Mengetahui,<br>
        <strong>Kepala Loket Parkir</strong>
      </p>
      <p style="text-decoration: underline; font-weight: bold; margin-bottom: 2px;">
        <?= htmlspecialchars($_SESSION['nama']) ?>
      </p>
      <span style="font-size: 10px; color: #666;">ID Staff: #<?= str_pad($_SESSION['user_id'], 4, '0', STR_PAD_LEFT) ?></span>
    </div>
  </div>

  <div class="footer">
    Laporan ini dicetak melalui sistem komputerisasi SParking Universitas Teknologi Nusantara.<br>
    &copy; <?= date('Y') ?> Universitas Teknologi Nusantara.
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      setTimeout(() => {
        window.print();
      }, 300);
    });
  </script>
</body>
</html>


