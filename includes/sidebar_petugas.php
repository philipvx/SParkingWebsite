<?php $current = basename($_SERVER['PHP_SELF'], '.php'); ?>
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
      <span>UTN — Loket Keluar</span>
    </div>
  </div>
  <div class="sidebar-role-badge">
    <div class="role-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
    <div>
      <div class="role-name"><?= htmlspecialchars($_SESSION['nama']) ?></div>
      <div class="role-label">Petugas Loket Keluar</div>
    </div>
  </div>
  <nav>
    <div class="nav-section">Operasional</div>
    <a href="dashboard.php" class="nav-item <?= $current==='dashboard'?'active':'' ?>">
      <span class="nav-icon"></span> Dashboard
    </a>
    <a href="scan_qr.php" class="nav-item <?= $current==='scan_qr'?'active':'' ?>">
      <span class="nav-icon"></span> Scan QR Keluar
    </a>
    <a href="pembayaran.php" class="nav-item <?= $current==='pembayaran'?'active':'' ?>">
      <span class="nav-icon"></span> Pembayaran
    </a>
    <a href="transaksi_aktif.php" class="nav-item <?= $current==='transaksi_aktif'?'active':'' ?>">
      <span class="nav-icon"></span> Transaksi Aktif
    </a>
    <a href="riwayat.php" class="nav-item <?= $current==='riwayat'?'active':'' ?>">
      <span class="nav-icon"></span> Riwayat Hari Ini
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
