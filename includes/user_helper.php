<?php
// includes/user_helper.php

/**
 * Mengambil profil lengkap akun pengguna (gabungan akun dan data diri).
 */
function get_user_profile($id_user) {
    return db_fetch_row(
        "SELECT a.id_akun, a.username, a.role, a.status_login, p.nama, p.email, p.no_hp, p.foto_profil, p.foto_border_warna 
         FROM akun_pengguna a 
         JOIN pengguna_parkir p ON a.id_akun = p.id_pengguna 
         WHERE a.id_akun = ? LIMIT 1",
        [$id_user]
    );
}

/**
 * Mengambil seluruh akun e-wallet terhubung milik pengguna dengan verifikasi kecukupan saldo.
 */
function get_user_ewallets($id_user, $required_balance = 0) {
    $ewallets = db_fetch_all(
        "SELECT * FROM e_wallet WHERE id_pengguna = ? AND status_koneksi = 'terhubung'",
        [$id_user]
    );
    
    foreach ($ewallets as &$ew) {
        $ew['is_sufficient'] = (floatval($ew['saldo']) >= floatval($required_balance));
    }
    
    return $ewallets;
}

/**
 * Mengambil transaksi parkir aktif milik pengguna saat ini.
 */
function get_user_active_parking($id_user) {
    return db_fetch_row(
        "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, s.lokasi 
         FROM transaksi_parkir t 
         JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
         JOIN lokasi_parkir s ON t.id_lokasi = s.id_lokasi 
         WHERE k.id_pengguna = ? AND t.status_transaksi = 'parkir' 
         ORDER BY t.waktu_masuk DESC LIMIT 1",
        [$id_user]
    );
}
