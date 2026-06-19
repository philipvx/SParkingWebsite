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
    // Check if the e-wallet provider is already linked for this user
    $check = $conn->prepare("SELECT id_ewallet FROM e_wallet WHERE id_pengguna = ? AND provider = ? LIMIT 1");
    $check->bind_param("is", $id, $provider);
    $check->execute();
    $check_res = $check->get_result();
    if ($check_res->num_rows > 0) {
     $err = 'Provider E-Wallet ini sudah terhubung!';
    } else {
     $stmt = $conn->prepare("INSERT INTO e_wallet (provider, nomor_akun, saldo, status_koneksi, id_pengguna) VALUES (?,?,0,'terhubung',?)");
     $stmt->bind_param("ssi", $provider, $nomor, $id);
     if ($stmt->execute()) {
      $msg = 'E-Wallet berhasil dihubungkan!';
     } else {
      $err = 'Gagal menghubungkan E-Wallet.';
     }
    }
   }
  } elseif ($_POST['action'] === 'topup') {
   $id_ewallet = intval($_POST['id_ewallet']);
   $jumlah = floatval($_POST['jumlah']);
   if ($jumlah > 0) {
    $conn->query("UPDATE e_wallet SET saldo = saldo + $jumlah WHERE id_ewallet = $id_ewallet AND id_pengguna = $id");
    $msg = 'Top Up ' . formatRupiah($jumlah) . ' berhasil!';
   }
  } elseif ($_POST['action'] === 'hapus') {
   $id_ewallet = intval($_POST['id_ewallet']);
   // Verify ownership of the wallet
   $check_owner = $conn->prepare("SELECT id_ewallet FROM e_wallet WHERE id_ewallet = ? AND id_pengguna = ? LIMIT 1");
   $check_owner->bind_param("ii", $id_ewallet, $id);
   $check_owner->execute();
   if ($check_owner->get_result()->num_rows > 0) {
    $conn->begin_transaction();
    try {
     // Set id_ewallet reference to NULL in pembayaran table
     $stmt1 = $conn->prepare("UPDATE pembayaran SET id_ewallet = NULL WHERE id_ewallet = ?");
     $stmt1->bind_param("i", $id_ewallet);
     $stmt1->execute();
     
     // Delete the e-wallet record
     $stmt2 = $conn->prepare("DELETE FROM e_wallet WHERE id_ewallet = ? AND id_pengguna = ?");
     $stmt2->bind_param("ii", $id_ewallet, $id);
     $stmt2->execute();
     
     $conn->commit();
     $msg = 'E-Wallet berhasil diputuskan!';
    } catch (Exception $e) {
     $conn->rollback();
     $err = 'Gagal memutuskan E-Wallet: ' . $e->getMessage();
    }
   } else {
    $err = 'Akses ditolak atau E-Wallet tidak ditemukan.';
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
  <?php if ($err): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      showToast(<?= json_encode($err) ?>, 'error');
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
 <div style="display:flex;flex-direction:column;gap:8px;">
  <form method="POST" style="display:flex;gap:8px;margin:0;">
   <input type="hidden" name="action" value="topup">
   <input type="hidden" name="id_ewallet" value="<?= $w['id_ewallet'] ?>">
   <input type="number" name="jumlah" class="form-control" placeholder="Nominal top up" min="10000" step="10000" style="flex:1;">
   <button class="btn btn-primary">Top Up</button>
  </form>
  <form method="POST" style="margin:0;" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan E-Wallet ini?')">
   <input type="hidden" name="action" value="hapus">
   <input type="hidden" name="id_ewallet" value="<?= $w['id_ewallet'] ?>">
   <button class="btn btn-danger" style="width:100%;justify-content:center;">Putuskan Koneksi</button>
  </form>
 </div>
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