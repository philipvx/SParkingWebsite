<?php
// includes/parking_helper.php

/**
 * Menghitung durasi parkir dalam menit dan jam.
 */
function calculate_duration($waktu_masuk, $waktu_keluar = null) {
    $masuk = new DateTime($waktu_masuk);
    $sekarang = $waktu_keluar ? new DateTime($waktu_keluar) : new DateTime();
    
    $diff = $masuk->diff($sekarang);
    
    // Cegah nilai negatif jika waktu sistem tidak sinkron
    if ($diff->invert) {
        return ['menit' => 0, 'jam' => 0];
    }
    
    $menit = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
    $jam = ceil($menit / 60);
    
    // Minimal 1 jam jika masuk sistem
    if ($jam == 0 && $menit >= 0) {
        $jam = 1;
    }
    
    return [
        'menit' => $menit,
        'jam' => $jam
    ];
}

/**
 * Menghitung tarif biaya parkir berdasarkan durasi dan jenis kendaraan.
 */
function calculate_fee($jenis_kendaraan, $waktu_masuk, $waktu_keluar = null) {
    // Cari tarif aktif di database
    $tarif = db_fetch_row("SELECT tarif_awal, tarif_per_jam FROM tarif_parkir WHERE jenis_kendaraan = ? AND status_tarif = 'aktif' LIMIT 1", [$jenis_kendaraan]);
    
    // Fallback jika tidak diset di database
    if (!$tarif) {
        if ($jenis_kendaraan === 'motor') {
            $tarif = ['tarif_awal' => 3000.00, 'tarif_per_jam' => 2000.00];
        } else {
            $tarif = ['tarif_awal' => 5000.00, 'tarif_per_jam' => 3000.00];
        }
    }
    
    $duration = calculate_duration($waktu_masuk, $waktu_keluar);
    $menit = $duration['menit'];
    $jam = $duration['jam'];
    
    $tarif_awal = floatval($tarif['tarif_awal']);
    $tarif_per_jam = floatval($tarif['tarif_per_jam']);
    
    // Rumus: Tarif Awal (jam pertama) + Jam berikutnya * Tarif Per Jam
    $biaya = $tarif_awal + max(0, $jam - 1) * $tarif_per_jam;
    
    return [
        'durasi_menit' => $menit,
        'durasi_jam' => $jam,
        'biaya' => $biaya,
        'tarif_awal' => $tarif_awal,
        'tarif_per_jam' => $tarif_per_jam
    ];
}

/**
 * Memproses pembayaran parkir dan menyelesaikan transaksi secara atomik (Database Transaction).
 * Mengembalikan nomor struk digital yang dibuat.
 */
function pay_and_checkout($id_transaksi, $metode, $amount, $id_ewallet = null, $id_petugas = null) {
    $waktu = date('Y-m-d H:i:s');
    
    db_begin_transaction();
    try {
        // 1. Validasi transaksi aktif
        $tx = db_fetch_row("SELECT t.*, k.id_pengguna FROM transaksi_parkir t JOIN kendaraan k ON t.id_kendaraan=k.id_kendaraan WHERE t.id_transaksi = ? AND t.status_transaksi = 'parkir' LIMIT 1", [$id_transaksi]);
        if (!$tx) {
            throw new Exception("Transaksi parkir tidak ditemukan atau sudah selesai.");
        }
        
        $id_pengguna = intval($tx['id_pengguna']);
        
        // 2. Debet saldo e-wallet jika menggunakan non-tunai
        if ($metode === 'e_wallet') {
            if (!$id_ewallet) {
                throw new Exception("Metode e-wallet dipilih tetapi akun e-wallet tidak ditentukan.");
            }
            
            $ew = db_fetch_row("SELECT saldo FROM e_wallet WHERE id_ewallet = ? AND id_pengguna = ? AND status_koneksi = 'terhubung' LIMIT 1", [$id_ewallet, $id_pengguna]);
            if (!$ew) {
                throw new Exception("Akun e-wallet tidak terhubung.");
            }
            
            $saldo = floatval($ew['saldo']);
            if ($saldo < $amount) {
                throw new Exception("Saldo e-wallet tidak mencukupi untuk melakukan transaksi.");
            }
            
            db_execute("UPDATE e_wallet SET saldo = saldo - ? WHERE id_ewallet = ?", [$amount, $id_ewallet]);
        }
        
        // 3. Masukkan data pembayaran
        $ew_id = ($metode === 'e_wallet') ? $id_ewallet : null;
        db_execute(
            "INSERT INTO pembayaran (metode_pembayaran, jumlah_bayar, waktu_bayar, status_pembayaran, id_transaksi, id_petugas, id_ewallet) VALUES (?, ?, ?, 'berhasil', ?, ?, ?)",
            [$metode, $amount, $waktu, $id_transaksi, $id_petugas, $ew_id]
        );
        $id_pembayaran = db_insert_id();
        
        // 4. Hitung durasi akhir sesungguhnya
        $duration = calculate_duration($tx['waktu_masuk'], $waktu);
        $durasi_menit = $duration['menit'];
        
        // 5. Update transaksi parkir
        db_execute(
            "UPDATE transaksi_parkir SET waktu_keluar = ?, durasi = ?, total_biaya = ?, status_transaksi = 'selesai' WHERE id_transaksi = ?",
            [$waktu, $durasi_menit, $amount, $id_transaksi]
        );
        
        // 6. Bebaskan slot lokasi parkir
        db_execute(
            "UPDATE lokasi_parkir sp JOIN transaksi_parkir t ON sp.id_lokasi = t.id_lokasi SET sp.status_lokasi = 'tersedia' WHERE t.id_transaksi = ?",
            [$id_transaksi]
        );
        
        // 7. Nonaktifkan QR Code keluar yang aktif
        db_execute(
            "UPDATE qr_code_parkir SET status_qr = 'sudah_digunakan' WHERE id_transaksi = ? AND status_qr = 'aktif'",
            [$id_transaksi]
        );
        
        // 8. Buat struk digital
        $nomor_struk = 'STR-' . date('Ymd') . '-' . str_pad($id_pembayaran, 4, '0', STR_PAD_LEFT);
        db_execute(
            "INSERT INTO struk_digital (nomor_struk, tanggal_struk, total_bayar, id_pembayaran) VALUES (?, ?, ?, ?)",
            [$nomor_struk, $waktu, $amount, $id_pembayaran]
        );
        
        db_commit();
        return $nomor_struk;
        
    } catch (Exception $e) {
        db_rollback();
        throw $e;
    }
}
