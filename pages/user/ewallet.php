<?php
require_once '../../includes/config.php';
requireRole('pengguna');
$page_title = 'E-Wallet';
$id = $_SESSION['user_id'];

$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 if (isset($_POST['action'])) {
 if ($_POST['action'] === 'hubungkan') {
 $provider = trim($_POST['provider']);
 $nomor = trim($_POST['nomor_akun']);
 if ($provider && $nomor) {
 $stmt = $conn->prepare("INSERT INTO e_wallet (provider, nomor_akun, saldo, status_koneksi, id_pengguna) VALUES (?,?,0,'terhubung',?)");
 $stmt->bind_param("ssi", $provider, $nomor, $id);
 $stmt->execute();
 $msg = 'E-Wallet berhasil dihubungkan!';
 }
 } elseif ($_POST['action'] === 'topup') {
 $id_ewallet = intval($_POST['id_ewallet']);
 $jumlah = floatval($_POST['jumlah']);
 if ($jumlah > 0) {
 $conn->query("UPDATE e_wallet SET saldo = saldo + $jumlah WHERE id_ewallet = $id_ewallet AND id_pengguna = $id");
 $msg = 'Top Up ' . formatRupiah($jumlah) . ' berhasil!';
 }
 }
 }
}

$wallets = $conn->query("SELECT * FROM e_wallet WHERE id_pengguna = $id ORDER BY status_koneksi DESC");
require_once '../../includes/header_user.php';
require_once '../../includes/sidebar_user.php';
?>
<div class="main">
 <div class="topbar"><div class="page-title">E-Wallet Saya</div></div>
 <div class="content fade-in-up">
 <?php if ($msg): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      showToast(<?= json_encode($msg) ?>, 'success');
    });
  </script>
  <?php endif; ?>

 <!-- Daftar wallet -->
 <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:24px;">
 <?php while ($w = $wallets->fetch_assoc()): 
    $prov = strtolower($w['provider']);
    $glowStyle = '';
    if ($w['status_koneksi'] === 'terhubung') {
        if (strpos($prov, 'gopay') !== false) {
            $glowStyle = 'border-color: rgba(0, 176, 240, 0.45); box-shadow: 0 10px 30px rgba(0, 176, 240, 0.12);';
        } elseif (strpos($prov, 'ovo') !== false) {
            $glowStyle = 'border-color: rgba(139, 92, 246, 0.45); box-shadow: 0 10px 30px rgba(139, 92, 246, 0.12);';
        } elseif (strpos($prov, 'dana') !== false) {
            $glowStyle = 'border-color: rgba(59, 130, 246, 0.45); box-shadow: 0 10px 30px rgba(59, 130, 246, 0.12);';
        } elseif (strpos($prov, 'shopee') !== false) {
            $glowStyle = 'border-color: rgba(249, 115, 22, 0.45); box-shadow: 0 10px 30px rgba(249, 115, 22, 0.12);';
        } elseif (strpos($prov, 'linkaja') !== false) {
            $glowStyle = 'border-color: rgba(239, 68, 68, 0.45); box-shadow: 0 10px 30px rgba(239, 68, 68, 0.12);';
        }
    }
    if ($glowStyle === '') {
        $glowStyle = 'border-color: var(--border);';
    }
  ?>
 <div style="background:linear-gradient(135deg,var(--surface2),var(--surface3)); border:1px solid transparent; border-radius:16px; padding:22px; transition: all 0.3s; <?= $glowStyle ?>">
 <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;">
 <div>
 <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;"><?= htmlspecialchars($w['provider']) ?></div>
 <div style="font-size:13px;"><?= htmlspecialchars($w['nomor_akun']) ?></div>
 </div>
 <span class="badge <?= $w['status_koneksi']==='terhubung' ? 'badge-green' : 'badge-gray' ?>"><?= $w['status_koneksi'] ?></span>
 </div>
 <div style="font-family:'Syne',sans-serif;font-size:24px;font-weight:700;color:var(--success);margin-bottom:16px;"><?= formatRupiah($w['saldo']) ?></div>
 <?php if ($w['status_koneksi']==='terhubung'): ?>
 <form method="POST" style="display:flex;gap:8px;">
 <input type="hidden" name="action" value="topup">
 <input type="hidden" name="id_ewallet" value="<?= $w['id_ewallet'] ?>">
 <input type="number" name="jumlah" class="form-control" placeholder="Nominal top up" min="10000" step="10000" style="flex:1;">
 <button class="btn btn-primary">Top Up</button>
 </form>
 <?php endif; ?>
 </div>
 <?php endwhile; ?>

 <!-- Add wallet card -->
 <div style="background:var(--surface);border:2px dashed var(--border);border-radius:16px;padding:22px;display:flex;align-items:center;justify-content:center;">
 <button class="btn btn-outline" onclick="document.getElementById('addWalletForm').style.display='block';this.style.display='none'">+ Hubungkan E-Wallet Baru</button>
 <form method="POST" id="addWalletForm" style="display:none;width:100%;">
 <input type="hidden" name="action" value="hubungkan">
 <div class="form-group">
 <label class="form-label">Provider</label>
 <select name="provider" class="form-control" required>
 <option value="">Pilih Provider</option>
 <option>GoPay</option><option>OVO</option><option>DANA</option><option>ShopeePay</option><option>LinkAja</option>
 </select>
 </div>
 <div class="form-group">
 <label class="form-label">Nomor Akun / No. HP</label>
 <input type="text" name="nomor_akun" class="form-control" placeholder="08xx..." required>
 </div>
 <button class="btn btn-primary" style="width:100%;">Hubungkan</button>
 </form>
 </div>
 </div>

 </div>
</div>
</body></html>