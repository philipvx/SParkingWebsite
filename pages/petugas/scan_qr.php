<?php
require_once '../../includes/config.php';
requireRole('petugas_keluar');
$page_title = 'Scan QR & Pembayaran';

$msg = ''; $err = '';
$transaksi = null;

// Handle QR lookup
$kode_qr = trim($_GET['qr'] ?? $_POST['kode_qr'] ?? '');

if ($kode_qr) {
  $transaksi = db_fetch_row("
    SELECT q.id_qr, q.kode_qr, t.*, k.plat_nomor, k.jenis_kendaraan, k.merk, k.warna, k.id_pengguna, s.lokasi 
    FROM qr_code_parkir q
    JOIN transaksi_parkir t ON q.id_transaksi = t.id_transaksi
    JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan
    JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi
    WHERE q.kode_qr = ? AND q.status_qr = 'aktif' AND q.jenis_qr = 'keluar'
    LIMIT 1
  ", [$kode_qr]);
  
  if (!$transaksi) {
    $err = 'QR Code tidak valid, sudah digunakan, atau bukan QR keluar.';
  } else {
    $fee_details = calculate_fee($transaksi['jenis_kendaraan'], $transaksi['waktu_masuk']);
    $transaksi['_durasi_menit'] = $fee_details['durasi_menit'];
    $transaksi['_jam'] = $fee_details['durasi_jam'];
    $transaksi['_biaya'] = $fee_details['biaya'];
    $transaksi['tarif_awal'] = $fee_details['tarif_awal'];
    $transaksi['tarif_per_jam'] = $fee_details['tarif_per_jam'];
  }
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_bayar'])) {
  $id_transaksi = intval($_POST['id_transaksi']);
  $metode = $_POST['metode'];
  $jumlah = floatval($_POST['jumlah_bayar']);
  $tid = $_SESSION['user_id'];
  $id_ewallet = isset($_POST['id_ewallet']) ? intval($_POST['id_ewallet']) : null;

  try {
    $nomor_struk = pay_and_checkout($id_transaksi, $metode, $jumlah, $id_ewallet, $tid);
    $msg = "Pembayaran berhasil! Kendaraan boleh keluar. Struk: $nomor_struk";
    $transaksi = null; 
    $kode_qr = '';
  } catch (Exception $e) {
    $err = 'Gagal: ' . $e->getMessage();
  }
}

require_once '../../includes/header_staff.php';
require_once '../../includes/sidebar_petugas.php';
?>
<style>
.scanner-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 28px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  box-shadow: var(--shadow);
  position: relative;
  overflow: hidden;
  width: 100%;
}
body.light-mode .scanner-card {
  background: #ffffff;
  border-color: rgba(99, 102, 241, 0.15);
  box-shadow: 0 10px 30px rgba(99, 102, 241, 0.05);
}
.scanner-title {
  font-family: 'Syne', sans-serif;
  font-size: 16px;
  font-weight: 700;
  text-align: center;
  color: var(--text);
  margin-bottom: 4px;
}
body.light-mode .scanner-title {
  color: #1f2937;
}
.scanner-viewport {
  width: 100%;
  max-width: 380px;
  aspect-ratio: 4 / 3;
  background: #090d16;
  border-radius: 16px;
  position: relative;
  overflow: hidden;
  border: 2px solid rgba(255, 255, 255, 0.08);
  box-shadow: inset 0 0 40px rgba(0, 0, 0, 0.8), 0 8px 24px rgba(0,0,0,0.25);
}
.scanner-corner {
  position: absolute;
  width: 20px;
  height: 20px;
  border: 3px solid transparent;
  pointer-events: none;
  z-index: 3;
}
.scanner-corner.top-left {
  top: 16px; left: 16px;
  border-top-color: #10b981; border-left-color: #10b981;
}
.scanner-corner.top-right {
  top: 16px; right: 16px;
  border-top-color: #10b981; border-right-color: #10b981;
}
.scanner-corner.bottom-left {
  bottom: 16px; left: 16px;
  border-bottom-color: #10b981; border-left-color: #10b981;
}
.scanner-corner.bottom-right {
  bottom: 16px; right: 16px;
  border-bottom-color: #10b981; border-right-color: #10b981;
}
.scanner-laser {
  position: absolute;
  left: 16px;
  right: 16px;
  height: 3px;
  background: linear-gradient(90deg, transparent, #10b981, transparent);
  box-shadow: 0 0 12px #10b981, 0 0 4px #10b981;
  animation: scan-anim 2.5s infinite ease-in-out;
  z-index: 2;
}
@keyframes scan-anim {
  0% { top: 16px; opacity: 0; }
  10% { opacity: 1; }
  90% { opacity: 1; }
  100% { top: calc(100% - 20px); opacity: 0; }
}
.scanner-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #fff;
  text-align: center;
  padding: 20px;
  background: radial-gradient(circle, transparent 35%, rgba(0,0,0,0.5) 100%);
  z-index: 1;
}
.scanner-grid-pattern {
  position: absolute;
  inset: 0;
  background-image: linear-gradient(rgba(16, 185, 129, 0.04) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(16, 185, 129, 0.04) 1px, transparent 1px);
  background-size: 16px 16px;
  pointer-events: none;
}
.scanner-icon {
  font-size: 42px;
  color: rgba(16, 185, 129, 0.35);
  animation: pulse-icon 2s infinite ease-in-out;
}
@keyframes pulse-icon {
  0%, 100% { transform: scale(1); opacity: 0.35; }
  50% { transform: scale(1.06); opacity: 0.75; color: rgba(16, 185, 129, 0.65); }
}
.scanner-status {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.8px;
  color: #10b981;
  text-transform: uppercase;
}
.scanner-hint {
  font-size: 11px;
  color: #94a3b8;
  max-width: 240px;
}
.scanner-input-group {
  width: 100%;
  max-width: 380px;
  margin-top: 8px;
}
</style>
<div class="main">
 <div class="topbar">
 <div class="page-title"> Scan QR & Proses Keluar</div>
 <div class="topbar-right">
 <span class="topbar-time" id="clock"></span>
 </div>
 </div>
 <div class="content">

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error"> <?= htmlspecialchars($err) ?></div><?php endif; ?>

  <?php if (!$transaksi): ?>
  <!-- CAMERA SCANNER VIEWPORT ON EMPTY STATE (Centered) -->
  <div style="max-width: 480px; margin: 20px auto 0;">
    <div class="scanner-card">
      <div class="scanner-title">Kamera Scanner Tiket Keluar</div>
      <div class="scanner-viewport">
        <div class="scanner-grid-pattern"></div>
        <div class="scanner-corner top-left"></div>
        <div class="scanner-corner top-right"></div>
        <div class="scanner-corner bottom-left"></div>
        <div class="scanner-corner bottom-right"></div>
        <div class="scanner-laser"></div>
        <div class="scanner-overlay">
          <div class="scanner-icon">🎦</div>
          <div class="scanner-status">Mencari Kode QR...</div>
          <div class="scanner-hint">Posisikan tiket dengan QR Code menghadap langsung ke arah kamera.</div>
        </div>
      </div>
      
      <div class="scanner-input-group">
        <form method="GET" style="display:flex;gap:10px;width:100%;">
          <input type="text" name="qr" class="form-control" placeholder="Scan atau input kode QR..." value="<?= htmlspecialchars($kode_qr) ?>" style="flex:1;text-align:center;font-family:monospace;letter-spacing:1px;background:var(--surface2);" autofocus>
          <button type="submit" class="btn btn-primary">Proses</button>
        </form>
      </div>
    </div>
  </div>
  <?php else: ?>
  <!-- CAMERA SCANNER + DATA DETAILS (2 Columns) -->
  <div style="display:grid;grid-template-columns:1fr 1.2fr;gap:20px;align-items:start;">
    <!-- Column 1: Scanner Viewport with success state -->
    <div class="scanner-card">
      <div class="scanner-title">Status Scanner</div>
      <div class="scanner-viewport" style="border-color: rgba(16, 185, 129, 0.3);">
        <div class="scanner-grid-pattern"></div>
        <div class="scanner-corner top-left"></div>
        <div class="scanner-corner top-right"></div>
        <div class="scanner-corner bottom-left"></div>
        <div class="scanner-corner bottom-right"></div>
        <div class="scanner-overlay" style="background: radial-gradient(circle, transparent 35%, rgba(16, 185, 129, 0.15) 100%);">
          <div class="scanner-icon" style="color: #10b981; animation: none;">✓</div>
          <div class="scanner-status" style="color: #10b981;">Scan Sukses!</div>
          <div class="scanner-hint" style="color: #a7f3d0; font-weight: 600; font-family: monospace; font-size: 12px;"><?= htmlspecialchars($kode_qr) ?></div>
        </div>
      </div>
      
      <div style="width: 100%;">
        <a href="scan_qr.php" class="btn btn-outline" style="width: 100%; justify-content: center; border-radius: 12px;">Scan Tiket Lain</a>
      </div>
    </div>

    <!-- Column 2: Data Kendaraan & Rincian Biaya -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
      <div class="form-card">
        <h3 style="font-family:'Syne',sans-serif;font-size:15px;margin-bottom:16px;">Data Kendaraan</h3>
        <table style="width:100%;">
          <tr><td style="color:var(--text-muted);font-size:12px;padding:6px 0;">Plat Nomor</td><td style="font-weight:700;font-size:16px;color:var(--accent);"><?= htmlspecialchars($transaksi['plat_nomor']) ?></td></tr>
          <tr><td style="color:var(--text-muted);font-size:12px;padding:6px 0;">Jenis</td><td><?= ucfirst($transaksi['jenis_kendaraan']) ?></td></tr>
          <tr><td style="color:var(--text-muted);font-size:12px;padding:6px 0;">Merk/Warna</td><td><?= htmlspecialchars($transaksi['merk'].' '.$transaksi['warna']) ?></td></tr>
          <tr><td style="color:var(--text-muted);font-size:12px;padding:6px 0;">Lokasi</td><td><?= htmlspecialchars($transaksi['lokasi']) ?></td></tr>
          <tr><td style="color:var(--text-muted);font-size:12px;padding:6px 0;">Waktu Masuk</td><td><?= date('d/m/Y H:i', strtotime($transaksi['waktu_masuk'])) ?></td></tr>
          <tr><td style="color:var(--text-muted);font-size:12px;padding:6px 0;">Durasi</td><td><strong><?= $transaksi['_jam'] ?> jam <?= $transaksi['_durasi_menit'] % 60 ?> menit</strong></td></tr>
        </table>
      </div>

      <div class="form-card" style="background:linear-gradient(135deg,rgba(249,115,22,0.08),var(--surface));">
        <h3 style="font-family:'Syne',sans-serif;font-size:15px;margin-bottom:6px;">Rincian Biaya</h3>
        <div style="background:var(--surface2);border-radius:10px;padding:14px;margin-bottom:16px;">
          <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);margin-bottom:6px;"><span>Tarif Awal</span><span><?= formatRupiah($transaksi['tarif_awal']) ?></span></div>
          <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);margin-bottom:8px;"><span>Tambahan (<?= max(0,$transaksi['_jam']-1) ?> jam)</span><span><?= formatRupiah(max(0,$transaksi['_jam']-1)*$transaksi['tarif_per_jam']) ?></span></div>
          <div style="display:flex;justify-content:space-between;font-size:17px;font-weight:700;color:var(--success);border-top:1px solid var(--border);padding-top:8px;"><span>Total</span><span><?= formatRupiah($transaksi['_biaya']) ?></span></div>
        </div>

        <form method="POST">
          <input type="hidden" name="id_transaksi" value="<?= $transaksi['id_transaksi'] ?>">
          <input type="hidden" name="id_qr" value="<?= $transaksi['id_qr'] ?>">
          <input type="hidden" name="kode_qr" value="<?= htmlspecialchars($kode_qr) ?>">
          <input type="hidden" name="durasi_menit" value="<?= $transaksi['_durasi_menit'] ?>">
          <input type="hidden" name="jumlah_bayar" value="<?= $transaksi['_biaya'] ?>">
          <input type="hidden" name="id_pengguna" value="<?= $transaksi['id_pengguna'] ?>">

          <?php
          $ews = get_user_ewallets($transaksi['id_pengguna'], $transaksi['_biaya']);
          $has_sufficient_balance = false;
          foreach ($ews as $ew) {
            if ($ew['is_sufficient']) {
              $has_sufficient_balance = true;
            }
          }
          ?>

          <div class="form-group">
            <label class="form-label">Metode Pembayaran</label>
            <select name="metode" class="form-control" id="metodeSelect" onchange="toggleEwallet(this.value)">
              <option value="tunai"> Tunai</option>
              <option value="e_wallet" <?php if (empty($ews) || !$has_sufficient_balance) echo 'disabled'; ?>> 
                E-Wallet <?php 
                  if (empty($ews)) {
                    echo '(Belum terhubung)';
                  } elseif (!$has_sufficient_balance) {
                    echo '(Saldo tidak cukup)';
                  } 
                ?>
              </option>
            </select>
          </div>

          <div id="ewalletDiv" style="display:none;" class="form-group">
            <label class="form-label">Pilih E-Wallet</label>
            <select name="id_ewallet" class="form-control">
              <?php foreach ($ews as $ew): ?>
                <option value="<?= $ew['id_ewallet'] ?>" <?php if (!$ew['is_sufficient']) echo 'disabled'; ?>>
                  <?= htmlspecialchars($ew['provider']) ?> — <?= htmlspecialchars($ew['nomor_akun']) ?> (<?= formatRupiah($ew['saldo']) ?>)
                  <?php if (!$ew['is_sufficient']) echo ' — Saldo Kurang'; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit" name="proses_bayar" class="btn btn-success" style="width:100%;justify-content:center;"> Konfirmasi Pembayaran <?= formatRupiah($transaksi['_biaya']) ?></button>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>

 </div>
</div>
<script>
function toggleEwallet(v) {
 document.getElementById('ewalletDiv').style.display = v === 'e_wallet' ? 'block' : 'none';
}
function updateClock() {
 document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID');
}
updateClock(); setInterval(updateClock, 1000);
</script>
</body></html>


