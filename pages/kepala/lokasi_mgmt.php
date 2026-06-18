<?php
require_once '../../includes/config.php';
requireRole('kepala_loket');
$page_title = 'Kelola Lokasi Parkir';

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'];
  if ($action === 'tambah') {
    $lokasi = trim($_POST['lokasi']);
    $jenis = $_POST['jenis_kendaraan'] === 'motor' ? 'motor' : 'mobil';
    $kapasitas = intval($_POST['kapasitas'] ?? 0);

    if ($lokasi === '') {
      $err = 'Nama lokasi wajib diisi.';
    } elseif ($kapasitas < 1 || $kapasitas > 1000) {
      $err = 'Kapasitas harus antara 1 sampai 1000.';
    } else {
      // Check if already exists
      $checkStmt = $conn->prepare("SELECT id_lokasi FROM lokasi_parkir WHERE lokasi = ? AND jenis_kendaraan = ? LIMIT 1");
      $checkStmt->bind_param("ss", $lokasi, $jenis);
      $checkStmt->execute();
      if ($checkStmt->get_result()->fetch_assoc()) {
        $err = 'Lokasi parkir dengan nama dan jenis kendaraan tersebut sudah terdaftar.';
      } else {
        $insertStmt = $conn->prepare("INSERT INTO lokasi_parkir (lokasi, jenis_kendaraan, kapasitas, status_lokasi) VALUES (?,?,?, 'tersedia')");
        $insertStmt->bind_param("ssi", $lokasi, $jenis, $kapasitas);
        if ($insertStmt->execute()) {
          $msg = "Lokasi parkir <strong>" . htmlspecialchars($lokasi) . "</strong> untuk <strong>$jenis</strong> dengan kapasitas <strong>$kapasitas</strong> kendaraan berhasil ditambahkan.";
        } else {
          $err = 'Gagal menambahkan lokasi: ' . $conn->error;
        }
      }
    }
  } elseif ($action === 'status') {
    $id = intval($_POST['id_lokasi']);
    $status = $_POST['status_lokasi'];
    if ($conn->query("UPDATE lokasi_parkir SET status_lokasi='$status' WHERE id_lokasi=$id")) {
      $msg = 'Status lokasi diperbarui!';
    } else {
      $err = 'Gagal memperbarui status lokasi.';
    }
  } elseif ($action === 'hapus') {
    $id = intval($_POST['id_lokasi']);
    $relation = $conn->query("SELECT COUNT(*) as c FROM transaksi_parkir WHERE id_lokasi=$id")->fetch_assoc();
    $relatedCount = $relation ? intval($relation['c']) : 0;
    if ($relatedCount > 0) {
      $err = 'Lokasi tidak dapat dihapus karena sudah terkait ' . $relatedCount . ' transaksi parkir.';
    } elseif ($conn->query("DELETE FROM lokasi_parkir WHERE id_lokasi=$id")) {
      $msg = 'Lokasi parkir dihapus!';
    } else {
      $err = 'Gagal menghapus lokasi: ' . $conn->error;
    }
  }
}

// Fetch stats
$total_capacity_query = $conn->query("SELECT SUM(kapasitas) as c FROM lokasi_parkir")->fetch_assoc();
$total_capacity = $total_capacity_query ? intval($total_capacity_query['c']) : 0;

$total_maintenance_query = $conn->query("SELECT SUM(kapasitas) as c FROM lokasi_parkir WHERE status_lokasi='maintenance'")->fetch_assoc();
$total_maintenance = $total_maintenance_query ? intval($total_maintenance_query['c']) : 0;

$total_terisi = $conn->query("SELECT COUNT(*) as c FROM transaksi_parkir WHERE status_transaksi='parkir'")->fetch_assoc()['c'];
$total_tersedia = max(0, $total_capacity - $total_terisi - $total_maintenance);

// Fetch all locations
$locations_query = $conn->query("SELECT * FROM lokasi_parkir ORDER BY lokasi, jenis_kendaraan");
$locations = [];
while ($row = $locations_query->fetch_assoc()) {
    $id_lok = intval($row['id_lokasi']);
    $activeCountResult = $conn->query("SELECT COUNT(*) as c FROM transaksi_parkir WHERE id_lokasi = $id_lok AND status_transaksi = 'parkir'")->fetch_assoc();
    $activeCount = $activeCountResult ? intval($activeCountResult['c']) : 0;
    $row['terisi'] = $activeCount;
    $row['tersedia'] = $row['status_lokasi'] === 'maintenance' ? 0 : max(0, intval($row['kapasitas']) - $activeCount);
    $locations[] = $row;
}

require_once '../../includes/header_staff.php';
require_once '../../includes/sidebar_kepala.php';
?>

<style>
:root {
 --m-bg: transparent;
 --m-surface: rgba(255,255,255,0.08);
 --m-card: rgba(255,255,255,0.1);
 --m-purple: #6366f1;
 --m-purple2: #8b5cf6;
 --m-green: #3CC98A;
 --m-red: #F06B6B;
 --m-amber: #F59E0B;
 --m-gray: #A0A3B1;
 --m-text: #f8fafc;
 --m-muted: #94a3b8;
 --m-border: rgba(255,255,255,.12);
 --m-shadow: 0 18px 40px rgba(0,0,0,.22);
 --m-radius: 18px;
}
.mgmt-wrap * { box-sizing: border-box; margin: 0; padding: 0; }
.mgmt-wrap {
 font-family: 'DM Sans', 'Segoe UI', sans-serif;
 color: var(--m-text);
}

/* ── Alert ── */
.mgmt-alert {
 border-radius: 14px; padding: 14px 18px;
 font-size: 13.5px; margin-bottom: 18px;
 display: flex; align-items: center; gap: 10px;
}
.mgmt-alert-success { background: #D4F5E8; color: #1A6644; border: 1px solid rgba(60,201,138,.3); }
.mgmt-alert-error { background: #FDE8E8; color: #8B1A1A; border: 1px solid rgba(240,107,107,.3); }

/* ── Summary cards ── */
.mgmt-summary {
 display: grid;
 grid-template-columns: repeat(4, 1fr);
 gap: 14px;
 margin-bottom: 24px;
}
@media (max-width: 900px) { .mgmt-summary { grid-template-columns: repeat(2,1fr); } }
.mgmt-sum-card {
 background: var(--m-surface);
 backdrop-filter: blur(12px);
 border-radius: var(--m-radius);
 padding: 20px 18px;
 box-shadow: var(--m-shadow);
 display: flex; flex-direction: column; gap: 6px;
}
.mgmt-sum-icon {
 width: 40px; height: 40px; border-radius: 12px;
 display: flex; align-items: center; justify-content: center;
 font-size: 20px; margin-bottom: 6px;
}
.mgmt-sum-icon.purple { background: rgba(124,92,191,.12); }
.mgmt-sum-icon.green { background: rgba(60,201,138,.12); }
.mgmt-sum-icon.red { background: rgba(240,107,107,.12); }
.mgmt-sum-icon.gray { background: rgba(160,163,177,.12); }
.mgmt-sum-label { font-size: 11px; font-weight: 600; letter-spacing: .5px; text-transform: uppercase; color: var(--m-muted); }
.mgmt-sum-val { font-size: 30px; font-weight: 800; line-height: 1; }
.mgmt-sum-val.purple { color: var(--m-purple); }
.mgmt-sum-val.green { color: var(--m-green); }
.mgmt-sum-val.red { color: var(--m-red); }
.mgmt-sum-val.gray { color: var(--m-gray); }

/* ── Main layout ── */
.mgmt-layout {
 display: grid;
 grid-template-columns: 1fr 300px;
 gap: 20px;
 align-items: flex-start;
}
@media (max-width: 860px) { .mgmt-layout { grid-template-columns: 1fr; } }

/* ── Table structure ── */
.mgmt-table-card {
 background: var(--m-surface);
 backdrop-filter: blur(12px);
 border-radius: var(--m-radius);
 box-shadow: var(--m-shadow);
 overflow: hidden;
 border: 1px solid var(--m-border);
}
.mgmt-table { width: 100%; border-collapse: collapse; }
.mgmt-table th {
 padding: 14px 18px; font-size: 11px; font-weight: 700; letter-spacing: .5px;
 text-transform: uppercase; color: var(--m-muted);
 background: var(--m-card); text-align: left; border-bottom: 1px solid var(--m-border);
}
.mgmt-table td {
 padding: 16px 18px; font-size: 13.5px;
 border-bottom: 1px solid var(--m-border);
 vertical-align: middle;
}
.mgmt-table tr:last-child td { border-bottom: none; }
.mgmt-table tr:hover td { background: var(--m-card); }

/* ── Badges ── */
.mgmt-badge {
 display: inline-flex; align-items: center; gap: 5px;
 padding: 5px 12px; border-radius: 99px; font-size: 12px; font-weight: 600;
}
.mgmt-badge.mobil { background: rgba(60,201,138,.12); color: #1a6644; }
.mgmt-badge.motor { background: rgba(124,92,191,.12); color: var(--m-purple); }
.mgmt-badge.tersedia { background: rgba(60,201,138,.12); color: #1a6644; }
.mgmt-badge.terisi { background: rgba(240,107,107,.12); color: #8b1a1a; }
.mgmt-badge.maintenance{ background: rgba(160,163,177,.15); color: #555; }

/* ── Inline status form ── */
.mgmt-status-form { display: flex; gap: 8px; align-items: center; }
.mgmt-select {
 padding: 7px 10px; border-radius: 10px; font-size: 12.5px;
 border: 1.5px solid var(--m-border); background: var(--m-surface);
 color: var(--m-text); outline: none; cursor: pointer;
 transition: border-color .18s;
}
.mgmt-select:focus { border-color: var(--m-purple); }
.mgmt-btn {
  padding: 7px 14px; border-radius: 10px; font-size: 12.5px;
  font-weight: 600; border: none; cursor: pointer; transition: all .18s;
}
.mgmt-btn-update { background: var(--m-purple); color: #fff; }
.mgmt-btn-update:hover { background: var(--m-purple2); }
.mgmt-btn-delete { background: rgba(240,107,107,.12); color: #8b1a1a; border: 1px solid rgba(240,107,107,.2); }
.mgmt-btn-delete:hover { background: var(--m-red); color: #fff; }

/* ── Add locations form panel ── */
.mgmt-form-panel {
 background: var(--m-surface);
 border-radius: var(--m-radius);
 box-shadow: var(--m-shadow);
 padding: 22px 20px;
 position: sticky; top: 20px;
 border: 1px solid var(--m-border);
}
.mgmt-form-title {
 font-size: 15px; font-weight: 700; margin-bottom: 6px;
}
.mgmt-form-subtitle { font-size: 12.5px; color: var(--m-muted); margin-bottom: 20px; line-height: 1.5; }
.mgmt-form-group { margin-bottom: 16px; }
.mgmt-form-label {
 display: block; font-size: 12px; font-weight: 700;
 letter-spacing: .4px; text-transform: uppercase; color: var(--m-muted);
 margin-bottom: 7px;
}
.mgmt-form-control {
 width: 100%; padding: 11px 14px; border-radius: 12px;
 border: 1.5px solid var(--m-border); background: var(--m-card);
 font-size: 14px; color: var(--m-text); outline: none;
 transition: border-color .18s;
}
.mgmt-form-control:focus { border-color: var(--m-purple); background: var(--m-surface); }
.mgmt-btn-submit {
 width: 100%; padding: 14px; border-radius: 14px;
 background: var(--m-purple); color: #fff;
 border: none; font-size: 14px; font-weight: 700;
 cursor: pointer; transition: background .18s; margin-top: 4px;
}
.mgmt-btn-submit:hover { background: var(--m-purple2); }

/* ── LIGHT MODE STYLES OVERRIDE ── */
body.light-mode .mgmt-wrap {
  --m-bg: transparent;
  --m-surface: #ffffff;
  --m-card: #f9fafb;
  --m-purple: #3b82f6;
  --m-purple2: #2563eb;
  --m-green: #10b981;
  --m-red: #ef4444;
  --m-amber: #f59e0b;
  --m-gray: #6b7280;
  --m-text: #1f2937;
  --m-muted: #6b7280;
  --m-border: rgba(99, 102, 241, 0.15);
  --m-shadow: 0 10px 30px rgba(99, 102, 241, 0.05);
}
body.light-mode .mgmt-badge.mobil { background: rgba(16, 185, 129, 0.15); color: #059669; }
body.light-mode .mgmt-badge.motor { background: rgba(59, 130, 246, 0.15); color: #1d4ed8; }
body.light-mode .mgmt-badge.tersedia { background: rgba(16, 185, 129, 0.15); color: #059669; }
body.light-mode .mgmt-badge.terisi { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
body.light-mode .mgmt-badge.maintenance { background: rgba(107, 114, 128, 0.15); color: #4b5563; }
body.light-mode .mgmt-btn-delete { background: rgba(239, 68, 68, 0.15); color: #dc2626; border-color: rgba(239, 68, 68, 0.25); }
body.light-mode .mgmt-btn-delete:hover { background: #ef4444; color: #fff; }
</style>

<div class="main">
 <div class="topbar"><div class="page-title">Kelola Lokasi Parkir</div></div>

 <?php if ($msg): ?>
 <div class="mgmt-alert mgmt-alert-success"> <?= $msg ?></div>
 <?php endif; ?>
 <?php if ($err): ?>
 <div class="mgmt-alert mgmt-alert-error"> <?= $err ?></div>
 <?php endif; ?>

 <div class="mgmt-wrap">
   <!-- Summary cards -->
   <div class="mgmt-summary">
     <div class="mgmt-sum-card">
       <div class="mgmt-sum-icon purple">📍</div>
       <div class="mgmt-sum-label">Kapasitas Total</div>
       <div class="mgmt-sum-val purple"><?= $total_capacity ?></div>
     </div>
     <div class="mgmt-sum-card">
       <div class="mgmt-sum-icon green">🟢</div>
       <div class="mgmt-sum-label">Tersedia</div>
       <div class="mgmt-sum-val green"><?= $total_tersedia ?></div>
     </div>
     <div class="mgmt-sum-card">
       <div class="mgmt-sum-icon red">🔴</div>
       <div class="mgmt-sum-label">Terisi</div>
       <div class="mgmt-sum-val red"><?= $total_terisi ?></div>
     </div>
     <div class="mgmt-sum-card">
       <div class="mgmt-sum-icon gray">⚪</div>
       <div class="mgmt-sum-label">Maintenance</div>
       <div class="mgmt-sum-val gray"><?= $total_maintenance ?></div>
     </div>
   </div>

   <!-- Main layout -->
   <div class="mgmt-layout">
     
     <!-- Location list table -->
     <div class="mgmt-table-card">
       <table class="mgmt-table">
         <thead>
           <tr>
             <th>Lokasi / Area</th>
             <th>Jenis</th>
             <th>Kapasitas</th>
             <th>Terisi</th>
             <th>Tersedia</th>
             <th>Status</th>
             <th>Ubah Status</th>
             <th>Aksi</th>
           </tr>
         </thead>
         <tbody>
           <?php if (empty($locations)): ?>
             <tr>
               <td colspan="8" style="text-align:center;color:var(--m-muted);padding:30px;">Belum ada lokasi parkir terdaftar.</td>
             </tr>
           <?php else: ?>
             <?php foreach ($locations as $loc): ?>
               <tr>
                 <td><strong><?= htmlspecialchars($loc['lokasi']) ?></strong></td>
                 <td>
                   <span class="mgmt-badge <?= $loc['jenis_kendaraan'] ?>">
                     <?= ucfirst($loc['jenis_kendaraan']) ?>
                   </span>
                 </td>
                 <td><?= $loc['kapasitas'] ?></td>
                 <td><?= $loc['terisi'] ?></td>
                 <td><?= $loc['tersedia'] ?></td>
                 <td>
                   <span class="mgmt-badge <?= $loc['status_lokasi'] ?>">
                     <?= ucfirst($loc['status_lokasi']) ?>
                   </span>
                 </td>
                 <td>
                   <form method="POST" class="mgmt-status-form">
                     <input type="hidden" name="action" value="status">
                     <input type="hidden" name="id_lokasi" value="<?= $loc['id_lokasi'] ?>">
                     <select name="status_lokasi" class="mgmt-select">
                       <option value="tersedia" <?= $loc['status_lokasi']==='tersedia' ? 'selected' : '' ?>>Tersedia</option>
                       <option value="maintenance" <?= $loc['status_lokasi']==='maintenance' ? 'selected' : '' ?>>Maintenance</option>
                     </select>
                     <button type="submit" class="mgmt-btn mgmt-btn-update">Update</button>
                   </form>
                 </td>
                 <td>
                   <form method="POST" onsubmit="return confirm('Hapus lokasi parkir ini?')">
                     <input type="hidden" name="action" value="hapus">
                     <input type="hidden" name="id_lokasi" value="<?= $loc['id_lokasi'] ?>">
                     <button type="submit" class="mgmt-btn mgmt-btn-delete">Hapus</button>
                   </form>
                 </td>
               </tr>
             <?php endforeach; ?>
           <?php endif; ?>
         </tbody>
       </table>
     </div>

     <!-- Add location sidebar form -->
     <div class="mgmt-form-panel">
       <div class="mgmt-form-title">Tambah Lokasi Baru</div>
       <div class="mgmt-form-subtitle">Masukkan nama area, jenis kendaraan, dan kapasitas maksimum lokasi tersebut.</div>
       <form method="POST">
         <input type="hidden" name="action" value="tambah">
         <div class="mgmt-form-group">
           <label class="mgmt-form-label">Nama Fakultas / Area</label>
           <input type="text" name="lokasi" class="mgmt-form-control" placeholder="Contoh: Fakultas Teknik" required>
         </div>
         <div class="mgmt-form-group">
           <label class="mgmt-form-label">Jenis Kendaraan</label>
           <select name="jenis_kendaraan" class="mgmt-form-control" required>
             <option value="mobil">Mobil</option>
             <option value="motor">Motor</option>
           </select>
         </div>
         <div class="mgmt-form-group">
           <label class="mgmt-form-label">Kapasitas Kendaraan</label>
           <input type="number" name="kapasitas" class="mgmt-form-control" placeholder="Contoh: 50" min="1" max="1000" required>
         </div>
         <button type="submit" class="mgmt-btn-submit">Tambah Lokasi</button>
       </form>
     </div>

   </div>
 </div>
</div>
</body></html>
