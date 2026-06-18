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
      <span>UTN — Kepala Loket</span>
    </div>
  </div>
  <div class="sidebar-role-badge" style="background:linear-gradient(135deg,rgba(6,182,212,0.15),rgba(249,115,22,0.08));border-color:rgba(6,182,212,0.3);">
    <div class="role-avatar" style="background:linear-gradient(135deg,var(--accent-cool),#0891b2);"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
    <div>
      <div class="role-name"><?= htmlspecialchars($_SESSION['nama']) ?></div>
      <div class="role-label" style="color:var(--accent-cool);">Kepala Loket Parkir</div>
    </div>
  </div>
  <nav>
    <div class="nav-section">Overview</div>
    <a href="dashboard.php" class="nav-item <?= $current==='dashboard'?'active':'' ?>">
      <span class="nav-icon"></span> Dashboard
    </a>
    <div class="nav-section">Manajemen</div>
    <a href="pengguna.php" class="nav-item <?= $current==='pengguna'?'active':'' ?>">
      <span class="nav-icon"></span> Kelola Pengguna
    </a>
    <a href="petugas_mgmt.php" class="nav-item <?= $current==='petugas_mgmt'?'active':'' ?>">
      <span class="nav-icon"></span> Kelola Petugas
    </a>
    <a href="tarif.php" class="nav-item <?= $current==='tarif'?'active':'' ?>">
      <span class="nav-icon"></span> Kelola Tarif
    </a>
    <a href="lokasi_mgmt.php" class="nav-item <?= $current==='lokasi_mgmt'?'active':'' ?>">
      <span class="nav-icon"></span> Kelola Lokasi
    </a>
    <div class="nav-section">Laporan</div>
    <a href="transaksi_all.php" class="nav-item <?= $current==='transaksi_all'?'active':'' ?>">
      <span class="nav-icon"></span> Semua Transaksi
    </a>
    <a href="laporan.php" class="nav-item <?= $current==='laporan'?'active':'' ?>">
      <span class="nav-icon"></span> Laporan Parkir
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
