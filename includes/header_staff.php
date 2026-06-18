<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? 'Dashboard' ?> — SParking UTN</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<style>
:root {
 --bg: #0f172a;
 --surface: rgba(255,255,255,0.08);
 --surface2: rgba(255,255,255,0.1);
 --surface3: rgba(255,255,255,0.14);
 --border: rgba(255,255,255,0.12);
 --accent: #6366f1;
 --accent2: #8b5cf6;
 --accent-cool: #38bdf8;
 --text: #f8fafc;
 --text-muted: #94a3b8;
 --success: #34d399;
 --warning: #f59e0b;
 --danger: #ef4444;
 --sidebar-w: 260px;
}
* { margin:0; padding:0; box-sizing:border-box; }
body {
 font-family:'Inter',sans-serif;
 background:
 radial-gradient(circle at 12% 16%, rgba(99,102,241,0.22), transparent 28%),
 radial-gradient(circle at 86% 64%, rgba(139,92,246,0.18), transparent 30%),
 linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
 color:var(--text);
 display:flex;
 min-height:100vh;
}

.sidebar {
 width: var(--sidebar-w);
 background: var(--surface);
 backdrop-filter: blur(14px);
 border-right: 1px solid var(--border);
 display: flex; flex-direction: column;
 position: fixed; top: 0; left: 0; bottom: 0; z-index: 100;
}
.sidebar-brand {
 padding: 22px 20px 18px;
 border-bottom: 1px solid var(--border);
 display: flex; align-items: center; gap: 12px;
}
.brand-icon {
 width: 40px; height: 40px;
 background: linear-gradient(135deg, var(--accent), var(--accent2));
 border-radius: 10px;
 display: flex; align-items: center; justify-content: center;
 font-size: 18px; flex-shrink: 0;
}
.brand-text h2 { font-family:'Poppins',sans-serif; font-size:16px; font-weight:700; }
.brand-text span { font-size:11px; color:var(--text-muted); }
.sidebar-role-badge {
 margin: 12px 20px;
 background: linear-gradient(135deg, rgba(155,93,229,0.12), rgba(190,149,255,0.1));
 border: 1px solid rgba(155,93,229,0.18);
 border-radius: 14px;
 padding: 12px 16px;
 display: flex; align-items: center; gap: 10px;
}
.role-avatar {
 width: 34px; height: 34px; border-radius: 50%;
 background: linear-gradient(135deg, var(--accent), var(--accent2));
 display: flex; align-items: center; justify-content: center;
 font-weight: 700; font-size: 13px; flex-shrink: 0;
}
.role-name { font-size: 13px; font-weight: 500; }
.role-label { font-size: 10px; color: var(--accent); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

nav { flex:1; padding: 14px 10px; overflow-y: auto; }
.nav-section { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted); padding:10px 10px 6px; }
.nav-item {
 display:flex; align-items:center; gap:10px;
 padding:11px 12px; border-radius:12px;
 color:var(--text-muted); text-decoration:none;
 font-size:14px; transition:all 0.15s; margin-bottom:6px;
}
.nav-item:hover { background:rgba(255,255,255,0.1); color:var(--text); transform: translateX(4px); }
.nav-item.active { background:rgba(99,102,241,0.18); color:#c7d2fe; font-weight:600; }
.nav-icon { font-size:15px; width:20px; text-align:center; flex-shrink:0; }
.sidebar-footer { padding:12px 10px; border-top:1px solid rgba(155,93,229,0.16); }

.main { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; }
.topbar {
 background:var(--surface); backdrop-filter: blur(14px); border-bottom:1px solid var(--border);
 padding:14px 28px; display:flex; align-items:center; justify-content:space-between;
 position:sticky; top:0; z-index:50;
}
.page-title { font-family:'Poppins',sans-serif; font-size:18px; font-weight:700; }
.topbar-right { display:flex; align-items:center; gap:10px; }
 padding:14px 28px; display:flex; align-items:center; justify-content:space-between;
 position:sticky; top:0; z-index:50;
}
.page-title { font-family:'Poppins',sans-serif; font-size:18px; font-weight:700; }
.topbar-right { display:flex; align-items:center; gap:10px; }
.topbar-time { font-size:12px; color:var(--text-muted); background:var(--surface2); padding:5px 12px; border-radius:20px; }
.content { padding:28px; flex:1; }

.stat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; margin-bottom:24px; }
.stat-card {
 background:var(--surface); backdrop-filter: blur(12px); border:1px solid var(--border); border-radius:14px; padding:20px;
 transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
}
.stat-card:hover { border-color:rgba(99,102,241,0.45); transform: translateY(-3px); box-shadow: 0 18px 40px rgba(0,0,0,0.22); }
.stat-icon { font-size:22px; margin-bottom:10px; }
.stat-label { font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }
.stat-value { font-family:'Syne',sans-serif; font-size:26px; font-weight:700; }
.stat-sub { font-size:12px; color:var(--text-muted); margin-top:3px; }

/* MINIMAL ICONS */
.brand-icon,
.nav-icon,
.stat-icon,
.mgmt-sum-icon,
.mgmt-area-icon,
.mgmt-empty-icon,
.sp-sum-icon,
.sp-area-icon,
.sp-slot-icon,
.sp-empty-icon {
 font-size: 0 !important;
 position: relative;
 color: currentColor;
}

.brand-icon::before,
.nav-icon::before,
.stat-icon::before,
.mgmt-sum-icon::before,
.mgmt-area-icon::before,
.mgmt-empty-icon::before,
.sp-sum-icon::before,
.sp-area-icon::before,
.sp-slot-icon::before,
.sp-empty-icon::before {
 content: '';
 display: inline-block;
 width: 18px;
 height: 18px;
 border: 1.8px solid currentColor;
 border-radius: 5px;
 vertical-align: middle;
}

.brand-icon::before {
 content: 'P';
 width: auto;
 height: auto;
 border: 0;
 border-radius: 0;
 font-family: 'Poppins', sans-serif;
 font-size: 18px;
 font-weight: 700;
 color: #fff;
}

.nav-icon::before {
 width: 16px;
 height: 16px;
 border-radius: 4px;
}

.stat-icon::before,
.mgmt-sum-icon::before,
.mgmt-area-icon::before,
.mgmt-empty-icon::before,
.sp-sum-icon::before,
.sp-area-icon::before,
.sp-empty-icon::before {
 width: 24px;
 height: 24px;
 border-radius: 7px;
}

.sp-slot-icon::before {
 width: 16px;
 height: 16px;
 border-radius: 50%;
}

.minimal-icon {
 width: 1em;
 height: 1em;
 display: inline-block;
 fill: none;
 stroke: currentColor;
 stroke-width: 2;
 stroke-linecap: round;
 stroke-linejoin: round;
}

.brand-icon .minimal-icon,
.stat-icon .minimal-icon,
.mgmt-sum-icon .minimal-icon,
.mgmt-area-icon .minimal-icon,
.mgmt-empty-icon .minimal-icon,
.sp-sum-icon .minimal-icon,
.sp-area-icon .minimal-icon,
.sp-empty-icon .minimal-icon {
 width: 24px;
 height: 24px;
}

.nav-icon .minimal-icon,
.sp-slot-icon .minimal-icon {
 width: 18px;
 height: 18px;
}

.brand-icon::before,
.nav-icon::before,
.stat-icon::before,
.mgmt-sum-icon::before,
.mgmt-area-icon::before,
.mgmt-empty-icon::before,
.sp-sum-icon::before,
.sp-area-icon::before,
.sp-slot-icon::before,
.sp-empty-icon::before {
 display: none !important;
}

.table-card { background:var(--surface); backdrop-filter:blur(12px); border:1px solid var(--border); border-radius:14px; overflow-x:auto; margin-bottom:24px; }
.table-header { padding:18px 22px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.table-header h3 { font-family:'Syne',sans-serif; font-size:15px; font-weight:700; }
table { width:100%; min-width:900px; border-collapse:collapse; }
th, td { white-space:nowrap; }
th { padding:11px 22px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted); background:rgba(99,102,241,0.12); text-align:left; }
td { padding:13px 22px; font-size:13px; border-bottom:1px solid var(--border); }
tr:last-child td { border-bottom:none; }
tr:hover td { background:rgba(255,255,255,0.06); }

.badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
.badge-green { background:rgba(34,197,94,0.12); color:#4ade80; }
.badge-blue { background:rgba(6,182,212,0.12); color:#67e8f9; }
.badge-yellow { background:rgba(234,179,8,0.12); color:#fde047; }
.badge-red { background:rgba(239,68,68,0.12); color:#fca5a5; }
.badge-gray { background:rgba(100,116,139,0.12); color:#94a3b8; }
.badge-orange { background:rgba(249,115,22,0.15); color:#fb923c; }

.btn { padding:8px 16px; border-radius:12px; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:13px; font-weight:500; transition:all 0.15s; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
.btn-primary { background:linear-gradient(135deg, var(--accent), var(--accent2)); color:white; }
.btn-primary:hover { background:linear-gradient(135deg, #be95ff, #9b5de5); }
.btn-outline { background:transparent; border:1px solid var(--border); color:var(--text-muted); }
.btn-outline:hover { border-color:var(--accent); color:var(--accent); }
.btn-success { background:rgba(52,211,153,0.15); color:var(--success); border:1px solid rgba(52,211,153,0.3); }
.btn-success:hover { background:var(--success); color:white; }
.btn-danger { background:rgba(239,68,68,0.12); color:var(--danger); border:1px solid rgba(239,68,68,0.3); }
.btn-danger:hover { background:var(--danger); color:white; }
.btn-sm { padding:5px 12px; font-size:12px; }

.form-card { background:var(--surface); backdrop-filter:blur(12px); border:1px solid var(--border); border-radius:14px; padding:24px; margin-bottom:24px; }
.form-group { margin-bottom:16px; }
.form-label { display:block; font-size:12px; font-weight:500; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }
.form-control { width:100%; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.16); border-radius:8px; padding:10px 14px; font-family:'Inter',sans-serif; font-size:13px; color:var(--text); outline:none; transition:border-color 0.2s, box-shadow 0.2s; }
.form-control:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(99,102,241,0.18); background:rgba(255,255,255,0.12); }
select.form-control option { background:var(--surface2); }

.alert { border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:16px; }
.alert-success { background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3); color:#4ade80; }
.alert-error { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#fca5a5; }
select.form-control option { background:var(--surface2); }

.alert { border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:16px; }
.alert-success { background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3); color:#4ade80; }
.alert-error { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#fca5a5; }

.two-col { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
.chart-placeholder {
 background:var(--surface2); border-radius:10px; padding:16px;
 display:flex; align-items:flex-end; gap:6px; height:140px;
}
.bar { flex:1; background:var(--accent); border-radius:4px 4px 0 0; opacity:0.7; transition:opacity 0.2s; }
.bar:hover { opacity:1; }

::-webkit-scrollbar { width:4px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:var(--border); border-radius:4px; }

/* ── LIGHT MODE STYLES ── */
.light-mode, body.light-mode {
  --bg: #f3f4f6;
  --surface: #ffffff;
  --surface2: #f9fafb;
  --surface3: #e5e7eb;
  --border: rgba(99, 102, 241, 0.15);
  --text: #1f2937;
  --text-muted: #6b7280;
  --accent: #3b82f6;
  --accent2: #2563eb;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
}

.light-mode body, body.light-mode {
  background: 
    radial-gradient(circle at 12% 16%, rgba(59, 130, 246, 0.12), transparent 30%), 
    radial-gradient(circle at 86% 64%, rgba(99, 102, 241, 0.08), transparent 30%), 
    linear-gradient(135deg, #f3f4f6 0%, #eff6ff 100%) !important;
  color: var(--text) !important;
}

select.form-control {
  background-color: rgba(255, 255, 255, 0.08) !important;
  color: var(--text) !important;
}

body.light-mode select.form-control {
  background-color: #ffffff !important;
  border-color: #d1d5db !important;
}

select.form-control option {
  background-color: #1e293b !important;
  color: #f8fafc !important;
}

body.light-mode select.form-control option {
  background-color: #ffffff !important;
  color: #1f2937 !important;
}

body.light-mode .sidebar {
  background: #ffffff;
  border-right-color: #e5e7eb;
}

body.light-mode .sidebar-role-badge {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(99, 102, 241, 0.05));
  border-color: rgba(59, 130, 246, 0.12);
}

body.light-mode .nav-item:hover {
  background: rgba(59, 130, 246, 0.08);
}

body.light-mode .nav-item.active {
  background: rgba(59, 130, 246, 0.12);
  color: #1d4ed8;
}

body.light-mode .topbar {
  background: rgba(255, 255, 255, 0.85);
  border-bottom-color: #e5e7eb;
}

body.light-mode .topbar-time {
  background: #e5e7eb;
}

body.light-mode tr:hover td {
  background: rgba(59, 130, 246, 0.04);
}

body.light-mode .btn-outline {
  border-color: #d1d5db;
}

body.light-mode .btn-outline:hover {
  border-color: var(--accent);
  color: var(--accent);
}

body.light-mode input.form-control {
  background-color: #ffffff !important;
  border-color: #d1d5db !important;
  color: #1f2937 !important;
}

body.light-mode input.form-control:focus {
  border-color: var(--accent) !important;
}

body.light-mode .badge-green { background: rgba(16, 185, 129, 0.15); color: #059669; }
body.light-mode .badge-blue { background: rgba(59, 130, 246, 0.15); color: #1d4ed8; }
body.light-mode .badge-yellow { background: rgba(245, 158, 11, 0.15); color: #d97706; }
body.light-mode .badge-red { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
body.light-mode .badge-gray { background: rgba(107, 114, 128, 0.15); color: #4b5563; }

body.light-mode .slot-tersedia { background: rgba(16, 185, 129, 0.12); color: #15803d; border-color: rgba(16, 185, 129, 0.25); }
body.light-mode .slot-terisi { background: rgba(239, 68, 68, 0.12); color: #b91c1c; border-color: rgba(239, 68, 68, 0.25); }
body.light-mode .slot-maintenance { background: rgba(107, 114, 128, 0.12); color: #4b5563; border-color: rgba(107, 114, 128, 0.25); }

/* BACKGROUND ORBS */
.bg-glow-orb {
  position: fixed;
  border-radius: 50%;
  filter: blur(80px);
  z-index: -1;
  pointer-events: none;
  opacity: 0.15;
  transition: opacity 0.5s ease, filter 0.5s ease;
}
.bg-glow-orb-1 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
  top: -100px;
  left: -100px;
  animation: float-blob 22s infinite alternate ease-in-out;
}
.bg-glow-orb-2 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, var(--accent2) 0%, transparent 70%);
  bottom: -150px;
  right: -100px;
  animation: float-blob 28s infinite alternate-reverse ease-in-out;
}

body.light-mode .bg-glow-orb {
  opacity: 0.06;
  filter: blur(100px);
}

@keyframes float-blob {
  0% {
    transform: translate(0, 0) scale(1) rotate(0deg);
  }
  33% {
    transform: translate(70px, 40px) scale(1.15) rotate(120deg);
  }
  66% {
    transform: translate(-50px, 80px) scale(0.9) rotate(240deg);
  }
  100% {
    transform: translate(0, 0) scale(1) rotate(360deg);
  }
}

/* ENTRY ANIMATIONS */
.fade-in-up {
  animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* PULSING BADGE / DOT */
.badge-pulse {
  display: inline-block;
  width: 8px;
  height: 8px;
  background-color: var(--success);
  border-radius: 50%;
  margin-left: 6px;
  vertical-align: middle;
  box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7);
  animation: pulse-glow 2s infinite;
}
@keyframes pulse-glow {
  0% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7);
  }
  70% {
    transform: scale(1);
    box-shadow: 0 0 0 8px rgba(52, 211, 153, 0);
  }
  100% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(52, 211, 153, 0);
  }
}

/* GLOWING ACTIVE PARKING BORDER */
.active-parking-glow {
  position: relative;
  border: none !important;
  background: var(--surface) !important;
  border-radius: 16px !important;
  z-index: 1;
  overflow: hidden;
  padding: 2px !important;
}

.active-parking-glow::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: conic-gradient(
    from 0deg,
    transparent 20%,
    var(--accent) 40%,
    var(--accent2) 60%,
    transparent 80%
  );
  animation: border-gradient-shift 4s linear infinite;
  z-index: -2;
}

.active-parking-glow::after {
  content: '';
  position: absolute;
  inset: 1px;
  background: var(--bg);
  border-radius: 15px;
  z-index: -1;
  transition: background 0.3s;
}
body.light-mode .active-parking-glow::after {
  background: #ffffff;
}

.active-parking-glow-content {
  padding: 20px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  border-radius: 15px;
  width: 100%;
  height: 100%;
}

@keyframes border-gradient-shift {
  100% {
    transform: rotate(360deg);
  }
}

/* SIDEBAR HOVER LINE INDICATOR */
.nav-item {
  position: relative;
  overflow: hidden;
}
.nav-item::after {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 0%;
  background: linear-gradient(to bottom, var(--accent), var(--accent2));
  border-radius: 0 4px 4px 0;
  transition: height 0.25s ease;
}
.nav-item:hover::after, .nav-item.active::after {
  height: 60%;
}
.nav-item:hover .nav-icon {
  animation: wiggle-icon 0.4s ease;
}
@keyframes wiggle-icon {
  0% { transform: scale(1); }
  50% { transform: scale(1.15) rotate(8deg); }
  100% { transform: scale(1) rotate(0deg); }
}

/* CARD STAT HOVER GLOW */
.stat-card {
  position: relative;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
}
.stat-card::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 14px;
  padding: 1px;
  background: linear-gradient(135deg, rgba(255,255,255,0.08), transparent);
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  transition: background 0.3s;
}
.stat-card:hover::before {
  background: linear-gradient(135deg, var(--accent), var(--accent2));
}

/* CARD SHEEN */
.stat-card, .pk-location-card {
  position: relative;
  overflow: hidden;
}
.stat-card::after, .pk-location-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: -150%;
  width: 50%;
  height: 100%;
  background: linear-gradient(
    to right,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.08) 50%,
    rgba(255, 255, 255, 0) 100%
  );
  transform: skewX(-25deg);
  transition: none;
  pointer-events: none;
}
body.light-mode .stat-card::after, body.light-mode .pk-location-card::after {
  background: linear-gradient(
    to right,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.3) 50%,
    rgba(255, 255, 255, 0) 100%
  );
}
.stat-card:hover::after, .pk-location-card:hover::after {
  animation: shine-sheen 0.75s ease-in-out;
}
@keyframes shine-sheen {
  100% {
    left: 150%;
  }
}

/* TOAST SYSTEM */
.toast-item {
  background: rgba(15, 23, 42, 0.7) !important;
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  padding: 14px 18px;
  color: var(--text);
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
  min-width: 300px;
  max-width: 400px;
  display: flex;
  align-items: center;
  gap: 12px;
  pointer-events: auto;
  position: relative;
  overflow: hidden;
  transform: translateX(120%);
  transition: transform 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s;
  opacity: 0;
}
.toast-item.show {
  transform: translateX(0);
  opacity: 1;
}
body.light-mode .toast-item {
  background: rgba(255, 255, 255, 0.92) !important;
  border-color: rgba(99, 102, 241, 0.15);
  box-shadow: 0 10px 30px rgba(99, 102, 241, 0.08);
}
.toast-icon {
  font-size: 20px;
  flex-shrink: 0;
}
.toast-content {
  font-size: 13px;
  font-weight: 600;
  flex-grow: 1;
}
.toast-close {
  cursor: pointer;
  opacity: 0.6;
  font-size: 18px;
  transition: opacity 0.2s;
  user-select: none;
}
.toast-close:hover {
  opacity: 1;
}
.toast-progress {
  width: 100%;
}

/* GLOBAL TRANSITIONS */
body, .main, .content, .sidebar, .topbar, .stat-card, .table-card, .btn, .form-control {
  transition: background-color 0.4s ease, color 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease !important;
}

/* SPRING BOUNCE MODALS */
.pk-modal-overlay.open .pk-modal {
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

/* WEEKLY CHART WIDGET */
.chart-widget {
  background: var(--surface);
  backdrop-filter: blur(12px);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 22px 24px;
  margin-bottom: 24px;
  flex: 1;
}
.chart-container {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  height: 180px;
  padding-top: 20px;
  gap: 12px;
}
.chart-bar-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
}
.chart-bar-wrap {
  width: 100%;
  background: rgba(255, 255, 255, 0.04);
  border-radius: 6px;
  height: 130px;
  display: flex;
  align-items: flex-end;
  overflow: hidden;
  position: relative;
}
body.light-mode .chart-bar-wrap {
  background: rgba(99, 102, 241, 0.05);
}
.chart-bar {
  width: 100%;
  background: linear-gradient(to top, var(--accent), var(--accent2));
  border-radius: 6px;
  height: 0;
  transition: height 1.2s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
}
.chart-bar-val {
  font-size: 10px;
  font-weight: 700;
  color: var(--text-muted);
  margin-bottom: 4px;
}
.chart-label {
  font-size: 11px;
  color: var(--text-muted);
  margin-top: 8px;
  font-weight: 500;
}

/* MOBILE RESPONSIVENESS */
.mobile-menu-toggle,
.sidebar-overlay {
 display: none;
}

@media (max-width: 900px) {
 body {
 display: block;
 min-width: 0;
 }

 .mobile-menu-toggle {
 position: fixed;
 top: 12px;
 left: 12px;
 z-index: 220;
 width: 42px;
 height: 42px;
 border: 1px solid var(--border);
 border-radius: 12px;
 background: var(--surface);
 color: var(--text);
 box-shadow: 0 10px 28px rgba(42,26,71,0.12);
 display: inline-flex;
 align-items: center;
 justify-content: center;
 font-size: 22px;
 cursor: pointer;
 }

 .mobile-menu-toggle::before {
   content: '';
   width: 18px;
   height: 2px;
   background: currentColor;
   border-radius: 2px;
   box-shadow: 0 6px 0 currentColor, 0 -6px 0 currentColor;
 }

 .sidebar {
 transform: translateX(-100%);
 box-shadow: 18px 0 42px rgba(42,26,71,0.16);
 }

 body.sidebar-open .sidebar {
 transform: translateX(0);
 }

 .sidebar-overlay {
 position: fixed;
 inset: 0;
 z-index: 90;
 background: rgba(42,26,71,0.35);
 }

 body.sidebar-open .sidebar-overlay {
 display: block;
 }

 .main {
 margin-left: 0;
 min-width: 0;
 }

 .topbar {
 padding: 14px 16px 14px 66px;
 min-height: 66px;
 gap: 12px;
 }

 .topbar-right {
 gap: 8px;
 flex-wrap: wrap;
 justify-content: flex-end;
 }

 .page-title {
 font-size: 17px;
 }

 .content {
 padding: 18px 14px 28px;
 }

 .stat-grid {
 grid-template-columns: repeat(2, minmax(0, 1fr));
 gap: 12px;
 }

 .stat-card,
 .form-card {
 border-radius: 12px;
 padding: 16px;
 }

 .table-card {
 border-radius: 12px;
 }

 .table-header {
 align-items: flex-start;
 flex-direction: column;
 gap: 12px;
 padding: 16px;
 }

 table {
 min-width: 680px;
 }

 th,
 td {
 padding: 11px 14px;
 }

 .form-grid {
 grid-template-columns: 1fr;
 }

 .slot-grid {
 display: grid;
 grid-template-columns: repeat(auto-fill, minmax(64px, 1fr));
 }

 .slot-item {
 width: 100%;
 min-height: 78px;
 }
}

@media (max-width: 560px) {
 .topbar {
 align-items: flex-start;
 flex-direction: column;
 }

 .topbar-right,
 .topbar-time,
 .btn {
 width: 100%;
 }

 .btn {
 justify-content: center;
 }

 .stat-grid {
 grid-template-columns: 1fr;
 }

 .stat-value {
 font-size: 24px;
 }

 .content > div[style*="display:flex"],
 .content form[style*="display:flex"] {
 flex-direction: column !important;
 align-items: stretch !important;
 }

 .content div[style*="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))"] {
 grid-template-columns: 1fr !important;
 }

 table {
 min-width: 620px;
 }
}
</style>
<script>
window.SParkingIcons = window.SParkingIcons || {
  svg: {
    parking: '<svg class="minimal-icon" viewBox="0 0 24 24"><path d="M8 19V5h6a4 4 0 0 1 0 8H8"/><path d="M8 13h6"/></svg>',
    home: '<svg class="minimal-icon" viewBox="0 0 24 24"><path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M10 20v-5h4v5"/></svg>',
    map: '<svg class="minimal-icon" viewBox="0 0 24 24"><path d="M4 6.5 9 4l6 2.5 5-2.5v13.5l-5 2.5-6-2.5-5 2.5V6.5Z"/><path d="M9 4v13.5"/><path d="M15 6.5V20"/></svg>',
    list: '<svg class="minimal-icon" viewBox="0 0 24 24"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>',
    qr: '<svg class="minimal-icon" viewBox="0 0 24 24"><path d="M4 4h6v6H4z"/><path d="M14 4h6v6h-6z"/><path d="M4 14h6v6H4z"/><path d="M14 14h2"/><path d="M18 14h2v2"/><path d="M14 18h6"/></svg>',
    wallet: '<svg class="minimal-icon" viewBox="0 0 24 24"><path d="M4 7h16v12H4z"/><path d="M16 11h4v4h-4z"/><path d="M4 7l3-3h10l3 3"/></svg>',
    user: '<svg class="minimal-icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
    logout: '<svg class="minimal-icon" viewBox="0 0 24 24"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M14 4h6v16h-6"/></svg>',
    chart: '<svg class="minimal-icon" viewBox="0 0 24 24"><path d="M4 19h16"/><path d="M7 16V9"/><path d="M12 16V5"/><path d="M17 16v-4"/></svg>',
    car: '<svg class="minimal-icon" viewBox="0 0 24 24"><path d="M5 14l2-5h10l2 5"/><path d="M4 14h16v5H4z"/><circle cx="7" cy="19" r="1"/><circle cx="17" cy="19" r="1"/></svg>',
    bike: '<svg class="minimal-icon" viewBox="0 0 24 24"><circle cx="6" cy="17" r="3"/><circle cx="18" cy="17" r="3"/><path d="M6 17l5-8h3l4 8"/><path d="M10 9H8"/><path d="M13 9l-2 8"/></svg>',
    building: '<svg class="minimal-icon" viewBox="0 0 24 24"><path d="M4 21V5h16v16"/><path d="M8 9h2"/><path d="M14 9h2"/><path d="M8 13h2"/><path d="M14 13h2"/><path d="M10 21v-4h4v4"/></svg>',
    clock: '<svg class="minimal-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
    scan: '<svg class="minimal-icon" viewBox="0 0 24 24"><path d="M4 7V4h3"/><path d="M17 4h3v3"/><path d="M20 17v3h-3"/><path d="M7 20H4v-3"/><path d="M7 12h10"/></svg>'
  },
  pick: function (text, el) {
    text = (text || '').toLowerCase();
    if (el.classList.contains('brand-icon')) return 'parking';
    if (text.includes('dashboard') || text.includes('home')) return 'home';
    if (text.includes('slot') || text.includes('lokasi')) return 'map';
    if (text.includes('scan') || text.includes('qr')) return 'scan';
    if (text.includes('bayar') || text.includes('tarif') || text.includes('pendapatan')) return 'wallet';
    if (text.includes('pengguna') || text.includes('petugas') || text.includes('profil')) return 'user';
    if (text.includes('logout')) return 'logout';
    if (text.includes('laporan') || text.includes('transaksi') || text.includes('total')) return 'chart';
    if (text.includes('motor')) return 'bike';
    if (text.includes('mobil') || text.includes('kendaraan') || text.includes('parkir')) return 'car';
    return 'parking';
  },
  hydrate: function () {
    document.querySelectorAll('.brand-icon,.nav-icon,.stat-icon,.mgmt-sum-icon,.mgmt-area-icon,.mgmt-empty-icon,.sp-sum-icon,.sp-area-icon,.sp-slot-icon,.sp-empty-icon').forEach(function (el) {
      if (el.querySelector('svg')) return;
      var text = (el.closest('a,button,.stat-card,.mgmt-sum-card,.mgmt-area,.sp-sum-card,.sp-area,.sp-slot') || el.parentElement || el).textContent || '';
      var key = (el.classList.contains('mgmt-area-icon') || el.classList.contains('sp-area-icon')) ? 'building' : SParkingIcons.pick(text, el);
      el.innerHTML = SParkingIcons.svg[key] || SParkingIcons.svg.parking;
    });
  }
};
document.addEventListener('DOMContentLoaded', window.SParkingIcons.hydrate);

// Theme Toggle Script
(function() {
  const theme = localStorage.getItem('theme') || 'dark';
  if (theme === 'light') {
    document.documentElement.classList.add('light-mode');
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Sync class to body
    if (localStorage.getItem('theme') === 'light') {
      document.body.classList.add('light-mode');
    }

    let topbarRight = document.querySelector('.topbar-right');
    const topbar = document.querySelector('.topbar');
    
    if (topbar && !topbarRight) {
      topbarRight = document.createElement('div');
      topbarRight.className = 'topbar-right';
      topbar.appendChild(topbarRight);
    }

    if (topbarRight) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'theme-toggle-btn';
      btn.style.background = 'none';
      btn.style.border = 'none';
      btn.style.color = 'var(--text-muted)';
      btn.style.cursor = 'pointer';
      btn.style.padding = '6px';
      btn.style.display = 'flex';
      btn.style.alignItems = 'center';
      btn.style.justifyContent = 'center';
      btn.style.borderRadius = '50%';
      btn.style.transition = 'color 0.2s, background-color 0.2s';
      btn.style.marginRight = '8px';
      
      const updateIcon = (isLight) => {
        btn.innerHTML = isLight 
          ? '<svg class="minimal-icon" style="width:18px;height:18px;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>'
          : '<svg class="minimal-icon" style="width:18px;height:18px;" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
      };

      updateIcon(localStorage.getItem('theme') === 'light');
      
      btn.addEventListener('mouseenter', () => {
        btn.style.backgroundColor = 'rgba(255,255,255,0.08)';
        btn.style.color = 'var(--text)';
      });
      btn.addEventListener('mouseleave', () => {
        btn.style.backgroundColor = 'transparent';
        btn.style.color = 'var(--text-muted)';
      });

      btn.addEventListener('click', () => {
        const isLight = document.body.classList.toggle('light-mode');
        if (isLight) {
          document.documentElement.classList.add('light-mode');
        } else {
          document.documentElement.classList.remove('light-mode');
        }
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
        updateIcon(isLight);
      });

      topbarRight.prepend(btn);
    }

    // Top Loading Progress Bar logic
    const bar = document.getElementById('topLoadingBar');
    if (bar) {
      bar.style.width = '40%';
      window.addEventListener('load', () => {
        bar.style.width = '100%';
        setTimeout(() => {
          bar.style.opacity = '0';
        }, 300);
      });
    }

    // Global Toast Notification trigger
    window.showToast = function(message, type = 'success') {
      const container = document.getElementById('toastContainer');
      if (!container) return;
      
      const toast = document.createElement('div');
      toast.className = 'toast-item';
      
      let icon = 'ℹ️';
      let progressColor = 'var(--accent)';
      if (type === 'success') {
        icon = '✅';
        progressColor = 'var(--success)';
      } else if (type === 'error' || type === 'danger') {
        icon = '❌';
        progressColor = 'var(--danger)';
      } else if (type === 'warning') {
        icon = '⚠️';
        progressColor = 'var(--warning)';
      }
      
      toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-content">${message}</div>
        <div class="toast-close">&times;</div>
        <div class="toast-progress" style="background:${progressColor};"></div>
      `;
      
      container.appendChild(toast);
      
      setTimeout(() => {
        toast.classList.add('show');
      }, 10);
      
      toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 350);
      });
      
      const progress = toast.querySelector('.toast-progress');
      let start = Date.now();
      const duration = 4000;
      const interval = setInterval(() => {
        const elapsed = Date.now() - start;
        const pct = Math.max(0, 100 - (elapsed / duration) * 100);
        progress.style.width = pct + '%';
        if (elapsed >= duration) {
          clearInterval(interval);
          toast.classList.remove('show');
          setTimeout(() => toast.remove(), 350);
        }
      }, 16);
    };

    // Chart Bar Growing animation on load
    setTimeout(() => {
      document.querySelectorAll('.chart-bar').forEach(bar => {
        const h = bar.getAttribute('data-height');
        if (h) bar.style.height = h;
      });
    }, 400);
  });
})();
</script>
</head>
<body>
<div id="topLoadingBar" style="position:fixed;top:0;left:0;height:3px;background:linear-gradient(to right, var(--accent), var(--accent2));z-index:99999;width:0%;transition:width 0.4s ease, opacity 0.4s ease;"></div>
<div id="toastContainer" style="position:fixed;top:20px;right:20px;z-index:100000;display:flex;flex-direction:column;gap:10px;pointer-events:none;"></div>
<div class="bg-glow-orb bg-glow-orb-1"></div>
<div class="bg-glow-orb bg-glow-orb-2"></div>
