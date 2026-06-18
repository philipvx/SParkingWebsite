<?php
$current = basename($_SERVER['PHP_SELF'], '.php');
$sidebar_photo = $_SESSION['foto_profil'] ?? null;
$sidebar_border = $_SESSION['foto_border_warna'] ?? null;
if ((!$sidebar_photo || !isset($_SESSION['foto_border_warna'])) && isset($conn, $_SESSION['user_id'])) {
  $photo_stmt = $conn->prepare("SELECT foto_profil, foto_border_warna FROM pengguna_parkir WHERE id_pengguna = ? LIMIT 1");
  if ($photo_stmt) {
    $photo_stmt->bind_param("i", $_SESSION['user_id']);
    $photo_stmt->execute();
    $photo_row = $photo_stmt->get_result()->fetch_assoc();
    $sidebar_photo = $photo_row['foto_profil'] ?? null;
    $sidebar_border = $photo_row['foto_border_warna'] ?? null;
    $_SESSION['foto_profil'] = $sidebar_photo;
    $_SESSION['foto_border_warna'] = $sidebar_border;
  }
}
?>
<button type="button" class="mobile-menu-toggle" aria-label="Buka menu" onclick="document.body.classList.toggle('sidebar-open')"></button>
<div class="sidebar-overlay" onclick="document.body.classList.remove('sidebar-open')"></div>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-logos" style="display: flex; align-items: center; gap: 8px; flex-shrink: 0; margin-right: 2px;">
      <div style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
        <img src="<?= htmlspecialchars(appUrl('logo_utn_nobg.png')) ?>" alt="Logo UTN" style="width: 100%; height: 100%; object-fit: contain;">
      </div>
      <div style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
        <img src="<?= htmlspecialchars(appUrl('logo_sparking_nobg.png')) ?>" alt="Logo SParking" style="width: 100%; height: 100%; object-fit: contain;">
      </div>
    </div>
    <div class="brand-text">
      <h2>SParking</h2>
      <span>UTN — Pengguna</span>
    </div>
  </div>
  <div class="sidebar-user">
    <div class="user-info">
      <div class="user-avatar" style="overflow:hidden; display:flex; align-items:center; justify-content:center; padding: <?= $sidebar_border ? '2px' : '0' ?>; background: <?= $sidebar_border ?: 'transparent' ?>; border-radius:50%; width:36px; height:36px; transition: background 0.2s, padding 0.2s;">
        <div style="width:100%; height:100%; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; background:var(--surface2);">
          <?php if ($sidebar_photo): ?>
            <img src="<?= htmlspecialchars(appUrl($sidebar_photo)) ?>" alt="Foto profil" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
          <?php endif; ?>
        </div>
      </div>
      <div>
        <div class="user-name"><?= htmlspecialchars($_SESSION['nama']) ?></div>
        <span class="user-role">Pengguna Parkir</span>
      </div>
    </div>
  </div>
  <nav>
    <div class="nav-section">Menu Utama</div>
    <a href="dashboard.php" class="nav-item <?= $current === 'dashboard' ? 'active' : '' ?>">
      <span class="nav-icon"></span> Dashboard
    </a>
    <a href="lokasi.php" class="nav-item <?= $current === 'slot' ? 'active' : '' ?>">
      <span class="nav-icon"></span> Lokasi Parkir
    </a>
    <a href="transaksi.php" class="nav-item <?= $current === 'transaksi' ? 'active' : '' ?>">
      <span class="nav-icon"></span> Riwayat Transaksi
    </a>
    <a href="qrcode.php" class="nav-item <?= $current === 'qrcode' ? 'active' : '' ?>">
      <span class="nav-icon"></span> QR Code Saya
    </a>
    <div class="nav-section">Keuangan</div>
    <a href="ewallet.php" class="nav-item <?= $current === 'ewallet' ? 'active' : '' ?>">
      <span class="nav-icon"></span> E-Wallet
    </a>
    <div class="nav-section">Akun</div>
    <a href="profil.php" class="nav-item <?= $current === 'profil' ? 'active' : '' ?>">
      <span class="nav-icon"></span> Profil
    </a>
  </nav>
  <div class="sidebar-footer">
    <a href="../../logout.php" class="nav-item btn-danger" style="border-radius:8px;">
      <span class="nav-icon"></span> Logout
    </a>
  </div>
</aside>
<script>
document.querySelectorAll('.sidebar .nav-item').forEach(function (item) {
  item.addEventListener('click', function () {
    document.body.classList.remove('sidebar-open');
  });
});
</script>
