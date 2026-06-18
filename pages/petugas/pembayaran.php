<?php
require_once '../../includes/config.php';
requireRole('petugas_keluar');
$page_title = 'Proses Pembayaran';

$msg = '';
$err = '';
$search = trim($_GET['search'] ?? '');
$selected = null;
$tid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_bayar'])) {
  $id_transaksi = intval($_POST['id_transaksi']);
  $jumlah = floatval($_POST['jumlah_bayar']);
  $metode = $_POST['metode'] ?? 'tunai';
  $id_ewallet = isset($_POST['id_ewallet']) ? intval($_POST['id_ewallet']) : null;

  try {
    $nomor_struk = pay_and_checkout($id_transaksi, $metode, $jumlah, $id_ewallet, $tid);
    $msg = 'Pembayaran berhasil. Kendaraan boleh keluar. Struk: ' . $nomor_struk;
  } catch (Exception $e) {
    $err = 'Gagal: ' . $e->getMessage();
  }
}

$where = "t.status_transaksi = 'parkir'";
$params = [];
if ($search !== '') {
  $where .= " AND (k.plat_nomor LIKE ? OR s.lokasi LIKE ?)";
  $like = '%' . $search . '%';
  $params = [$like, $like];
}

$active_list = db_fetch_all("
  SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.merk, k.warna, s.lokasi 
  FROM transaksi_parkir t 
  JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
  JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi 
  WHERE $where 
  ORDER BY t.waktu_masuk DESC
", $params);

if (isset($_GET['id'])) {
  $id_transaksi = intval($_GET['id']);
  $selected = db_fetch_row("
    SELECT t.*, k.id_pengguna, k.plat_nomor, k.jenis_kendaraan, k.merk, k.warna, s.lokasi 
    FROM transaksi_parkir t 
    JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
    JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi 
    WHERE t.id_transaksi = ? AND t.status_transaksi = 'parkir' 
    LIMIT 1
  ", [$id_transaksi]);
  
  if ($selected) {
    $fee_details = calculate_fee($selected['jenis_kendaraan'], $selected['waktu_masuk']);
    $selected['_durasi_menit'] = $fee_details['durasi_menit'];
    $selected['_jam'] = $fee_details['durasi_jam'];
    $selected['_biaya'] = $fee_details['biaya'];

    $ews = get_user_ewallets($selected['id_pengguna'], $selected['_biaya']);
    $has_sufficient_balance = false;
    foreach ($ews as $ew) {
      if ($ew['is_sufficient']) {
        $has_sufficient_balance = true;
      }
    }
  }
}

require_once '../../includes/header_staff.php';
require_once '../../includes/sidebar_petugas.php';
?>
<div class="main">
  <div class="topbar">
  <div class="page-title">Proses Pembayaran</div>
  <div class="topbar-right">
  <span class="topbar-time" id="clock"></span>
  </div>
  </div>
  <div class="content">
  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="form-card" style="margin-bottom:24px;">
  <h3 style="font-family:'Syne',sans-serif;margin-bottom:16px;">Cari Transaksi Aktif</h3>
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
  <input type="text" name="search" class="form-control" placeholder="Plat atau Slot" value="<?= htmlspecialchars($search) ?>" style="flex:1;min-width:200px;">
  <button type="submit" class="btn btn-primary">Cari</button>
  </form>
  </div>

  <?php if ($selected): ?>
  <div class="form-card" style="background:linear-gradient(135deg,rgba(249,115,22,0.08),var(--surface));margin-bottom:24px;">
  <h3 style="font-family:'Syne',sans-serif;margin-bottom:16px;">Bayar Kendaraan</h3>
  <table style="width:100%;margin-bottom:16px;">
  <tr><td style="color:var(--text-muted);font-size:12px;padding:6px 0;">Plat Nomor</td><td style="font-weight:700;"><?= htmlspecialchars($selected['plat_nomor']) ?></td></tr>
  <tr><td style="color:var(--text-muted);font-size:12px;padding:6px 0;">Jenis</td><td><?= ucfirst($selected['jenis_kendaraan']) ?></td></tr>
  <tr><td style="color:var(--text-muted);font-size:12px;padding:6px 0;">Lokasi</td><td><?= htmlspecialchars($selected['lokasi']) ?></td></tr>
  <tr><td style="color:var(--text-muted);font-size:12px;padding:6px 0;">Waktu Masuk</td><td><?= date('d/m/Y H:i', strtotime($selected['waktu_masuk'])) ?></td></tr>
  <tr><td style="color:var(--text-muted);font-size:12px;padding:6px 0;">Durasi</td><td><?= $selected['_jam'] ?> jam <?= $selected['_durasi_menit'] % 60 ?> menit</td></tr>
  <tr><td style="color:var(--text-muted);font-size:12px;padding:6px 0;">Total</td><td style="font-weight:700;color:var(--success);"><?= formatRupiah($selected['_biaya']) ?></td></tr>
  </table>
  <form method="POST">
  <input type="hidden" name="proses_bayar" value="1">
  <input type="hidden" name="id_transaksi" value="<?= intval($selected['id_transaksi']) ?>">
  <input type="hidden" name="durasi_menit" value="<?= intval($selected['_durasi_menit']) ?>">
  <input type="hidden" name="jumlah_bayar" value="<?= floatval($selected['_biaya']) ?>">
  <input type="hidden" name="id_pengguna" value="<?= intval($selected['id_pengguna']) ?>">

  <div class="form-group" style="margin-bottom: 12px;">
    <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 600;">Metode Pembayaran</label>
    <select name="metode" class="form-control" id="metodeSelect" onchange="toggleEwallet(this.value)">
      <option value="tunai">Tunai</option>
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

  <div id="ewalletDiv" style="display: none; margin-bottom: 16px;" class="form-group">
    <label class="form-label" style="display: block; margin-bottom: 6px; font-weight: 600;">Pilih E-Wallet</label>
    <select name="id_ewallet" class="form-control">
      <?php foreach ($ews as $ew): ?>
        <option value="<?= $ew['id_ewallet'] ?>" <?php if (!$ew['is_sufficient']) echo 'disabled'; ?>>
          <?= htmlspecialchars($ew['provider']) ?> — <?= htmlspecialchars($ew['nomor_akun']) ?> (<?= formatRupiah($ew['saldo']) ?>)
          <?php if (!$ew['is_sufficient']) echo ' — Saldo Kurang'; ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <button type="submit" class="btn btn-success" style="width:100%;">Konfirmasi Pembayaran</button>
  </form>
  </div>
  <?php endif; ?>

 <div class="table-card">
 <div class="table-header">
 <h3>Transaksi Aktif</h3>
 </div>
 <table>
 <thead>
 <tr><th>Plat Nomor</th><th>Jenis</th><th>Lokasi</th><th>Waktu Masuk</th><th>Aksi</th></tr>
 </thead>
 <tbody>
  <?php if (count($active_list) === 0): ?>
  <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:30px;">Belum ada transaksi parkir aktif.</td></tr>
  <?php else: ?>
  <?php foreach ($active_list as $row): ?>
 <tr>
 <td><strong><?= htmlspecialchars($row['plat_nomor']) ?></strong></td>
 <td><?= htmlspecialchars(ucfirst($row['jenis_kendaraan'])) ?></td>
 <td><?= htmlspecialchars($row['lokasi']) ?></td>
 <td><?= date('d/m/Y H:i', strtotime($row['waktu_masuk'])) ?></td>
 <td><a href="pembayaran.php?id=<?= intval($row['id_transaksi']) ?>" class="btn btn-outline btn-sm">Bayar</a></td>
 </tr>
  <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
 </div>
</div>
<script>
function toggleEwallet(v) {
  const div = document.getElementById('ewalletDiv');
  if (div) div.style.display = v === 'e_wallet' ? 'block' : 'none';
}
function updateClock() {
 document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID');
}
updateClock(); setInterval(updateClock, 1000);
</script>
</body>
</html>



