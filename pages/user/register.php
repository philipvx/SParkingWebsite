<?php
require_once '../../includes/config.php';
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = trim($_POST['nama']);
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $konfirmasi = $_POST['konfirmasi'];
  $no_hp = trim($_POST['no_hp']);
  
  if ($password !== $konfirmasi) {
    $err = 'Password dan konfirmasi tidak cocok.';
  } elseif (strlen($password) < 6) {
    $err = 'Password minimal 6 karakter.';
  } else {
    $cek_user = $conn->prepare("SELECT id_akun FROM akun_pengguna WHERE username = ?");
    $cek_user->bind_param("s", $username);
    $cek_user->execute();
    $has_user = $cek_user->get_result()->num_rows > 0;

    $cek_email = $conn->prepare("SELECT id_pengguna FROM pengguna_parkir WHERE email = ?");
    $cek_email->bind_param("s", $email);
    $cek_email->execute();
    $has_email = $cek_email->get_result()->num_rows > 0;

    if ($has_user || $has_email) {
      $err = 'Email atau username sudah terdaftar.';
    } else {
      $conn->begin_transaction();
      try {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt_akun = $conn->prepare("INSERT INTO akun_pengguna (username, password_hash, role, status_login) VALUES (?, ?, 'pengguna', 'aktif')");
        $stmt_akun->bind_param("ss", $username, $hash);
        $stmt_akun->execute();
        $new_id = $conn->insert_id;
        
        $stmt_profile = $conn->prepare("INSERT INTO pengguna_parkir (id_pengguna, nama, email, no_hp) VALUES (?, ?, ?, ?)");
        $stmt_profile->bind_param("isss", $new_id, $nama, $email, $no_hp);
        $stmt_profile->execute();
        
        $conn->commit();
        $msg = 'Akun berhasil dibuat! Silakan login.';
      } catch (Exception $e) {
        $conn->rollback();
        $err = 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage();
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <script>
    (function() {
      const theme = localStorage.getItem('theme') || 'dark';
      if (theme === 'light') {
        document.documentElement.classList.add('light-mode');
      }
    })();
  </script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun — SParking UTN</title>
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
      --success: #10b981;
      --success-bg: rgba(16,185,129,.16);
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
    .register-shell {
      position: relative; z-index: 1;
      display: grid;
      grid-template-columns: minmax(280px, .95fr) minmax(340px, 1.05fr);
      max-width: 960px; width: 100%;
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
      min-height: 580px;
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
      padding: 40px;
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
    .form-subheading {
      font-size: 13px; color: var(--muted); margin-bottom: 24px;
    }

    /* ── form fields ── */
    .field { margin-bottom: 16px; }
    .field-label {
      display: block; font-size: 11px; font-weight: 700;
      letter-spacing: .5px; text-transform: uppercase;
      color: var(--muted); margin-bottom: 6px;
    }
    .field-wrap { position: relative; }
    .field-icon {
      position: absolute; left: 14px; top: 50%;
      transform: translateY(-50%);
      pointer-events: none;
      color: var(--muted);
      width: 18px;
      height: 18px;
    }
    .field-input {
      width: 100%; padding: 11px 16px 11px 40px;
      background: rgba(255,255,255,0.08);
      border: 1.5px solid rgba(255,255,255,0.16);
      border-radius: 12px; font-family: 'Inter', sans-serif;
      font-size: 14px; color: var(--text); outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .field-input:focus {
      border-color: var(--purple);
      box-shadow: 0 0 0 3px rgba(124,92,191,.12);
      background: rgba(255,255,255,0.12);
    }
    .field-input::placeholder { color: #BEB8CC; }

    /* grid layout for fields */
    .field-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    /* ── alerts ── */
    .alert-err {
      background: var(--danger-bg);
      border: 1px solid rgba(224,85,85,.25);
      border-radius: 11px; padding: 11px 14px;
      font-size: 13px; color: var(--danger);
      margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
    }
    .alert-succ {
      background: var(--success-bg);
      border: 1px solid rgba(16,185,129,.25);
      border-radius: 11px; padding: 11px 14px;
      font-size: 13px; color: var(--success);
      margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px;
    }

    /* ── submit button ── */
    .btn-submit {
      width: 100%; padding: 14px;
      background: linear-gradient(135deg, var(--purple) 0%, var(--purple2) 100%);
      color: #fff; border: none; border-radius: 12px;
      font-family: 'Poppins', sans-serif;
      font-size: 14px; font-weight: 700;
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
    .login-row {
      text-align: center; margin-top: 22px;
      font-size: 13px; color: var(--muted);
    }
    .login-row a {
      color: var(--purple); font-weight: 600;
      text-decoration: none;
    }
    .login-row a:hover { text-decoration: underline; }

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
      .register-shell {
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
      .field-grid {
        grid-template-columns: 1fr;
        gap: 0;
      }
    }

    @media (max-width: 430px) {
      body {
        padding: 12px;
      }
      .register-shell {
        border-radius: 16px;
      }
      .login-right {
        padding: 24px 16px;
      }
      .mobile-brand {
        margin-bottom: 20px;
      }
      .field-input {
        font-size: 13.5px;
      }
    }

    /* ── LIGHT MODE STYLES ── */
    .light-mode, body.light-mode {
      --bg: #f3f4f6;
      --surface: #ffffff;
      --card-bg: rgba(255,255,255,0.85);
      --purple: #3b82f6;
      --purple2: #2563eb;
      --purple-lt: rgba(59, 130, 246, 0.12);
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

  <div class="register-shell">

    <!-- ── Left decorative panel ── -->
    <div class="login-left">
      <div class="left-blob left-blob-1"></div>
      <div class="left-blob left-blob-2"></div>

      <div class="left-logo">
        <div class="left-logos-container">
          <div class="logo-wrapper">
            <img src="../../logo_utn.jpg" alt="Logo UTN" class="logo-utn">
          </div>
          <div class="logo-wrapper">
            <img src="../../logo_sparking.jpg" alt="Logo SParking" class="logo-sparking">
          </div>
        </div>
        <h1>SParking<br>UTN</h1>
        <p>Sistem Informasi Parkir<br>Terintegrasi Universitas</p>
      </div>

      <div class="left-features">
        <div class="left-feature">
          <div class="left-feature-icon">
            <svg class="icon-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <div class="left-feature-text">
            <strong>Daftar Instan</strong>
            Buat akun baru dalam hitungan detik
          </div>
        </div>
        <div class="left-feature">
          <div class="left-feature-icon">
            <svg class="icon-svg" viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M12 12h.01"/><path d="M17 12h.01"/><path d="M7 12h.01"/></svg>
          </div>
          <div class="left-feature-text">
            <strong>Integrasi E-Wallet</strong>
            Hubungkan GoPay, OVO, DANA untuk bayar non-tunai
          </div>
        </div>
        <div class="left-feature">
          <div class="left-feature-icon">
            <svg class="icon-svg" viewBox="0 0 24 24" aria-hidden="true"><rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 17h2"/></svg>
          </div>
          <div class="left-feature-text">
            <strong>QR Code Masuk/Keluar</strong>
            Gunakan QR Code pribadi untuk akses parkir cepat
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
      <div class="form-heading">Daftar Akun</div>
      <div class="form-subheading">Lengkapi formulir di bawah untuk membuat akun baru.</div>

      <form method="POST">
        <?php if ($err): ?>
          <div class="alert-err">
            <svg class="icon-svg" style="width:16px;height:16px;flex-shrink:0;" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($err) ?>
          </div>
        <?php endif; ?>

        <?php if ($msg): ?>
          <div class="alert-succ">
            <div style="display:flex;align-items:center;gap:8px;">
              <svg class="icon-svg" style="width:16px;height:16px;flex-shrink:0;" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              <?= htmlspecialchars($msg) ?>
            </div>
            <a href="../../login.php" style="color:var(--purple);font-weight:600;text-decoration:none;margin-top:4px;font-size:12.5px;">&rarr; Klik di sini untuk Login</a>
          </div>
        <?php endif; ?>

        <div class="field-grid">
          <!-- Nama Lengkap -->
          <div class="field">
            <label class="field-label">Nama Lengkap</label>
            <div class="field-wrap">
              <span class="field-icon">
                <svg class="icon-svg" viewBox="0 0 24 24"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </span>
              <input
                class="field-input"
                type="text" name="nama"
                placeholder="Nama lengkap"
                value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                required
              >
            </div>
          </div>

          <!-- Username -->
          <div class="field">
            <label class="field-label">Username</label>
            <div class="field-wrap">
              <span class="field-icon">
                <svg class="icon-svg" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
              </span>
              <input
                class="field-input"
                type="text" name="username"
                placeholder="username"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                required
              >
            </div>
          </div>
        </div>

        <div class="field-grid">
          <!-- Email -->
          <div class="field">
            <label class="field-label">Email</label>
            <div class="field-wrap">
              <span class="field-icon">
                <svg class="icon-svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/></svg>
              </span>
              <input
                class="field-input"
                type="email" name="email"
                placeholder="nama@utn.ac.id"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                required
              >
            </div>
          </div>

          <!-- No. HP -->
          <div class="field">
            <label class="field-label">No. HP</label>
            <div class="field-wrap">
              <span class="field-icon">
                <svg class="icon-svg" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              </span>
              <input
                class="field-input"
                type="text" name="no_hp"
                placeholder="08xx..."
                value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>"
              >
            </div>
          </div>
        </div>

        <div class="field-grid">
          <!-- Password -->
          <div class="field">
            <label class="field-label">Password</label>
            <div class="field-wrap">
              <span class="field-icon">
                <svg class="icon-svg" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <input
                class="field-input"
                type="password" name="password" id="password"
                placeholder="Min. 6 karakter"
                required
              >
            </div>
          </div>

          <!-- Konfirmasi Password -->
          <div class="field">
            <label class="field-label">Konfirmasi Password</label>
            <div class="field-wrap">
              <span class="field-icon">
                <svg class="icon-svg" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <input
                class="field-input"
                type="password" name="konfirmasi" id="konfirmasi"
                placeholder="Ulangi password"
                required
              >
            </div>
          </div>
        </div>

        <button type="submit" class="btn-submit">Daftar Akun Baru</button>
      </form>

      <div class="login-row">
        Sudah memiliki akun? <a href="../../login.php">Masuk di sini</a>
      </div>
    </div>

  </div>

  <script>
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
