<?php
// setup_db.php
require_once 'includes/config.php';

// Cek apakah database sudah siap (memiliki tabel akun_pengguna)
$check_table = $conn->query("SHOW TABLES LIKE 'akun_pengguna'");
$already_setup = ($check_table && $check_table->num_rows > 0);

if ($already_setup && !isset($_GET['force'])) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Setup — SParking UTN</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --bg: #0f172a;
                --surface: rgba(255,255,255,0.08);
                --surface-hover: rgba(255,255,255,0.12);
                --border: rgba(255,255,255,0.12);
                --accent: #6366f1;
                --text: #f8fafc;
                --text-muted: #94a3b8;
                --success: #34d399;
                --warning: #f59e0b;
            }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', sans-serif;
                background: radial-gradient(circle at 12% 16%, rgba(99,102,241,0.2), transparent 35%),
                            radial-gradient(circle at 86% 64%, rgba(139,92,246,0.15), transparent 35%),
                            linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                color: var(--text);
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 24px;
            }
            .card {
                background: var(--surface);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid var(--border);
                border-radius: 24px;
                padding: 40px 30px;
                max-width: 480px;
                width: 100%;
                text-align: center;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            }
            .icon-wrapper {
                width: 72px;
                height: 72px;
                background: rgba(99, 102, 241, 0.15);
                border: 1.5px solid rgba(99, 102, 241, 0.3);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 32px;
                margin: 0 auto 20px;
                box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
            }
            h2 {
                font-family: 'Poppins', sans-serif;
                font-size: 22px;
                font-weight: 700;
                margin-bottom: 12px;
                color: var(--text);
            }
            p {
                color: var(--text-muted);
                font-size: 14px;
                line-height: 1.6;
                margin-bottom: 28px;
            }
            .info-box {
                background: rgba(255, 255, 255, 0.04);
                border: 1px solid var(--border);
                border-radius: 12px;
                padding: 16px;
                text-align: left;
                margin-bottom: 28px;
                font-size: 13px;
            }
            .info-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
            }
            .info-row:last-child { margin-bottom: 0; }
            .info-label { color: var(--text-muted); }
            .info-val { font-weight: 600; font-family: monospace; color: var(--text); }
            .btn {
                display: block;
                width: 100%;
                padding: 14px;
                border-radius: 12px;
                background: linear-gradient(135deg, var(--accent), #8b5cf6);
                color: white;
                text-decoration: none;
                font-weight: 600;
                font-size: 14px;
                border: none;
                cursor: pointer;
                transition: transform 0.2s, box-shadow 0.2s;
                text-align: center;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
            }
            .btn-outline {
                background: transparent;
                border: 1px solid var(--border);
                color: var(--text-muted);
                margin-top: 12px;
            }
            .btn-outline:hover {
                border-color: var(--accent);
                color: var(--text);
                background: rgba(255, 255, 255, 0.02);
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon-wrapper">🛡️</div>
            <h2>Database Terproteksi</h2>
            <p>Database SParking UTN sudah terinisialisasi dengan tabel data. Setup otomatis dinonaktifkan untuk melindungi data Anda dari penghapusan tidak sengaja.</p>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Host:</span>
                    <span class="info-val"><?= htmlspecialchars(DB_HOST) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Database Name:</span>
                    <span class="info-val"><?= htmlspecialchars(DB_NAME) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">User Database:</span>
                    <span class="info-val"><?= htmlspecialchars(DB_USER) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status Koneksi:</span>
                    <span class="info-val" style="color: var(--success);">Terhubung & Aktif</span>
                </div>
            </div>

            <a href="login.php" class="btn">Masuk Ke Aplikasi</a>
            <a href="setup_db.php?force=1" class="btn btn-outline" onclick="return confirm('PERINGATAN: Memaksa setup ulang akan MENGHAPUS SEMUA DATA transaksi dan akun yang ada saat ini! Tindakan ini tidak bisa dibatalkan. Apakah Anda yakin?')">Inisialisasi Ulang</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$sql_file = 'parkir.sql';
$import_error = '';
$tables_imported = [];

if (!file_exists($sql_file)) {
    $import_error = "File schema database '$sql_file' tidak ditemukan di root directory. Pastikan file tersebut telah diunggah.";
} else {
    // Load schema
    $sql_content = file_get_contents($sql_file);
    
    // Set database
    $conn->select_db(DB_NAME);
    
    // Untuk re-setup, hapus semua tabel lama jika ?force=1
    if (isset($_GET['force'])) {
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        $get_tables = $conn->query("SHOW TABLES");
        if ($get_tables) {
            while ($row = $get_tables->fetch_array()) {
                $conn->query("DROP TABLE IF EXISTS `" . $row[0] . "`");
            }
        }
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    }

    // Jalankan multi query
    if ($conn->multi_query($sql_content)) {
        do {
            // Bersihkan hasil query
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        
        // Ambil daftar tabel yang baru saja diimpor
        $show_tables = $conn->query("SHOW TABLES");
        if ($show_tables) {
            while ($row = $show_tables->fetch_array()) {
                $tables_imported[] = $row[0];
            }
        }
    } else {
        $import_error = $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup — SParking UTN</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f172a;
            --surface: rgba(255,255,255,0.08);
            --border: rgba(255,255,255,0.12);
            --accent: #6366f1;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --success: #34d399;
            --danger: #ef4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 12% 16%, rgba(99,102,241,0.2), transparent 35%),
                        radial-gradient(circle at 86% 64%, rgba(139,92,246,0.15), transparent 35%),
                        linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }
        .card {
            background: var(--surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px 30px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .icon-wrapper {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 20px;
        }
        .icon-success {
            background: rgba(52, 211, 153, 0.15);
            border: 1.5px solid rgba(52, 211, 153, 0.3);
            box-shadow: 0 0 20px rgba(52, 211, 153, 0.2);
        }
        .icon-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1.5px solid rgba(239, 68, 68, 0.3);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
        }
        h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .info-box {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            text-align: left;
            margin-bottom: 28px;
            font-size: 13px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .info-row:last-child { margin-bottom: 0; }
        .info-label { color: var(--text-muted); }
        .info-val { font-weight: 600; font-family: monospace; color: var(--text); }
        
        .list-tables {
            max-height: 120px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            padding: 10px 14px;
            list-style: none;
            margin-top: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .list-tables li {
            padding: 4px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            font-family: monospace;
            color: var(--success);
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .list-tables li::before {
            content: '✓';
            font-weight: bold;
        }
        .list-tables li:last-child { border-bottom: none; }
        
        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-align: center;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }
    </style>
</head>
<body>
    <div class="card">
        <?php if (empty($import_error)): ?>
            <div class="icon-wrapper icon-success">✓</div>
            <h2>Inisialisasi Sukses!</h2>
            <p>Database SParking UTN telah berhasil dibuat dan dikonfigurasi pada server hosting.</p>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Host:</span>
                    <span class="info-val"><?= htmlspecialchars(DB_HOST) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Database Name:</span>
                    <span class="info-val"><?= htmlspecialchars(DB_NAME) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Tabel Dibuat:</span>
                    <span class="info-val"><?= count($tables_imported) ?> tabel</span>
                </div>
                <div class="info-label" style="margin-top: 12px;">Daftar Tabel:</div>
                <ul class="list-tables">
                    <?php foreach ($tables_imported as $table): ?>
                        <li><?= htmlspecialchars($table) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <a href="login.php" class="btn">Masuk Ke Aplikasi</a>
        <?php else: ?>
            <div class="icon-wrapper icon-danger">✗</div>
            <h2>Setup Gagal</h2>
            <p>Terjadi kesalahan saat memproses inisialisasi database di server hosting Anda.</p>
            
            <div class="info-box" style="border-color: rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.02);">
                <div style="font-weight: bold; color: var(--danger); margin-bottom: 8px;">Pesan Error:</div>
                <div style="font-family: monospace; font-size: 12px; color: var(--text); line-height: 1.5; word-break: break-all;">
                    <?= htmlspecialchars($import_error) ?>
                </div>
            </div>

            <a href="setup_db.php" class="btn">Coba Lagi</a>
        <?php endif; ?>
    </div>
</body>
</html>
