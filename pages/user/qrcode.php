<?php
require_once '../../includes/config.php';
requireRole('pengguna');
$page_title = 'QR Code Saya';

$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT q.*, t.waktu_masuk, t.status_transaksi, k.plat_nomor, s.lokasi
 FROM qr_code_parkir q
 JOIN transaksi_parkir t ON q.id_transaksi = t.id_transaksi
 JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan
 JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi
 WHERE k.id_pengguna = ?
 ORDER BY q.waktu_generate DESC LIMIT 10");
$stmt->bind_param("i", $id);
$stmt->execute();
$qr_list = $stmt->get_result();

require_once '../../includes/header_user.php';
require_once '../../includes/sidebar_user.php';
?>
<div class="main">
 <div class="topbar"><div class="page-title">QR Code Parkir Saya</div></div>
 <div class="content">
  <div class="table-card">
  <div class="table-header"><h3>Daftar QR Code</h3></div>
  <table>
  <thead>
  <tr><th>Kode QR</th><th>Plat Nomor</th><th>Lokasi</th><th>Jenis QR</th><th>Waktu Generate</th><th>Status</th><th>Aksi</th></tr>
  </thead>
  <tbody>
  <?php while ($row = $qr_list->fetch_assoc()): ?>
  <tr>
  <td><code style="font-size:11px;color:var(--accent);"><?= htmlspecialchars(substr($row['kode_qr'],0,20)).'...' ?></code></td>
  <td><?= htmlspecialchars($row['plat_nomor']) ?></td>
  <td><?= htmlspecialchars($row['lokasi']) ?></td>
  <td><span class="badge <?= $row['jenis_qr']==='masuk' ? 'badge-blue' : 'badge-green' ?>"><?= ucfirst($row['jenis_qr']) ?></span></td>
  <td><?= date('d/m/Y H:i', strtotime($row['waktu_generate'])) ?></td>
  <td>
  <?php if ($row['status_qr']==='aktif'): ?>
  <span class="badge badge-green">Aktif</span>
  <?php elseif ($row['status_qr']==='sudah_digunakan'): ?>
  <span class="badge badge-gray">Digunakan</span>
  <?php else: ?>
  <span class="badge badge-red">Kadaluarsa</span>
  <?php endif; ?>
  </td>
  <td>
  <?php if ($row['status_qr']==='aktif'): ?>
  <button class="btn btn-primary btn-sm" onclick="showQR('<?= htmlspecialchars($row['kode_qr']) ?>', '<?= htmlspecialchars($row['plat_nomor']) ?>', '<?= htmlspecialchars($row['lokasi']) ?>')">Tampilkan</button>
  <?php endif; ?>
  </td>
  </tr>
  <?php endwhile; ?>
  </tbody>
  </table>
  </div>
  </div>
</div>

<!-- QR Modal -->
<div class="pk-modal-overlay" id="qrModal" onclick="if(event.target===this)this.classList.remove('open')">
 <div class="pk-modal" style="position:relative; max-width:380px;">
   <span class="pk-modal-pill"></span>
   <h3 style="font-family:'Syne',sans-serif;margin-bottom:6px;font-size:20px;font-weight:800;color:var(--text);" id="qr-title">QR Code Parkir</h3>
   <p style="font-size:13.5px;color:var(--text-muted);margin-bottom:20px;" id="qr-sub"></p>
   
   <div class="qr-box" style="margin:0 auto 20px; background:#ffffff; border-radius:16px; padding:16px; box-shadow:0 10px 25px rgba(0,0,0,0.15); width:fit-content; display:flex; justify-content:center; align-items:center; position:relative; overflow:hidden;">
     <div class="qr-scan-line-overlay"></div>
     <img id="qr-img" src="" alt="QR Code" style="width:180px;height:180px;display:block;">
   </div>
   
   <p style="font-size:12px;color:var(--text-muted);margin-bottom:20px;line-height:1.5;">Tunjukkan QR Code ini kepada petugas saat keluar dari area parkir.</p>
   <button class="pk-btn-outline" style="margin-top:0;" onclick="document.getElementById('qrModal').classList.remove('open')">Tutup</button>
 </div>
</div>

<script>
function showQR(kode, plat, lokasi) {
  document.getElementById('qr-title').textContent = 'QR Code — ' + plat;
  document.getElementById('qr-sub').textContent = 'Lokasi: ' + lokasi;
  document.getElementById('qr-img').src = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(kode);
  document.getElementById('qrModal').classList.add('open');
}
</script>
</body></html>
