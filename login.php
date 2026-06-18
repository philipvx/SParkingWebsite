<?php
require_once 'includes/config.php';

$error = '';
$username_value = $_POST['username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $logged_in = false;

  if ($username === '' || $password === '') {
    $error = 'Username dan password wajib diisi.';
  } else {
    $stmt = $conn->prepare("SELECT id_akun, username, password_hash, role, status_login FROM akun_pengguna WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
      if ($row['status_login'] === 'nonaktif') {
        $error = 'Akun Anda tidak aktif.';
      } elseif (password_verify($password, $row['password_hash'])) {
        $id_akun = $row['id_akun'];
        $_SESSION['user_id'] = $id_akun;
        $_SESSION['role'] = $row['role'];
        
        // Update terakhir login
        $conn->query("UPDATE akun_pengguna SET terakhir_login = NOW() WHERE id_akun = $id_akun");
        
        // Fetch details based on role
        if ($row['role'] === 'pengguna') {
          $p_stmt = $conn->prepare("SELECT nama, email FROM pengguna_parkir WHERE id_pengguna = ?");
          $p_stmt->bind_param("i", $id_akun);
          $p_stmt->execute();
          $p_row = $p_stmt->get_result()->fetch_assoc();
          $_SESSION['nama'] = $p_row ? $p_row['nama'] : 'Pengguna';
          $_SESSION['email'] = $p_row ? $p_row['email'] : '';
          $logged_in = true;
          redirect('pages/user/dashboard.php');
        } elseif ($row['role'] === 'petugas_keluar') {
          $p_stmt = $conn->prepare("SELECT nama_petugas FROM petugas_loket_keluar WHERE id_petugas = ?");
          $p_stmt->bind_param("i", $id_akun);
          $p_stmt->execute();
          $p_row = $p_stmt->get_result()->fetch_assoc();
          $_SESSION['nama'] = $p_row ? $p_row['nama_petugas'] : 'Petugas';
          $logged_in = true;
          redirect('pages/petugas/dashboard.php');
        } elseif ($row['role'] === 'kepala_loket') {
          $p_stmt = $conn->prepare("SELECT nama_kepala FROM kepala_loket_parkir WHERE id_kepala = ?");
          $p_stmt->bind_param("i", $id_akun);
          $p_stmt->execute();
          $p_row = $p_stmt->get_result()->fetch_assoc();
          $_SESSION['nama'] = $p_row ? $p_row['nama_kepala'] : 'Kepala';
          $logged_in = true;
          redirect('pages/kepala/dashboard.php');
        }
      }
    }

    if (!$logged_in && $error === '') {
      $error = 'Username atau password salah.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Login — SParking UTN</title>
 <script>
    (function() {
      const theme = localStorage.getItem('theme') || 'dark';
      if (theme === 'light') {
        document.documentElement.classList.add('light-mode');
      }
    })();
  </script>
 <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
 <style>
 *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
 html { min-height: 100%; }

 :root {
 --bg: #0f172a;
 --surface: rgba(255,255,255,0.08);
 --card-bg: rgba(255,255,255,0.1);
 --purple: #6366f1;
 --purple2: #8b5cf6;
 --purple-lt: rgba(99,102,241,.16);
 --text: #f8fafc;
 --muted: #94a3b8;
 --border: rgba(255,255,255,.14);
 --danger: #E05555;
 --danger-bg: rgba(224,85,85,.16);
 --radius: 22px;
 --shadow: 0 24px 70px rgba(0,0,0,.34);
 }

  body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #090d16 0%, #1a103c 30%, #052e3d 70%, #090d16 100%);
    background-size: 300% 300%;
    animation: gradientShift 12s ease infinite;
    min-height: 100vh;
    min-height: 100dvh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(14px, 3vw, 24px);
    position: relative;
    overflow-x: hidden;
    overflow-y: auto;
  }

  @keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  /* ── decorative blobs ── */
  body::before, body::after {
    content: '';
    position: fixed;
    border-radius: 50%;
    pointer-events: none;
    z-index: 0;
    filter: blur(50px);
    opacity: 0.9;
    transition: opacity 0.4s, filter 0.4s;
  }
  body::before {
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.75) 0%, rgba(139, 92, 246, 0.45) 50%, transparent 70%);
    top: -150px; left: -100px;
    animation: floatBlob1 14s ease-in-out infinite alternate;
  }
  body::after {
    width: 450px; height: 450px;
    background: radial-gradient(circle, rgba(236, 72, 153, 0.7) 0%, rgba(139, 92, 246, 0.4) 60%, transparent 70%);
    bottom: -100px; right: -50px;
    animation: floatBlob2 16s ease-in-out infinite alternate;
  }
  body.light-mode::before, body.light-mode::after {
    opacity: 0.45;
    filter: blur(75px);
  }

  @keyframes floatBlob1 {
    0% { transform: translate(0px, 0px) scale(1) rotate(0deg); }
    50% { transform: translate(180px, 120px) scale(1.25) rotate(180deg); }
    100% { transform: translate(-80px, -100px) scale(0.85) rotate(360deg); }
  }
  @keyframes floatBlob2 {
    0% { transform: translate(0px, 0px) scale(1) rotate(0deg); }
    50% { transform: translate(-160px, -120px) scale(0.85) rotate(-180deg); }
    100% { transform: translate(120px, 100px) scale(1.2) rotate(-360deg); }
  }


 /* subtle dot grid */
 .grid-bg {
 position: fixed; inset: 0; z-index: 0; pointer-events: none;
 background-image: radial-gradient(rgba(124,92,191,.13) 1px, transparent 1px);
 background-size: 28px 28px;
 }

 /* ── layout ── */
 .login-shell {
 position: relative; z-index: 1;
 display: grid;
 grid-template-columns: minmax(280px, .95fr) minmax(340px, 1.05fr);
 max-width: 920px; width: 100%;
 background: var(--surface);
 backdrop-filter: blur(16px);
 border: 1px solid var(--border);
 border-radius: var(--radius);
 box-shadow: var(--shadow);
 overflow: hidden;
 animation: fadeUp .5s ease both;
 }
 @keyframes fadeUp {
 from { opacity: 0; transform: translateY(24px); }
 to { opacity: 1; transform: translateY(0); }
 }
 /* ── left decorative panel ── */
 .login-left {
 background: linear-gradient(160deg, rgba(99,102,241,.9) 0%, rgba(79,70,229,.7) 100%);
 padding: 48px 40px;
 display: flex; flex-direction: column;
 justify-content: space-between;
 color: #fff; position: relative; overflow: hidden;
 min-height: 560px;
 }
 .login-left::before {
 content: '';
 position: absolute; inset: 0;
 background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='28'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
 }
 .left-logo {
 position: relative; z-index: 1;
 }
  .left-logos-container {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
  }
  .logo-wrapper {
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border-radius: 5px;
  transition: transform 0.2s ease, filter 0.2s ease, opacity 0.2s ease;
  }
  .logo-wrapper:hover {
  transform: scale(1.08);
  opacity: 0.9;
  filter: brightness(1.1);
  }
  .logo-utn {
  width: 100%;
  height: 100%;
  object-fit: contain;
  mix-blend-mode: multiply;
  }
  .logo-sparking {
  width: 100%;
  height: 100%;
  object-fit: contain;
  mix-blend-mode: screen;
  }
 .left-logo h1 {
 font-family: 'Poppins', sans-serif;
 font-size: 26px; font-weight: 800;
 letter-spacing: -.4px; line-height: 1.1;
 }
 .left-logo p {
 font-size: 13px; opacity: .72; margin-top: 8px; line-height: 1.5;
 }
 .left-features { position: relative; z-index: 1; }
 .left-feature {
 display: flex; align-items: center; gap: 12px;
 margin-bottom: 16px;
 }
 .left-feature-icon {
 width: 36px; height: 36px; border-radius: 10px;
 background: rgba(255,255,255,.15);
 display: flex; align-items: center; justify-content: center;
 font-size: 16px; flex-shrink: 0;
 }
 .left-feature-text { font-size: 13px; opacity: .85; line-height: 1.4; }
 .left-feature-text strong { display: block; font-size: 13.5px; opacity: 1; margin-bottom: 1px; }

 .icon-svg {
 width: 18px;
 height: 18px;
 display: block;
 fill: none;
 stroke: currentColor;
 stroke-width: 1.8;
 stroke-linecap: round;
 stroke-linejoin: round;
 }
 .left-logo-icon .icon-svg,
 .mobile-brand-icon .icon-svg {
 width: 24px;
 height: 24px;
 }
 .left-feature-icon .icon-svg {
 width: 17px;
 height: 17px;
 }

 /* blob accents inside left */
 .left-blob {
 position: absolute; border-radius: 50%; pointer-events: none;
 background: rgba(255,255,255,.07);
 }
 .left-blob-1 { width: 220px; height: 220px; bottom: -60px; right: -60px; }
 .left-blob-2 { width: 120px; height: 120px; top: 40px; right: 20px; }

 /* ── right form panel ── */
 .login-right {
 padding: 48px 40px;
 display: flex; flex-direction: column; justify-content: center;
 min-width: 0;
 }
 .mobile-brand {
 display: none;
 align-items: center;
 gap: 12px;
 margin-bottom: 24px;
 }
 .mobile-brand-icon {
 width: 44px; height: 44px; border-radius: 14px;
 background: linear-gradient(135deg, var(--purple), var(--purple2));
 color: #fff;
 display: flex; align-items: center; justify-content: center;
 font-size: 0;
 flex-shrink: 0;
 }
 .mobile-brand-icon::before {
 content: '';
 width: 20px;
 height: 20px;
 border: 2px solid currentColor;
 border-radius: 4px;
 border-right-width: 0;
 box-shadow: 7px 0 0 -1px currentColor;
 }
 .mobile-brand-title {
 font-family: 'Poppins', sans-serif;
 font-size: 18px;
 font-weight: 800;
 color: var(--text);
 line-height: 1.1;
 }
 .mobile-brand-subtitle {
 font-size: 12px;
 color: var(--muted);
 margin-top: 2px;
 }
 .form-heading {
 font-family: 'Poppins', sans-serif;
 font-size: 22px; font-weight: 700;
 color: var(--text); margin-bottom: 4px;
 }
 .form-heading {
 font-size: 0;
 }
 .form-heading::before {
 content: 'Selamat datang';
 font-size: 22px;
 }
 .form-subheading {
 font-size: 13px; color: var(--muted); margin-bottom: 28px;
 }

 /* ── role tabs ── */
 .role-tabs {
 display: grid; grid-template-columns: 1fr 1fr 1fr;
 gap: 5px; background: var(--card-bg);
 border-radius: 14px; padding: 5px; margin-bottom: 26px;
 border: 1px solid var(--border);
 display: none;
 }
 .role-tab {
 padding: 9px 4px; text-align: center;
 font-size: 12px; font-weight: 600;
 color: var(--muted); border-radius: 10px;
 cursor: pointer; transition: all .2s;
 user-select: none; line-height: 1.3;
 overflow-wrap: anywhere;
 }
 .role-tab span { display: block; font-size: 16px; margin-bottom: 2px; }
 .role-tab.active {
 background: var(--surface);
 color: var(--purple);
 box-shadow: 0 2px 10px rgba(124,92,191,.15);
 }
 .role-tab:hover:not(.active) { color: var(--text); }

 /* ── form fields ── */
 .field { margin-bottom: 18px; }
 .field-label {
 display: block; font-size: 11.5px; font-weight: 700;
 letter-spacing: .5px; text-transform: uppercase;
 color: var(--muted); margin-bottom: 7px;
 }
 .field-wrap { position: relative; }
 .field-icon {
 position: absolute; left: 15px; top: 50%;
 transform: translateY(-50%); font-size: 15px;
 pointer-events: none;
 color: var(--muted);
 width: 18px;
 height: 18px;
 font-size: 0;
 }
 .field-icon::before {
 content: '';
 position: absolute;
 inset: 2px 3px;
 border: 1.7px solid currentColor;
 border-radius: 2px;
 }
 .field-icon::after {
 content: '';
 position: absolute;
 left: 4px;
 right: 4px;
 top: 6px;
 height: 7px;
 border-left: 1.7px solid currentColor;
 border-bottom: 1.7px solid currentColor;
 transform: rotate(-45deg);
 }
 .field-icon-lock::before {
 inset: 7px 2px 1px;
 border-radius: 3px;
 }
 .field-icon-lock::after {
 left: 5px;
 right: 5px;
 top: 1px;
 height: 9px;
 border: 1.7px solid currentColor;
 border-bottom: 0;
 border-radius: 8px 8px 0 0;
 transform: none;
 }
 .field-input {
 width: 100%; padding: 13px 16px 13px 42px;
 background: rgba(255,255,255,0.08);
 border: 1.5px solid rgba(255,255,255,0.16);
 border-radius: 13px; font-family: 'Inter', sans-serif;
 font-size: 14.5px; color: var(--text); outline: none;
 transition: border-color .2s, box-shadow .2s;
 }
 .field-input:focus {
 border-color: var(--purple);
 box-shadow: 0 0 0 3px rgba(124,92,191,.12);
 background: rgba(255,255,255,0.12);
 }
 .field-input::placeholder { color: #BEB8CC; }
 .eye-btn {
 position: absolute; right: 14px; top: 50%;
 transform: translateY(-50%); background: none;
 border: none; color: var(--muted); cursor: pointer;
 font-size: 15px; padding: 4px; line-height: 1;
 width: 28px;
 height: 28px;
 font-size: 0;
 }
 .eye-btn::before {
 content: '';
 position: absolute;
 left: 5px;
 top: 8px;
 width: 18px;
 height: 12px;
 border: 1.7px solid currentColor;
 border-radius: 50%;
 }
 .eye-btn::after {
 content: '';
 position: absolute;
 left: 12px;
 top: 12px;
 width: 4px;
 height: 4px;
 border-radius: 50%;
 background: currentColor;
 }
 .eye-btn:hover { color: var(--purple); }

 /* ── alert ── */
 .alert-err {
 background: var(--danger-bg);
 border: 1px solid rgba(224,85,85,.25);
 border-radius: 11px; padding: 11px 14px;
 font-size: 13px; color: var(--danger);
 margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
 }

 /* ── submit button ── */
 .btn-submit {
 width: 100%; padding: 15px;
 background: linear-gradient(135deg, var(--purple) 0%, var(--purple2) 100%);
 color: #fff; border: none; border-radius: 14px;
 font-family: 'Poppins', sans-serif;
 font-size: 15px; font-weight: 700;
 letter-spacing: .3px; cursor: pointer;
 transition: transform .18s, box-shadow .18s;
 box-shadow: 0 10px 30px rgba(124,92,191,.3);
 margin-top: 6px;
 }
 .btn-submit:hover {
 transform: translateY(-2px);
 box-shadow: 0 14px 36px rgba(124,92,191,.38);
 }
 .btn-submit:active { transform: translateY(0); }

 /* ── register link ── */
 .register-row {
 text-align: center; margin-top: 22px;
 font-size: 13px; color: var(--muted);
 }
 .register-row a {
 color: var(--purple); font-weight: 600;
 text-decoration: none;
 }
 .register-row a:hover { text-decoration: underline; }

 @media (max-width: 820px) {
 body {
 align-items: flex-start;
 }

 body::before {
 width: 360px; height: 360px;
 top: -140px; left: -150px;
 }

 body::after {
 width: 280px; height: 280px;
 bottom: -120px; right: -120px;
 }

 .login-shell {
 grid-template-columns: 1fr;
 max-width: 480px;
 border-radius: 18px;
 }

 .login-left {
 display: none;
 }

 .login-right {
 padding: 30px 24px;
 }

 .mobile-brand {
 display: flex;
 }

 .form-heading {
 font-size: 0;
 }

 .form-heading::before {
 font-size: 21px;
 }

 .form-subheading {
 margin-bottom: 22px;
 }
 }

 @media (max-width: 430px) {
 body {
 padding: 12px;
 }

 .login-shell {
 border-radius: 16px;
 }

 .login-right {
 padding: 24px 16px;
 }

 .mobile-brand {
 margin-bottom: 20px;
 }

 .role-tabs {
 gap: 4px;
 padding: 4px;
 }

 .role-tab {
 font-size: 11px;
 padding: 9px 2px;
 }

 .role-tab span {
 font-size: 15px;
 }

 .field-input {
 font-size: 14px;
 }
 }

 @media (max-width: 340px) {
 .role-tabs {
 grid-template-columns: 1fr;
 }

 .role-tab {
 display: flex;
 align-items: center;
 justify-content: center;
 gap: 8px;
 }

 .role-tab span {
 margin-bottom: 0;
 }
 }

 /* ── LIGHT MODE STYLES ── */
 .light-mode, body.light-mode {
   --bg: #f3f4f6;
   --surface: #ffffff;
   --card-bg: rgba(255,255,255,0.85);
   --purple: #3b82f6;
   --purple2: #2563eb;
   --text: #1f2937;
   --muted: #6b7280;
   --border: rgba(99, 102, 241, 0.15);
   --shadow: 0 24px 70px rgba(99, 102, 241, 0.08);
 }

   .light-mode body, body.light-mode {
     background: linear-gradient(135deg, #f3f4f6 0%, #dbeafe 35%, #fbcfe8 70%, #f3f4f6 100%) !important;
     background-size: 300% 300% !important;
     animation: gradientShift 12s ease infinite !important;
     color: var(--text) !important;
   }

 body.light-mode .grid-bg {
   background-image: radial-gradient(rgba(59, 130, 246, 0.08) 1px, transparent 1px);
 }

 body.light-mode .field-input {
   background-color: #ffffff !important;
   border-color: #d1d5db !important;
   color: #1f2937 !important;
 }
 body.light-mode .field-input::placeholder {
   color: #9ca3af !important;
 }
  </style>
</head>
<body>
  <div class="grid-bg"></div>
  <button id="themeToggleBtn" class="theme-toggle-btn no-print" style="position:fixed; top:20px; right:20px; z-index:999; background:var(--surface); border:1px solid var(--border); color:var(--text); width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 4px 12px rgba(0,0,0,0.15); backdrop-filter:blur(8px); transition: background-color 0.2s, border-color 0.2s; outline:none;" title="Ganti Tema">
  </button>

  <div class="login-shell">

  <!-- ── Left decorative panel ── -->
  <div class="login-left">
  <div class="left-blob left-blob-1"></div>
  <div class="left-blob left-blob-2"></div>
  
  <div class="left-logo">
  <div class="left-logos-container">
    <a href="index.php" class="logo-wrapper" title="Kembali ke Halaman Utama">
      <img src="logo_utn.jpg" alt="Logo UTN" class="logo-utn">
    </a>
    <a href="index.php" class="logo-wrapper" title="Kembali ke Halaman Utama">
      <img src="logo_sparking.jpg" alt="Logo SParking" class="logo-sparking">
    </a>
  </div>
 <h1>SParking<br>UTN</h1>
 <p>Sistem Informasi Parkir<br>Terintegrasi Universitas</p>
 </div>

 <div class="left-features">
  <div class="left-feature">
  <div class="left-feature-icon">
  <svg class="icon-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6.5 9 4l6 2.5 5-2.5v13.5l-5 2.5-6-2.5-5 2.5V6.5Z"/><path d="M9 4v13.5"/><path d="M15 6.5V20"/></svg>
  </div>
  <div class="left-feature-text">
  <strong>Slot Real-time</strong>
  Pantau ketersediaan lokasi parkir secara langsung
  </div>
  </div>
  <div class="left-feature">
  <div class="left-feature-icon">
  <svg class="icon-svg" viewBox="0 0 24 24" aria-hidden="true"><rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 17h2"/></svg>
  </div>
  <div class="left-feature-text">
  <strong>QR Keluar Instan</strong>
  Proses keluar cepat dengan scan QR Code
  </div>
  </div>
  <div class="left-feature">
  <div class="left-feature-icon">
  <svg class="icon-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16"/><path d="M7 16V9"/><path d="M12 16V5"/><path d="M17 16v-4"/></svg>
  </div>
  <div class="left-feature-text">
  <strong>Laporan Terintegrasi</strong>
  Data transaksi & statistik parkir lengkap
  </div>
  </div>
 </div>
  </div>

  <!-- ── Right form panel ── -->
  <div class="login-right">
  <div class="mobile-brand">
         <div class="mobile-brand-icon"></div>
  <div>
  <div class="mobile-brand-title">SParking UTN</div>
  <div class="mobile-brand-subtitle">Sistem Informasi Parkir</div>
  </div>
  </div>
  <div class="form-heading">Selamat datang</div>
  <div class="form-subheading">Masuk dengan username akun Anda.</div>

  <form method="POST">
  <?php if ($error): ?>
  <div class="alert-err"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Username -->
  <div class="field">
  <label class="field-label" id="username_label">Username</label>
  <div class="field-wrap">
  <span class="field-icon" id="username_icon">
  </span>
  <input
  class="field-input"
  type="text" name="username" id="username_input"
  placeholder="Masukkan username"
  value="<?= htmlspecialchars($username_value) ?>"
  required
  >
  </div>
  </div>

  <!-- Password -->
  <div class="field">
  <label class="field-label">Password</label>
  <div class="field-wrap">
  <span class="field-icon field-icon-lock"></span>
  <input class="field-input" type="password" name="password" id="pwd"
  placeholder="Masukkan password" required style="padding-right:44px;">
  <button type="button" class="eye-btn" onclick="togglePwd()" title="Tampilkan password"></button>
  </div>
  </div>

  <button type="submit" class="btn-submit">Masuk ke SParking</button>
  </form>

  <div class="register-row">
  Belum punya akun? <a href="pages/user/register.php">Daftar di sini</a>
  </div>
  </div>

  </div><!-- /login-shell -->

  <script>
   function togglePwd() {
   const p = document.getElementById('pwd');
   p.type = p.type === 'password' ? 'text' : 'password';
   }

   // Theme Toggle Script
   (function() {
     document.addEventListener('DOMContentLoaded', () => {
       // Sync class to body
       if (localStorage.getItem('theme') === 'light') {
         document.body.classList.add('light-mode');
       }

       const btn = document.getElementById('themeToggleBtn');
       if (btn) {
         const updateIcon = (isLight) => {
           btn.innerHTML = isLight 
             ? '<svg class="icon-svg" style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>'
             : '<svg class="icon-svg" style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
         };
         updateIcon(localStorage.getItem('theme') === 'light');
         
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
       }
     });
   })();
   </script>
</body>
</html>
