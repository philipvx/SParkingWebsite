<?php
require_once '../../includes/config.php';
requireRole('petugas_keluar');
$page_title = 'Lokasi Parkir';

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
require_once '../../includes/sidebar_petugas.php';
?>

<style>
:root {
 --sp-bg: transparent;
 --sp-surface: rgba(255,255,255,0.08);
 --sp-card: rgba(255,255,255,0.1);
 --sp-purple: #6366f1;
 --sp-purple2: #8b5cf6;
 --sp-green: #3CC98A;
 --sp-red: #F06B6B;
 --sp-gray: #A0A3B1;
 --sp-text: #f8fafc;
 --sp-muted: #94a3b8;
 --sp-border: rgba(255,255,255,.12);
 --sp-shadow: 0 18px 40px rgba(0,0,0,.22);
 --sp-radius: 18px;
}
.sp-wrap * { box-sizing: border-box; margin: 0; padding: 0; }
.sp-wrap { font-family: 'Inter', 'Segoe UI', sans-serif; color: var(--sp-text); }

/* ── top summary bar ── */
.sp-summary {
 display: grid;
 grid-template-columns: repeat(4, 1fr);
 gap: 14px;
 margin-bottom: 22px;
}
@media (max-width: 800px) { .sp-summary { grid-template-columns: repeat(2,1fr); } }
.sp-sum-card {
 background: var(--sp-surface);
 backdrop-filter: blur(12px);
 border-radius: var(--sp-radius);
 padding: 18px 16px;
 box-shadow: var(--sp-shadow);
 display: flex; flex-direction: column; gap: 5px;
}
.sp-sum-icon {
 width: 38px; height: 38px; border-radius: 11px;
 display: flex; align-items: center; justify-content: center;
 font-size: 18px; margin-bottom: 4px;
}
.sp-sum-icon.purple { background: rgba(124,92,191,.12); }
.sp-sum-icon.green { background: rgba(60,201,138,.12); }
.sp-sum-icon.red { background: rgba(240,107,107,.12); }
.sp-sum-icon.gray { background: rgba(160,163,177,.12); }
.sp-sum-label { font-size: 11px; font-weight: 600; letter-spacing: .5px; text-transform: uppercase; color: var(--sp-muted); }
.sp-sum-val { font-size: 28px; font-weight: 800; line-height: 1; }
.sp-sum-val.purple { color: var(--sp-purple); }
.sp-sum-val.green { color: var(--sp-green); }
.sp-sum-val.red { color: var(--sp-red); }
.sp-sum-val.gray { color: var(--sp-gray); }

/* ── location list ── */
.sp-grid {
 display: grid;
 grid-template-columns: 1fr;
 gap: 16px;
}
.sp-card-item {
 background: var(--sp-surface);
 backdrop-filter: blur(12px);
 border-radius: var(--sp-radius);
 padding: 20px;
 box-shadow: var(--sp-shadow);
 border: 1px solid var(--sp-border);
 display: flex;
 justify-content: space-between;
 align-items: center;
 flex-wrap: wrap;
 gap: 16px;
}
.sp-card-left { display: flex; align-items: center; gap: 14px; }
.sp-card-icon {
 width: 44px; height: 44px; border-radius: 12px;
 background: rgba(124,92,191,.10);
 display: flex; align-items: center; justify-content: center;
 font-size: 20px;
}
.sp-card-info h4 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
.sp-card-info p { font-size: 12.5px; color: var(--sp-muted); }
.sp-badge {
 display: inline-flex; align-items: center; gap: 5px;
 padding: 4px 10px; border-radius: 99px; font-size: 11.5px; font-weight: 600;
}
.sp-badge.mobil { background: rgba(60,201,138,.12); color: #1a6644; }
.sp-badge.motor { background: rgba(124,92,191,.12); color: var(--sp-purple); }
.sp-badge.tersedia { background: rgba(60,201,138,.12); color: #1a6644; }
.sp-badge.maintenance{ background: rgba(160,163,177,.15); color: #555; }

.sp-card-right { display: flex; align-items: center; gap: 20px; }
.sp-metric { text-align: center; }
.sp-metric-label { font-size: 10px; text-transform: uppercase; color: var(--sp-muted); margin-bottom: 4px; font-weight: 600; }
.sp-metric-val { font-size: 18px; font-weight: 700; }

/* ── LIGHT MODE STYLES OVERRIDE ── */
body.light-mode .sp-wrap {
  --sp-bg: transparent;
  --sp-surface: #ffffff;
  --sp-card: #f9fafb;
  --sp-purple: #3b82f6;
  --sp-purple2: #2563eb;
  --sp-green: #10b981;
  --sp-red: #ef4444;
  --sp-gray: #6b7280;
  --sp-text: #1f2937;
  --sp-muted: #6b7280;
  --sp-border: rgba(99, 102, 241, 0.15);
  --sp-shadow: 0 10px 30px rgba(99, 102, 241, 0.05);
}
body.light-mode .sp-badge.mobil { background: rgba(16, 185, 129, 0.15); color: #059669; }
body.light-mode .sp-badge.motor { background: rgba(59, 130, 246, 0.15); color: #1d4ed8; }
body.light-mode .sp-badge.tersedia { background: rgba(16, 185, 129, 0.15); color: #059669; }
body.light-mode .sp-badge.maintenance { background: rgba(107, 114, 128, 0.15); color: #4b5563; }
</style>

<div class="main">
 <div class="topbar">
 <div class="page-title">Lokasi Parkir</div>
 <div class="topbar-right">
 <button class="btn btn-outline btn-sm" onclick="location.reload()"> Refresh</button>
 </div>
 </div>

 <div class="content">
 <div class="sp-wrap">

   <!-- Summary -->
   <div class="sp-summary">
     <div class="sp-sum-card">
       <div class="sp-sum-icon purple">📍</div>
       <div class="sp-sum-label">Kapasitas Total</div>
       <div class="sp-sum-val purple"><?= $total_capacity ?></div>
     </div>
     <div class="sp-sum-card">
       <div class="sp-sum-icon green">🟢</div>
       <div class="sp-sum-label">Tersedia</div>
       <div class="sp-sum-val green"><?= $total_tersedia ?></div>
     </div>
     <div class="sp-sum-card">
       <div class="sp-sum-icon red">🔴</div>
       <div class="sp-sum-label">Terisi</div>
       <div class="sp-sum-val red"><?= $total_terisi ?></div>
     </div>
     <div class="sp-sum-card">
       <div class="sp-sum-icon gray">⚪</div>
       <div class="sp-sum-label">Maintenance</div>
       <div class="sp-sum-val gray"><?= $total_maintenance ?></div>
     </div>
   </div>

   <!-- Location List -->
   <div class="sp-grid">
     <?php if (empty($locations)): ?>
       <div style="text-align:center;padding:50px;color:var(--sp-muted);">Belum ada lokasi parkir terdaftar.</div>
     <?php else: ?>
       <?php foreach ($locations as $loc): ?>
         <div class="sp-card-item">
           <div class="sp-card-left">
             <div class="sp-card-icon">📍</div>
             <div class="sp-card-info">
               <h4><?= htmlspecialchars($loc['lokasi']) ?></h4>
               <p style="display:flex;gap:6px;align-items:center;margin-top:2px;">
                 <span class="sp-badge <?= $loc['jenis_kendaraan'] ?>"><?= ucfirst($loc['jenis_kendaraan']) ?></span>
                 <span class="sp-badge <?= $loc['status_lokasi'] ?>"><?= ucfirst($loc['status_lokasi']) ?></span>
               </p>
             </div>
           </div>
           
           <div class="sp-card-right">
             <div class="sp-metric">
               <div class="sp-metric-label">Kapasitas</div>
               <div class="sp-metric-val" style="color:var(--sp-purple);"><?= $loc['kapasitas'] ?></div>
             </div>
             <div class="sp-metric">
               <div class="sp-metric-label">Terisi</div>
               <div class="sp-metric-val" style="color:var(--sp-red);"><?= $loc['terisi'] ?></div>
             </div>
             <div class="sp-metric">
               <div class="sp-metric-label">Tersedia</div>
               <div class="sp-metric-val" style="color:var(--sp-green);"><?= $loc['tersedia'] ?></div>
             </div>
           </div>
         </div>
       <?php endforeach; ?>
     <?php endif; ?>
   </div>

 </div><!-- /sp-wrap -->
 </div><!-- /content -->
</div><!-- /main -->

<script>
// Clock
function updateClock() {
 const now = new Date();
}
</script>
</body>
</html>
