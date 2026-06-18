<?php
require_once '../../includes/config.php';
requireRole('pengguna');
$page_title = 'Area Parkir';

$msg = '';
$err = '';
$qr_info = null;
$user_id = $_SESSION['user_id'];

$stmtActive = $conn->prepare("SELECT t.*, s.lokasi FROM transaksi_parkir t JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan WHERE k.id_pengguna = ? AND t.status_transaksi = 'parkir' LIMIT 1");
function generateRandomPlate() {
 $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
 $prefixes = ['B','D','F','H','K','L','M','N','T','Z'];
 $prefix = $prefixes[array_rand($prefixes)];
 $numbers = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
 $suffix = substr(str_shuffle($letters), 0, 2);
 return "$prefix $numbers $suffix";
}
function generateUniquePlate($conn) {
 do {
 $plat = generateRandomPlate();
 $exists = $conn->query("SELECT id_kendaraan FROM kendaraan WHERE plat_nomor='" . $conn->real_escape_string($plat) . "' LIMIT 1")->fetch_assoc();
 } while ($exists);
 return $plat;
}

$stmtActive->bind_param("i", $user_id);
$stmtActive->execute();
$active_parking = $stmtActive->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_slot'])) {
 $lokasi_pilihan = trim($_POST['lokasi'] ?? '');
 $kode_qr = trim($_POST['kode_qr'] ?? '');
 $jenis_kendaraan = trim($_POST['jenis_kendaraan'] ?? '');
 if ($lokasi_pilihan === '' || $kode_qr === '' || $jenis_kendaraan === '') {
 $err = 'Data area atau jenis kendaraan tidak valid.';
 } elseif ($active_parking) {
 $err = 'Anda masih memiliki kendaraan yang sedang parkir di area ' . htmlspecialchars($active_parking['lokasi']) . '. Selesaikan dulu sebelum memilih area baru.';
 } else {
 $slotStmt = $conn->prepare("SELECT * FROM lokasi_parkir WHERE lokasi = ? AND jenis_kendaraan = ? AND status_lokasi = 'tersedia' LIMIT 1");
 $slotStmt->bind_param("ss", $lokasi_pilihan, $jenis_kendaraan);
 $slotStmt->execute();
 $slot = $slotStmt->get_result()->fetch_assoc();
 if (!$slot) {
 $err = 'Tidak ada tempat tersedia di area tersebut.';
 } else {
 $id_lokasi = intval($slot['id_lokasi']);
 
 // Check active parked count
 $activeCountResult = $conn->query("SELECT COUNT(*) as c FROM transaksi_parkir WHERE id_lokasi = $id_lokasi AND status_transaksi = 'parkir'")->fetch_assoc();
 $activeCount = $activeCountResult ? intval($activeCountResult['c']) : 0;
 
 if ($activeCount >= intval($slot['kapasitas'])) {
 $err = 'Area parkir ini sudah penuh.';
 } else {
 $random_plate = generateUniquePlate($conn);
 $waktu = date('Y-m-d H:i:s');
 $conn->begin_transaction();
 try {
 $merk = 'Random'; $warna = 'Random';
 $stmtVeh = $conn->prepare("INSERT INTO kendaraan (plat_nomor, jenis_kendaraan, merk, warna, id_pengguna) VALUES (?,?,?,?,?)");
 $stmtVeh->bind_param("ssssi", $random_plate, $jenis_kendaraan, $merk, $warna, $user_id);
 $stmtVeh->execute();
 $id_kendaraan = $conn->insert_id;
 $stmt = $conn->prepare("INSERT INTO transaksi_parkir (id_kendaraan, id_lokasi, waktu_masuk, status_transaksi) VALUES (?,?,?, 'parkir')");
 $stmt->bind_param("iis", $id_kendaraan, $id_lokasi, $waktu);
 $stmt->execute();
 $id_transaksi = $conn->insert_id;
 
 $stmtQr = $conn->prepare("INSERT INTO qr_code_parkir (kode_qr, jenis_qr, status_qr, waktu_generate, id_transaksi) VALUES (?,?,?,?,?)");
 $jenis_qr = 'keluar'; $status_qr = 'aktif';
 $stmtQr->bind_param("ssssi", $kode_qr, $jenis_qr, $status_qr, $waktu, $id_transaksi);
 $stmtQr->execute();
 $conn->commit();
 $msg = 'Area parkir berhasil dipesan. Tunjukkan QR keluar ini ke petugas ketika pulang.';
 $qr_info = ['kode_qr' => $kode_qr, 'lokasi' => $slot['lokasi'], 'plat' => $random_plate, 'jenis' => $jenis_kendaraan];
 $active_parking = ['lokasi' => $slot['lokasi']];
 } catch (Exception $e) {
 $conn->rollback();
 $err = 'Terjadi kesalahan: ' . $e->getMessage();
 }
 }
 }
 }
}

$slots = $conn->query("SELECT * FROM lokasi_parkir ORDER BY lokasi, jenis_kendaraan");
$slot_data = [];
$total_capacity = 0;
$total_maintenance = 0;

while ($row = $slots->fetch_assoc()) {
    $id_lok = intval($row['id_lokasi']);
    $activeCountResult = $conn->query("SELECT COUNT(*) as c FROM transaksi_parkir WHERE id_lokasi = $id_lok AND status_transaksi = 'parkir'")->fetch_assoc();
    $activeCount = $activeCountResult ? intval($activeCountResult['c']) : 0;
    
    $row['tersedia'] = $row['status_lokasi'] === 'maintenance' ? 0 : max(0, intval($row['kapasitas']) - $activeCount);
    $row['terisi'] = $row['status_lokasi'] === 'maintenance' ? 0 : $activeCount;
    $row['maintenance'] = $row['status_lokasi'] === 'maintenance' ? intval($row['kapasitas']) : 0;

    $total_capacity += intval($row['kapasitas']);
    $total_maintenance += $row['maintenance'];
    
    $slot_data[$row['lokasi']][] = $row;
}

$total_terisi = $conn->query("SELECT COUNT(*) as c FROM transaksi_parkir WHERE status_transaksi='parkir'")->fetch_assoc()['c'];
$total_tersedia = max(0, $total_capacity - $total_terisi - $total_maintenance);

require_once '../../includes/header_user.php';
require_once '../../includes/sidebar_user.php';
?>

<style>
/* ── Palette ── */
:root {
 --p-bg: transparent;
 --p-surface: rgba(255,255,255,0.08);
 --p-card: rgba(255,255,255,0.1);
 --p-purple: #6366f1;
 --p-purple2: #8b5cf6;
 --p-green: #3CC98A;
 --p-red: #F06B6B;
 --p-gray: #A0A3B1;
 --p-text: #f8fafc;
 --p-muted: #94a3b8;
 --p-border: rgba(255,255,255,.12);
 --p-shadow: 0 18px 40px rgba(0,0,0,.22);
 --p-radius: 20px;
}

/* ── Reset / base ── */
.pk-wrap * { box-sizing: border-box; margin: 0; padding: 0; }
.pk-wrap {
 font-family: 'DM Sans', 'Segoe UI', sans-serif;
 background: var(--p-bg);
 min-height: 100vh;
 padding: 24px 20px 48px;
 color: var(--p-text);
}

/* ── Top heading ── */
.pk-heading {
 font-size: 22px;
 font-weight: 700;
 letter-spacing: -.3px;
 margin-bottom: 6px;
}
.pk-subheading { font-size: 13px; color: var(--p-muted); margin-bottom: 20px; }

/* ── Alert ── */
.pk-alert {
 border-radius: 14px;
 padding: 14px 18px;
 font-size: 13.5px;
 margin-bottom: 16px;
 display: flex;
 align-items: flex-start;
 gap: 10px;
}
.pk-alert-success { background: #D4F5E8; color: #1A6644; }
.pk-alert-error { background: #FDE8E8; color: #8B1A1A; }
.pk-alert-info { background: #E8E2F8; color: var(--p-purple); }

/* ── Vehicle type picker ── */
.pk-type-grid {
 display: grid;
 grid-template-columns: 1fr 1fr;
 gap: 14px;
 margin-bottom: 20px;
}
.pk-type-btn {
 background: var(--p-surface);
 backdrop-filter: blur(12px);
 border: 2px solid transparent;
 border-radius: var(--p-radius);
 padding: 28px 16px;
 display: flex;
 flex-direction: column;
 align-items: center;
 gap: 12px;
 cursor: pointer;
 transition: all .22s ease;
 box-shadow: var(--p-shadow);
}
.pk-type-btn:hover { border-color: var(--p-purple); transform: translateY(-2px); }
.pk-type-icon { font-size: 44px; line-height: 1; }
.pk-type-label { font-size: 17px; font-weight: 700; }
.pk-type-desc { font-size: 12px; color: var(--p-muted); text-align: center; line-height: 1.4; }

/* ── Selected type bar ── */
.pk-selected-bar {
 background: var(--p-surface);
 border-radius: var(--p-radius);
 padding: 18px 20px;
 display: flex;
 align-items: center;
 justify-content: space-between;
 margin-bottom: 18px;
 box-shadow: var(--p-shadow);
}
.pk-selected-bar small { font-size: 12px; color: var(--p-muted); }
.pk-selected-bar strong { font-size: 20px; font-weight: 700; display: block; margin-top: 2px; }
.pk-ganti-btn {
 background: var(--p-card);
 border: 1px solid var(--p-border);
 border-radius: 30px;
 padding: 9px 20px;
 font-size: 13px;
 font-weight: 600;
 cursor: pointer;
 color: var(--p-text);
 transition: background .18s;
}
.pk-ganti-btn:hover { background: var(--p-purple); color: #fff; }

/* ── Stat cards ── */
.pk-stat-grid {
 display: grid;
 grid-template-columns: repeat(3, 1fr);
 gap: 12px;
 margin-bottom: 22px;
}
.pk-stat-card {
 background: var(--p-surface);
 backdrop-filter: blur(12px);
 border-radius: var(--p-radius);
 padding: 18px 14px;
 display: flex;
 flex-direction: column;
 align-items: flex-start;
 gap: 6px;
 box-shadow: var(--p-shadow);
}
.pk-stat-dot {
 width: 36px; height: 36px; border-radius: 50%;
 display: flex; align-items: center; justify-content: center;
 font-size: 18px; margin-bottom: 4px;
}
.pk-stat-dot.green { background: rgba(60,201,138,.15); }
.pk-stat-dot.red { background: rgba(240,107,107,.15); }
.pk-stat-dot.gray { background: rgba(160,163,177,.15); }
.pk-stat-label { font-size: 11px; font-weight: 600; letter-spacing: .5px; text-transform: uppercase; color: var(--p-muted); }
.pk-stat-val { font-size: 28px; font-weight: 800; line-height: 1; }
.pk-stat-val.green { color: var(--p-green); }
.pk-stat-val.red { color: var(--p-red); }
.pk-stat-val.gray { color: var(--p-gray); }

/* ── Location card ── */
.pk-location-card {
 background: var(--p-surface);
 backdrop-filter: blur(12px);
 border-radius: var(--p-radius);
 padding: 18px 20px;
 margin-bottom: 14px;
 box-shadow: var(--p-shadow);
 cursor: pointer;
 transition: transform .18s;
}
.pk-location-card:hover { transform: translateY(-2px); }
.pk-location-card.disabled {
 opacity: .65;
 cursor: not-allowed;
}
.pk-location-card.disabled:hover { transform: none; }
.pk-location-header {
 display: flex;
 align-items: flex-start;
 gap: 14px;
}
.pk-location-icon {
 width: 46px; height: 46px; border-radius: 12px;
 background: var(--p-card);
 display: flex; align-items: center; justify-content: center;
 font-size: 20px; flex-shrink: 0;
}
.pk-location-name { font-size: 16px; font-weight: 700; margin-bottom: 3px; }
.pk-location-sub { font-size: 12px; color: var(--p-muted); display: flex; align-items: center; gap: 4px; }
.pk-location-badge {
 display: inline-flex; align-items: center; gap: 6px;
 margin-top: 10px;
 font-size: 12px; font-weight: 600;
 color: var(--p-purple);
}
.pk-avail-bar-wrap { margin-top: 12px; background: #E8E2F8; border-radius: 99px; height: 6px; overflow: hidden; }
.pk-avail-bar { height: 100%; border-radius: 99px; background: var(--p-green); transition: width .4s; }
.pk-avail-status { margin-top: 8px; font-size: 11px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; }
.pk-avail-status.available { color: var(--p-green); }
.pk-avail-status.maintenance { color: var(--p-gray); }
.pk-avail-status.full { color: var(--p-red); }
.pk-location-action {
 align-self: center;
 border: 1px solid var(--p-border);
 border-radius: 999px;
 color: var(--p-text);
 font-size: 12px;
 font-weight: 700;
 padding: 8px 12px;
 white-space: nowrap;
}

/* ── Modal ── */
.pk-modal-overlay {
 display: none; position: fixed; inset: 0;
 background: rgba(26,26,46,.55); backdrop-filter: blur(4px);
 z-index: 1000; align-items: flex-end; justify-content: center;
 padding: 20px;
}
.pk-modal-overlay.open { display: flex; }
.pk-modal {
 background: var(--p-surface);
 backdrop-filter: blur(16px);
 border-radius: 28px 28px 24px 24px;
 padding: 30px 24px 28px;
 width: 100%; max-width: 460px;
 position: relative;
  animation: modal-spring-bounce 0.48s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
@keyframes modal-spring-bounce {
  from {
    transform: scale(0.85) translateY(30px);
    opacity: 0;
  }
  to {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}
.pk-modal-pill {
 width: 40px; height: 4px; border-radius: 99px;
 background: var(--p-border); margin: 0 auto 22px; display: block;
}
.pk-modal-tag { font-size: 12px; color: var(--p-muted); text-align: center; margin-bottom: 6px; letter-spacing: .4px; text-transform: uppercase; }
.pk-modal-title { font-size: 22px; font-weight: 800; text-align: center; margin-bottom: 4px; }
.pk-modal-sub { font-size: 13px; color: var(--p-muted); text-align: center; margin-bottom: 20px; }
.pk-qr-box {
 background: var(--p-card); border-radius: 20px;
 padding: 20px; display: flex; justify-content: center; margin-bottom: 20px;
}
.pk-qr-box img { width: 200px; height: 200px; max-width: 100%; border-radius: 10px; }
.pk-btn-primary {
 width: 100%; padding: 16px;
 background: var(--p-purple); color: #fff;
 border: none; border-radius: 16px;
 font-size: 15px; font-weight: 700; cursor: pointer;
 transition: background .18s;
}
.pk-btn-primary:hover { background: var(--p-purple2); }
.pk-btn-outline {
 width: 100%; padding: 14px;
 background: transparent; color: var(--p-text);
 border: 1.5px solid var(--p-border); border-radius: 16px;
 font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 10px;
 transition: all .18s;
}
.pk-btn-outline:hover { background: var(--p-card); }

/* ── Empty state ── */
.pk-empty { text-align: center; padding: 40px 20px; color: var(--p-muted); font-size: 14px; }
.pk-empty-icon { font-size: 48px; margin-bottom: 12px; }

/* ── Countdown timer ── */
.pk-countdown-wrap {
 display: flex; flex-direction: column; align-items: center;
 gap: 8px; margin-bottom: 18px;
}
.pk-countdown-ring {
 position: relative; width: 72px; height: 72px;
}
.pk-countdown-ring svg {
 transform: rotate(-90deg);
}
.pk-countdown-ring circle {
 fill: none; stroke-width: 5;
}
.pk-countdown-track { stroke: var(--p-card); }
.pk-countdown-bar { stroke: var(--p-purple); stroke-linecap: round; transition: stroke-dashoffset .9s linear; }
.pk-countdown-bar.warning { stroke: #F59E0B; }
.pk-countdown-bar.danger { stroke: var(--p-red); }
.pk-countdown-num {
 position: absolute; inset: 0;
 display: flex; align-items: center; justify-content: center;
 font-size: 18px; font-weight: 800; color: var(--p-purple);
}
.pk-countdown-num.warning { color: #F59E0B; }
.pk-countdown-num.danger { color: var(--p-red); }
.pk-countdown-label {
 font-size: 12px; color: var(--p-muted); text-align: center; line-height: 1.4;
}
.pk-countdown-label strong { color: var(--p-text); font-size: 13px; }
.pk-expired-overlay {
 display: none; position: absolute; inset: 0;
 background: rgba(255,255,255,.92); border-radius: 28px 28px 24px 24px;
 flex-direction: column; align-items: center; justify-content: center;
 gap: 10px; z-index: 10;
}
.pk-expired-overlay.show { display: flex; }
.pk-expired-icon { font-size: 42px; }
.pk-expired-title { font-size: 18px; font-weight: 800; color: var(--p-red); }
.pk-expired-msg { font-size: 13px; color: var(--p-muted); text-align: center; }

/* ── Active parking banner ── */
.pk-active-banner {
 background: linear-gradient(135deg, var(--p-purple), var(--p-purple2));
 color: #fff;
 border-radius: var(--p-radius);
 padding: 18px 20px;
 display: flex; align-items: center; gap: 14px;
 margin-bottom: 20px;
 box-shadow: 0 8px 24px rgba(124,92,191,.3);
}
.pk-active-banner-icon { font-size: 28px; }
.pk-active-banner-text small { font-size: 12px; opacity: .8; }
.pk-active-banner-text strong { font-size: 17px; font-weight: 700; display: block; margin-top: 2px; }

/* DYNAMIC MOTION UPGRADES */
@keyframes banner-pulse {
  0% { transform: scale(1); box-shadow: 0 8px 24px rgba(124,92,191,.3); }
  50% { transform: scale(1.015); box-shadow: 0 12px 32px rgba(124,92,191,.5); }
  100% { transform: scale(1); box-shadow: 0 8px 24px rgba(124,92,191,.3); }
}
.pk-active-banner {
  animation: banner-pulse 3s infinite ease-in-out;
}

@keyframes dot-pulse {
  0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(60,201,138,0.4); }
  70% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(60,201,138,0); }
  100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(60,201,138,0); }
}
.pk-stat-dot.green {
  animation: dot-pulse 2s infinite ease-in-out;
}

.pk-location-card {
  position: relative;
  overflow: hidden;
  transition: all .25s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
}
.pk-location-card::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: var(--p-radius);
  padding: 1.5px;
  background: linear-gradient(135deg, rgba(255,255,255,0.08), transparent);
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  transition: background 0.3s;
}
.pk-location-card:hover::before {
  background: linear-gradient(135deg, var(--p-purple), var(--p-purple2));
}
.pk-location-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 45px rgba(0,0,0,0.28);
}

/* ── LIGHT MODE STYLES OVERRIDE ── */
body.light-mode .pk-wrap {
  --p-bg: transparent;
  --p-surface: #ffffff;
  --p-card: #f9fafb;
  --p-purple: #3b82f6;
  --p-purple2: #2563eb;
  --p-green: #10b981;
  --p-red: #ef4444;
  --p-gray: #6b7280;
  --p-text: #1f2937;
  --p-muted: #6b7280;
  --p-border: rgba(99, 102, 241, 0.15);
  --p-shadow: 0 10px 30px rgba(99, 102, 241, 0.05);
}
body.light-mode .pk-type-btn {
  border-color: var(--p-border);
}
body.light-mode .pk-location-card {
  border: 1px solid var(--p-border);
}
body.light-mode .pk-expired-overlay {
  background: rgba(255, 255, 255, 0.95);
}
</style>

<div class="main">
 <div class="topbar">
 <div class="page-title">Area Parkir</div>
 <div class="topbar-right">
 <button class="btn btn-outline btn-sm" onclick="location.reload()"> Refresh</button>
 </div>
 </div>
 <div class="content fade-in-up">
 <div class="pk-wrap">

  <?php if ($msg): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      showToast(<?= json_encode($msg) ?>, 'success');
    });
  </script>
  <?php endif; ?>
  <?php if ($err): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      showToast(<?= json_encode($err) ?>, 'error');
    });
  </script>
  <?php endif; ?>

 <?php if ($active_parking): ?>
 <!-- Active Parking Banner -->
 <div class="pk-active-banner">
 <div class="pk-active-banner-icon">🚗</div>
 <div class="pk-active-banner-text">
 <small>Sedang parkir di</small>
 <strong>Area <?= htmlspecialchars($active_parking['lokasi']) ?></strong>
 </div>
 </div>
 <?php endif; ?>

 <!-- Vehicle type picker -->
 <div id="vehicleTypeSection">
 <div class="pk-heading">Pilih Kendaraan</div>
 <div class="pk-subheading">Pilih jenis kendaraan untuk melihat area yang tersedia.</div>
 <div class="pk-type-grid">
 <button type="button" class="pk-type-btn" onclick="selectVehicleType('mobil')">
 <div class="pk-type-icon">🚗</div>
 <div class="pk-type-label">Mobil</div>
 <div class="pk-type-desc">Kendaraan roda 4</div>
 </button>
 <button type="button" class="pk-type-btn" onclick="selectVehicleType('motor')">
 <div class="pk-type-icon">🏍️</div>
 <div class="pk-type-label">Motor</div>
 <div class="pk-type-desc">Kendaraan roda 2</div>
 </button>
 </div>
 </div>

 <!-- Area content after type selected -->
 <div id="slotContentSection" style="display:none;">

 <!-- Selected type bar -->
 <div class="pk-selected-bar">
 <div>
 <small>Jenis kendaraan terpilih</small>
 <strong id="selectedTypeLabel"></strong>
 </div>
 <button class="pk-ganti-btn" onclick="resetSelection()">Ganti</button>
 </div>

 <!-- Stat cards -->
 <div class="pk-stat-grid">
 <div class="pk-stat-card">
 <div class="pk-stat-dot green">🟢</div>
 <div class="pk-stat-label">Tersedia</div>
 <div class="pk-stat-val green stat-value"><?= $total_tersedia ?></div>
 </div>
 <div class="pk-stat-card">
 <div class="pk-stat-dot red">🔴</div>
 <div class="pk-stat-label">Terisi</div>
 <div class="pk-stat-val red stat-value"><?= $total_terisi ?></div>
 </div>
 <div class="pk-stat-card">
 <div class="pk-stat-dot gray">⚪</div>
  <div class="pk-stat-label">Maintenance</div>
  <div class="pk-stat-val gray stat-value"><?= $total_maintenance ?></div>
  </div>
  </div>

  <!-- Location cards -->
  <?php if (empty($slot_data)): ?>
  <div class="pk-empty">
  <div class="pk-empty-icon">⚠️</div>
  <div>Belum ada data lokasi parkir</div>
  </div>
  <?php else: ?>
  <?php foreach ($slot_data as $area => $slots_in_area):
  $area_counts = [
  'mobil' => ['total' => 0, 'tersedia' => 0, 'maintenance' => 0],
  'motor' => ['total' => 0, 'tersedia' => 0, 'maintenance' => 0],
  ];
  foreach ($slots_in_area as $slot_item) {
  $vehicle_type = $slot_item['jenis_kendaraan'] ?? 'mobil';
  if (!isset($area_counts[$vehicle_type])) $area_counts[$vehicle_type] = ['total' => 0, 'tersedia' => 0, 'maintenance' => 0];
  $area_counts[$vehicle_type]['total'] = intval($slot_item['kapasitas']);
  $area_counts[$vehicle_type]['tersedia'] = intval($slot_item['tersedia']);
  $area_counts[$vehicle_type]['maintenance'] = intval($slot_item['maintenance']);
  }
  
  // Choose value based on active selection (fallback to first item if not set, Javascript will update anyway)
  $first_type = isset($slots_in_area[0]) ? $slots_in_area[0]['jenis_kendaraan'] : 'mobil';
  $area_tersedia = $area_counts[$first_type]['tersedia'];
  $area_total = $area_counts[$first_type]['total'];
  $pct = $area_total > 0 ? round($area_tersedia/$area_total*100) : 0;
  $area_status_cls = $area_tersedia === 0 ? 'full' : 'available';
  $area_status_lbl = $area_tersedia === 0 ? 'PENUH' : 'AVAILABLE';
  ?>
  <div class="pk-location-card <?= $active_parking ? 'disabled' : '' ?>"
  onclick="chooseArea(this)"
  data-lokasi="<?= htmlspecialchars($area, ENT_QUOTES) ?>"
  data-mobil-total="<?= intval($area_counts['mobil']['total'] ?? 0) ?>"
  data-mobil-available="<?= intval($area_counts['mobil']['tersedia'] ?? 0) ?>"
  data-mobil-maintenance="<?= intval($area_counts['mobil']['maintenance'] ?? 0) ?>"
  data-motor-total="<?= intval($area_counts['motor']['total'] ?? 0) ?>"
  data-motor-available="<?= intval($area_counts['motor']['tersedia'] ?? 0) ?>"
  data-motor-maintenance="<?= intval($area_counts['motor']['maintenance'] ?? 0) ?>">
  <div class="pk-location-header">
  <div class="pk-location-icon">📍</div>
  <div style="flex:1;">
  <div class="pk-location-name"><?= htmlspecialchars($area) ?></div>
  <div class="pk-location-sub"> Area parkir <?= htmlspecialchars(strtolower($area)) ?></div>
  <div class="pk-location-badge"><?= $area_tersedia ?> tempat tersedia</div>
  <div class="pk-avail-bar-wrap"><div class="pk-avail-bar" style="width:<?= $pct ?>%;"></div></div>
  <div class="pk-avail-status <?= $area_status_cls ?>"><?= $area_status_lbl ?></div>
  </div>
  <div class="pk-location-action">Pilih Area</div>
  </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
  </div>

  </div><!-- /pk-wrap -->
  </div><!-- /content -->
</div><!-- /main -->

<!-- Hidden reserve form -->
<form method="POST" id="reserveForm" style="display:none;">
 <input type="hidden" name="reserve_slot" value="1">
 <input type="hidden" name="lokasi" id="reserveLokasi">
 <input type="hidden" name="kode_qr" id="reserveKodeQr">
 <input type="hidden" name="jenis_kendaraan" id="reserveJenisKendaraan">
</form>

<!-- Reserve modal -->
<div class="pk-modal-overlay" id="reserveModal">
 <div class="pk-modal" style="position:relative;">
 <span class="pk-modal-pill"></span>
 <div class="pk-modal-tag">Area parkir dipilih</div>
 <div class="pk-modal-title" id="reserveSlotTitle">Area</div>
 <div class="pk-modal-sub" id="reserveSlotType" style="margin-bottom: 24px;"></div>
 <button class="pk-btn-primary" id="reserveConfirmBtn" onclick="submitReserve()">Konfirmasi Area</button>
 <button class="pk-btn-outline" onclick="closeReserveModal()">Batal</button>
 </div>
</div>

<!-- Success QR modal -->
<?php if ($qr_info): ?>
<div class="pk-modal-overlay open" id="successQrModal">
 <div class="pk-modal" style="position:relative;">
 <!-- Expired overlay -->
 <div class="pk-expired-overlay" id="successExpired">
 <div class="pk-expired-icon">⏱</div>
 <div class="pk-expired-title">Waktu Habis!</div>
 <div class="pk-expired-msg">QR sudah kadaluarsa.<br>Hubungi petugas jika butuh bantuan.</div>
 <button class="pk-btn-outline" style="margin-top:10px;width:auto;padding:10px 28px;" onclick="document.getElementById('successQrModal').classList.remove('open')">Tutup</button>
 </div>
 <span class="pk-modal-pill"></span>
 <div class="pk-modal-tag"> Parkir berhasil dipesan</div>
 <div class="pk-modal-title">Area <?= htmlspecialchars($qr_info['lokasi']) ?></div>
 <div class="pk-modal-sub"><?= htmlspecialchars($qr_info['plat']) ?> · <?= ucfirst(htmlspecialchars($qr_info['jenis'])) ?></div>

 <!-- Countdown -->
 <div class="pk-countdown-wrap">
 <div class="pk-countdown-ring">
 <svg width="72" height="72" viewBox="0 0 72 72">
 <circle class="pk-countdown-track" cx="36" cy="36" r="32"/>
 <circle class="pk-countdown-bar" id="successCountBar" cx="36" cy="36" r="32"
 stroke-dasharray="201" stroke-dashoffset="0"/>
 </svg>
 <div class="pk-countdown-num" id="successCountNum">5:00</div>
 </div>
 <div class="pk-countdown-label">
 <strong>QR berlaku 5 menit</strong><br>
 Scan sebelum waktu habis
 </div>
 </div>

 <div class="pk-qr-box">
  <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=<?= urlencode($qr_info['kode_qr']) ?>" alt="QR Code">
 </div>
 <button class="pk-btn-outline" onclick="document.getElementById('successQrModal').classList.remove('open')">Tutup</button>
 </div>
</div>
<?php endif; ?>

<script>
const allSlotCounts = {
 tersedia: <?= intval($total_tersedia) ?>,
 terisi: <?= intval($total_terisi) ?>,
 maintenance: <?= intval($total_maintenance) ?>
};
let selectedVehicleType = null;

function updateSlotStats(type = null) {
 const statVals = document.querySelectorAll('.stat-value');
 if (!type) {
 statVals[0].textContent = allSlotCounts.tersedia;
 statVals[1].textContent = allSlotCounts.terisi;
 statVals[2].textContent = allSlotCounts.maintenance;
 return;
 }
 const counts = { tersedia: 0, terisi: 0, maintenance: 0 };
 document.querySelectorAll('.pk-location-card').forEach(card => {
 const total = getAreaNumber(card, type, 'Total');
 const tersedia = getAreaNumber(card, type, 'Available');
 const maintenance = getAreaNumber(card, type, 'Maintenance');
 counts.tersedia += tersedia;
 counts.maintenance += maintenance;
 counts.terisi += Math.max(0, total - tersedia - maintenance);
 });
 statVals[0].textContent = counts.tersedia;
 statVals[1].textContent = counts.terisi;
 statVals[2].textContent = counts.maintenance;
}

// Helper functions
function getAreaNumber(card, type, key) {
 return parseInt(card.dataset[type + key] || '0', 10);
}

function updateAreaCard(card, type) {
 const total = getAreaNumber(card, type, 'Total');
 const tersedia = getAreaNumber(card, type, 'Available');
 const maintenance = getAreaNumber(card, type, 'Maintenance');
 const badge = card.querySelector('.pk-location-badge');
 const bar = card.querySelector('.pk-avail-bar');
 const status = card.querySelector('.pk-avail-status');
 const action = card.querySelector('.pk-location-action');
 const pct = total > 0 ? Math.round((tersedia / total) * 100) : 0;
 card.style.display = total > 0 ? 'block' : 'none';
 badge.textContent = tersedia + ' tempat tersedia';
 bar.style.width = pct + '%';
 status.classList.remove('available', 'full', 'maintenance');
 if (tersedia > 0) {
 status.classList.add('available');
 status.textContent = 'AVAILABLE';
 } else if (maintenance === total && total > 0) {
 status.classList.add('maintenance');
 status.textContent = 'MAINTENANCE';
 } else {
 status.classList.add('full');
 status.textContent = 'PENUH';
 }
 action.textContent = tersedia > 0 ? 'Pilih Area' : 'Penuh';
}

function selectVehicleType(type) {
 selectedVehicleType = type;
 document.getElementById('reserveJenisKendaraan').value = type;
 document.getElementById('selectedTypeLabel').textContent = type === 'mobil' ? 'Mobil' : 'Motor';
 document.getElementById('vehicleTypeSection').style.display = 'none';
 document.getElementById('slotContentSection').style.display = 'block';
 document.querySelectorAll('.pk-location-card').forEach(card => {
 updateAreaCard(card, type);
 });
 updateSlotStats(type);
}

function resetSelection() {
 selectedVehicleType = null;
 document.getElementById('reserveJenisKendaraan').value = '';
 document.getElementById('vehicleTypeSection').style.display = 'block';
 document.getElementById('slotContentSection').style.display = 'none';
}

function chooseArea(card) {
 if (!selectedVehicleType || card.classList.contains('disabled')) return;
 const tersedia = getAreaNumber(card, selectedVehicleType, 'Available');
 if (tersedia <= 0) return;
 openReserveModal(card.dataset.lokasi);
}

function generateQrCodeValue(lokasi) {
 const rand = Math.random().toString(36).substring(2,10).toUpperCase();
 return `DEMO-${lokasi}-${rand}-${Date.now()}`;
}

/* ── Countdown engine ── */
const COUNTDOWN_SEC = 5 * 60; // 5 menit
const CIRCUMFERENCE = 2 * Math.PI * 32; // r=32 → ~201

let reserveTimer = null;
let successTimer = null;

function startCountdown(barId, numId, expiredId, confirmBtnId, onExpire) {
 let remaining = COUNTDOWN_SEC;
 const bar = document.getElementById(barId);
 const num = document.getElementById(numId);
 const expired = document.getElementById(expiredId);
 const confirmBtn = confirmBtnId ? document.getElementById(confirmBtnId) : null;

 function tick() {
 const m = Math.floor(remaining / 60);
 const s = remaining % 60;
 const display = m + ':' + String(s).padStart(2, '0');
 num.textContent = display;

 // Ring progress
 const progress = remaining / COUNTDOWN_SEC;
 bar.style.strokeDashoffset = CIRCUMFERENCE * (1 - progress);

 // Color states
 const isWarning = remaining <= 60;
 const isDanger = remaining <= 30;
 [bar, num].forEach(el => {
 el.classList.toggle('warning', isWarning && !isDanger);
 el.classList.toggle('danger', isDanger);
 });

 if (remaining <= 0) {
 clearInterval(reserveTimer);
 clearInterval(successTimer);
 expired.classList.add('show');
 if (confirmBtn) confirmBtn.disabled = true;
 if (onExpire) onExpire();
 return;
 }
 remaining--;
 }

 tick();
 return setInterval(tick, 1000);
}

function openReserveModal(lokasi) {
 const kodeQr = generateQrCodeValue(lokasi);
 document.getElementById('reserveLokasi').value = lokasi;
 document.getElementById('reserveKodeQr').value = kodeQr;
 document.getElementById('reserveJenisKendaraan').value = selectedVehicleType;
 document.getElementById('reserveSlotTitle').textContent = lokasi;
 document.getElementById('reserveSlotType').textContent = 'Jenis: ' + (selectedVehicleType === 'mobil' ? 'Mobil' : 'Motor');
 document.getElementById('reserveModal').classList.add('open');
}

function closeReserveModal() {
 document.getElementById('reserveModal').classList.remove('open');
}

function submitReserve() {
 document.getElementById('reserveForm').submit();
}

// Auto-start success modal countdown on page load
<?php if ($qr_info): ?>
window.addEventListener('DOMContentLoaded', () => {
 document.getElementById('successExpired').classList.remove('show');
 successTimer = startCountdown('successCountBar', 'successCountNum', 'successExpired', null, null);
});
<?php endif; ?>
</script>
</body></html>
