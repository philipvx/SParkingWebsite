<?php
require_once '../../includes/config.php';
requireRole('pengguna');
$page_title = 'Riwayat Transaksi';

$user_id = $_SESSION['user_id'];

$transactions = db_fetch_all("
  SELECT t.*, k.plat_nomor, k.jenis_kendaraan, s.lokasi, p.metode_pembayaran, sd.nomor_struk, sd.tanggal_struk
  FROM transaksi_parkir t
  JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan
  JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi
  LEFT JOIN pembayaran p ON t.id_transaksi = p.id_transaksi
  LEFT JOIN struk_digital sd ON p.id_pembayaran = sd.id_pembayaran
  WHERE k.id_pengguna = ?
  ORDER BY t.waktu_masuk DESC
", [$user_id]);

$tx_count = db_fetch_value("
  SELECT COUNT(*) 
  FROM transaksi_parkir t 
  JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
  WHERE k.id_pengguna = ?
", [$user_id]);

$total_cost = db_fetch_value("
  SELECT COALESCE(SUM(total_biaya), 0) 
  FROM transaksi_parkir t 
  JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
  WHERE k.id_pengguna = ? AND t.status_transaksi = 'selesai'
", [$user_id]);

require_once '../../includes/header_user.php';
require_once '../../includes/sidebar_user.php';
?>
<div class="main">
 <div class="topbar">
 <div class="page-title">Riwayat Transaksi</div>
 <div class="topbar-right">
 <span class="topbar-time" id="clock"></span>
 </div>
 </div>
 <div class="content">
 <div class="stat-grid">
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Total Transaksi</div>
 <div class="stat-value"><?= intval($tx_count) ?></div>
 <div class="stat-sub">Semua parkir Anda</div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-label">Total Bayar</div>
 <div class="stat-value" style="color:var(--success);"><?= formatRupiah($total_cost) ?></div>
 <div class="stat-sub">Transaksi selesai</div>
 </div>
 </div>

 <div class="table-card">
  <div class="table-header">
  <h3>Daftar Transaksi</h3>
  </div>
  <table>
  <thead>
  <tr>
  <th>ID</th>
  <th>Plat Nomor</th>
  <th>Jenis</th>
  <th>Lokasi</th>
  <th>Waktu Masuk</th>
  <th>Waktu Keluar</th>
  <th>Total</th>
  <th>Metode</th>
  <th>Status</th>
  </tr>
  </thead>
  <tbody>
   <?php if (count($transactions) === 0): ?>
   <tr>
   <td colspan="9" style="text-align:center;color:var(--text-muted);padding:30px;">Belum ada transaksi.</td>
   </tr>
   <?php else: ?>
   <?php foreach ($transactions as $row): ?>
   <tr>
   <td><?= intval($row['id_transaksi']) ?></td>
   <td><strong><?= htmlspecialchars($row['plat_nomor']) ?></strong></td>
   <td><?= htmlspecialchars(ucfirst($row['jenis_kendaraan'])) ?></td>
   <td><?= htmlspecialchars($row['lokasi']) ?></td>
   <td><?= date('d/m/Y H:i', strtotime($row['waktu_masuk'])) ?></td>
   <td><?= $row['waktu_keluar'] ? date('d/m/Y H:i', strtotime($row['waktu_keluar'])) : '-' ?></td>
   <td><?= $row['total_biaya'] !== null ? formatRupiah($row['total_biaya']) : '-' ?></td>
   <td>
      <?php if ($row['status_transaksi'] === 'selesai'): ?>
        <span class="badge <?= ($row['metode_pembayaran'] ?? 'tunai') === 'tunai' ? 'badge-yellow' : 'badge-blue' ?>">
          <?= htmlspecialchars(ucfirst($row['metode_pembayaran'] ?? 'tunai')) ?>
        </span>
      <?php else: ?>
        -
      <?php endif; ?>
    </td>
   <td>
     <div style="display: flex; align-items: center; gap: 8px;">
       <?php if ($row['status_transaksi'] === 'selesai'): ?>
         <span class="badge badge-green">Selesai</span>
         <?php
           $no_struk = $row['nomor_struk'] ?: 'STR-' . date('Ymd', strtotime($row['waktu_keluar'])) . '-' . str_pad($row['id_transaksi'], 4, '0', STR_PAD_LEFT);
           $tgl_struk = $row['tanggal_struk'] ?: $row['waktu_keluar'];
           $receipt_data = [
             'trx_id' => 'TRX-' . str_pad($row['id_transaksi'], 4, '0', STR_PAD_LEFT),
             'plat' => $row['plat_nomor'],
             'jenis' => ucfirst($row['jenis_kendaraan']),
             'lokasi' => $row['lokasi'],
             'masuk' => date('d/m/Y H:i', strtotime($row['waktu_masuk'])),
             'keluar' => date('d/m/Y H:i', strtotime($row['waktu_keluar'])),
             'durasi' => $row['durasi'] . ' menit',
             'biaya' => formatRupiah($row['total_biaya']),
             'metode' => ucfirst($row['metode_pembayaran'] ?? 'tunai'),
             'no_struk' => $no_struk,
             'tgl_struk' => date('d/m/Y H:i', strtotime($tgl_struk))
           ];
         ?>
         <button class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 11px;" onclick="showReceipt(<?= htmlspecialchars(json_encode($receipt_data)) ?>)">Lihat</button>
       <?php else: ?>
         <span class="badge badge-yellow">Parkir</span>
       <?php endif; ?>
     </div>
   </td>
   </tr>
   <?php endforeach; ?>
   <?php endif; ?>
  </tbody>
  </table>
  </div>
  </div>
</div>

<!-- Receipt Modal -->
<div class="pk-modal-overlay" id="receiptModal" onclick="if(event.target===this)this.classList.remove('open')">
 <div class="pk-modal" style="position:relative; max-width:380px; padding: 24px;">
    <span class="pk-modal-pill"></span>
    
    <div style="text-align:center; margin-bottom:18px;">
      <div style="font-size:28px; margin-bottom:6px;">📄</div>
      <h3 style="font-family:'Syne',sans-serif;font-size:18px;font-weight:800;color:var(--text);margin-bottom:2px;">STRUK DIGITAL</h3>
      <span style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;">SParking UTN</span>
    </div>
    
    <div style="border-top:1.5px dashed var(--border); border-bottom:1.5px dashed var(--border); padding:16px 0; margin-bottom:20px; display:flex; flex-direction:column; gap:10px; font-size:13px; color:var(--text);">
      <div style="display:flex; justify-content:space-between;">
        <span style="color:var(--text-muted);">No. Struk</span>
        <strong id="rec-no" style="font-family:monospace; color:var(--accent);"></strong>
      </div>
      <div style="display:flex; justify-content:space-between;">
        <span style="color:var(--text-muted);">Tanggal</span>
        <span id="rec-tgl"></span>
      </div>
      <div style="display:flex; justify-content:space-between;">
        <span style="color:var(--text-muted);">Plat Nomor</span>
        <strong id="rec-plat"></strong>
      </div>
      <div style="display:flex; justify-content:space-between;">
        <span style="color:var(--text-muted);">Kendaraan</span>
        <span id="rec-jenis"></span>
      </div>
      <div style="display:flex; justify-content:space-between;">
        <span style="color:var(--text-muted);">Lokasi</span>
        <span id="rec-lokasi"></span>
      </div>
      <div style="display:flex; justify-content:space-between;">
        <span style="color:var(--text-muted);">Durasi</span>
        <span id="rec-durasi"></span>
      </div>
      <div style="display:flex; justify-content:space-between;">
        <span style="color:var(--text-muted);">Metode Bayar</span>
        <span id="rec-metode" class="badge" style="padding: 2px 8px; font-size:10px; font-weight:700;"></span>
      </div>
      <div style="border-top:1px solid var(--border); padding-top:10px; margin-top:4px; display:flex; justify-content:space-between; align-items:center;">
        <span style="font-weight:700;">Total Bayar</span>
        <strong id="rec-total" style="font-size:18px; color:var(--success);"></strong>
      </div>
    </div>
    
    <p style="font-size:11px;color:var(--text-muted);text-align:center;line-height:1.5;margin-bottom:20px;">Terima kasih telah berkendara dengan aman.<br>Simpan struk ini sebagai bukti pembayaran digital.</p>
    <button class="pk-btn-outline" style="margin-top:0;" onclick="document.getElementById('receiptModal').classList.remove('open')">Tutup</button>
 </div>
</div>

<script>
function updateClock() {
 document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID');
}
updateClock(); setInterval(updateClock, 1000);

function showReceipt(data) {
  document.getElementById('rec-no').textContent = data.no_struk;
  document.getElementById('rec-tgl').textContent = data.tgl_struk;
  document.getElementById('rec-plat').textContent = data.plat;
  document.getElementById('rec-jenis').textContent = data.jenis;
  document.getElementById('rec-lokasi').textContent = data.lokasi;
  document.getElementById('rec-durasi').textContent = data.durasi;
  
  const metodeEl = document.getElementById('rec-metode');
  metodeEl.textContent = data.metode;
  metodeEl.className = 'badge ' + (data.metode.toLowerCase() === 'tunai' ? 'badge-yellow' : 'badge-blue');
  
  document.getElementById('rec-total').textContent = data.biaya;
  
  document.getElementById('receiptModal').classList.add('open');
}
</script>
</body>
</html>
