<?php
require_once '../../includes/config.php';
requireRole('kepala_loket');
$page_title = 'Kelola Tarif';

$msg = ''; $err = '';

// Check which vehicle types already exist in the database
$existing_types = [];
$check_types = db_fetch_all("SELECT jenis_kendaraan FROM tarif_parkir");
foreach ($check_types as $ct) {
    $existing_types[] = $ct['jenis_kendaraan'];
}
$has_motor = in_array('motor', $existing_types);
$has_mobil = in_array('mobil', $existing_types);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    if ($_POST['action'] === 'tambah' || $_POST['action'] === 'edit') {
      $jenis = $_POST['jenis_kendaraan'];
      $tarif_awal = floatval($_POST['tarif_awal']);
      $tarif_jam = floatval($_POST['tarif_per_jam']);
      $status = $_POST['status_tarif'];
      
      if ($_POST['action'] === 'tambah') {
        // Double check on server side to prevent duplicates
        $exists = db_fetch_value("SELECT COUNT(*) FROM tarif_parkir WHERE jenis_kendaraan = ?", [$jenis]);
        if ($exists > 0) {
          $err = 'Tarif untuk jenis kendaraan ini sudah terdaftar.';
        } else {
          db_execute("INSERT INTO tarif_parkir (jenis_kendaraan, tarif_awal, tarif_per_jam, status_tarif) VALUES (?,?,?,?)", [$jenis, $tarif_awal, $tarif_jam, $status]);
          $msg = 'Tarif berhasil ditambahkan!';
          
          // Refresh existing types
          $existing_types[] = $jenis;
          $has_motor = in_array('motor', $existing_types);
          $has_mobil = in_array('mobil', $existing_types);
        }
      } else {
        $id = intval($_POST['id_tarif']);
        db_execute("UPDATE tarif_parkir SET tarif_awal=?, tarif_per_jam=?, status_tarif=? WHERE id_tarif=?", [$tarif_awal, $tarif_jam, $status, $id]);
        $msg = 'Tarif berhasil diupdate!';
      }
    }
  }
}

$tarif_list = db_fetch_all("SELECT * FROM tarif_parkir ORDER BY jenis_kendaraan");
require_once '../../includes/header_staff.php';
require_once '../../includes/sidebar_kepala.php';
?>
<div class="main">
 <div class="topbar"><div class="page-title"> Kelola Tarif Parkir</div></div>
 <div class="content">
 <?php if ($msg): ?><div class="alert alert-success"> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
 <?php if ($err): ?><div class="alert alert-error"> <?= htmlspecialchars($err) ?></div><?php endif; ?>

 <div style="display:grid;grid-template-columns:1fr 360px;gap:20px;">
 <!-- List -->
 <div class="table-card">
 <div class="table-header"><h3>Daftar Tarif</h3></div>
 <table>
 <thead><tr><th>Jenis Kendaraan</th><th>Tarif Awal</th><th>Tarif/Jam</th><th>Status</th><th>Aksi</th></tr></thead>
 <tbody>
  <?php foreach ($tarif_list as $t): ?>
  <tr>
  <td><?= ucfirst($t['jenis_kendaraan']) ?></td>
  <td><?= formatRupiah($t['tarif_awal']) ?></td>
  <td><?= formatRupiah($t['tarif_per_jam']) ?></td>
  <td><span class="badge <?= $t['status_tarif']==='aktif' ? 'badge-green' : 'badge-gray' ?>"><?= $t['status_tarif'] ?></span></td>
  <td>
  <button class="btn btn-outline btn-sm" onclick="editTarif(<?= htmlspecialchars(json_encode($t)) ?>)">Edit / Ubah</button>
  </td>
  </tr>
  <?php endforeach; ?>
 </tbody>
 </table>
 </div>

 <!-- Form -->
 <div class="form-card">
 <h3 style="font-family:'Syne',sans-serif;font-size:15px;margin-bottom:16px;" id="formTitle"><?= ($has_motor && $has_mobil) ? 'Info Tarif' : 'Tambah Tarif' ?></h3>
 
 <?php if ($has_motor && $has_mobil): ?>
 <div id="infoText" style="font-size:13px;color:var(--text-muted);line-height:1.6;margin-bottom:16px;">
   Tarif untuk kendaraan <strong>Mobil</strong> dan <strong>Motor</strong> sudah ditetapkan.
   <br><br>
   Anda tidak dapat menambahkan tarif baru agar tidak terjadi bentrok perhitungan biaya. Silakan gunakan tombol <strong>Edit / Ubah</strong> pada tabel daftar tarif untuk memperbarui tarif yang sudah ada.
 </div>
 <?php endif; ?>

 <form method="POST" id="tarifForm" <?= ($has_motor && $has_mobil) ? 'style="display:none;"' : '' ?>>
 <input type="hidden" name="action" id="formAction" value="tambah">
 <input type="hidden" name="id_tarif" id="formId">
 <div class="form-group">
 <label class="form-label">Jenis Kendaraan</label>
 <select name="jenis_kendaraan" id="formJenis" class="form-control" required>
   <?php if (!$has_motor): ?><option value="motor">Motor</option><?php endif; ?>
   <?php if (!$has_mobil): ?><option value="mobil">Mobil</option><?php endif; ?>
   <?php if ($has_motor && $has_mobil): ?>
     <option value="motor" style="display:none;">Motor</option>
     <option value="mobil" style="display:none;">Mobil</option>
   <?php endif; ?>
 </select>
 </div>
 <div class="form-group">
 <label class="form-label">Tarif Awal (Rp)</label>
 <input type="number" name="tarif_awal" id="formAwal" class="form-control" placeholder="2000" required>
 </div>
 <div class="form-group">
 <label class="form-label">Tarif Per Jam (Rp)</label>
 <input type="number" name="tarif_per_jam" id="formJam" class="form-control" placeholder="3000" required>
 </div>
 <div class="form-group">
 <label class="form-label">Status</label>
 <select name="status_tarif" id="formStatus" class="form-control">
 <option value="aktif">Aktif</option>
 <option value="nonaktif">Nonaktif</option>
 </select>
 </div>
 <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Simpan Tarif</button>
 <button type="button" class="btn btn-outline" style="width:100%;justify-content:center;margin-top:8px;" onclick="resetForm()">Batal</button>
 </form>
 </div>
 </div>
 </div>
</div>
<script>
function editTarif(t) {
 const infoText = document.getElementById('infoText');
 if (infoText) infoText.style.display = 'none';
 
 const form = document.getElementById('tarifForm');
 if (form) form.style.display = 'block';

 const formJenis = document.getElementById('formJenis');
 let optionExists = false;
 for (let i = 0; i < formJenis.options.length; i++) {
   if (formJenis.options[i].value === t.jenis_kendaraan) {
     formJenis.options[i].style.display = 'block';
     optionExists = true;
   }
 }
 if (!optionExists) {
   const opt = document.createElement('option');
   opt.value = t.jenis_kendaraan;
   opt.textContent = t.jenis_kendaraan.charAt(0).toUpperCase() + t.jenis_kendaraan.slice(1);
   formJenis.appendChild(opt);
 }

 document.getElementById('formTitle').textContent = 'Edit Tarif';
 document.getElementById('formAction').value = 'edit';
 document.getElementById('formId').value = t.id_tarif;
 document.getElementById('formJenis').value = t.jenis_kendaraan;
 
 // Readonly styling for select during edit
 formJenis.style.pointerEvents = 'none';
 formJenis.style.background = 'var(--surface2)';

 document.getElementById('formAwal').value = t.tarif_awal;
 document.getElementById('formJam').value = t.tarif_per_jam;
 document.getElementById('formStatus').value = t.status_tarif;
}

function resetForm() {
 const form = document.getElementById('tarifForm');
 if (form) form.reset();

 const formJenis = document.getElementById('formJenis');
 formJenis.style.pointerEvents = 'auto';
 formJenis.style.background = 'rgba(255,255,255,0.08)';

 const hasBoth = <?= ($has_motor && $has_mobil) ? 'true' : 'false' ?>;
 if (hasBoth) {
   const infoText = document.getElementById('infoText');
   if (infoText) infoText.style.display = 'block';
   if (form) form.style.display = 'none';
   document.getElementById('formTitle').textContent = 'Info Tarif';
 } else {
   document.getElementById('formTitle').textContent = 'Tambah Tarif';
   document.getElementById('formAction').value = 'tambah';
 }
}
</script>
</body></html>