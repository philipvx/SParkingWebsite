# SParking UTN — Smart & Integrated Parking System

**SParking UTN** adalah sistem manajemen perparkiran pintar berbasis web yang dirancang khusus untuk ekosistem kampus **Universitas Teknologi Nusantara (UTN)**. Sistem ini mengintegrasikan pelacakan kapasitas slot parkir secara real-time, registrasi kendaraan mandiri, pembayaran elektronik via e-wallet, check-out berbasis QR Code dengan webcam scanner, serta asisten virtual WhatsApp chatbot bertenaga kecerdasan buatan (Gemini AI).

---

## 🚀 Fitur Utama

Sistem ini membagi akses menjadi 3 peran utama (Roles) dengan fitur-fitur pendukung:

### 1. Portal Pengguna (Mahasiswa/Dosen/Staf)
*   **Dashboard Interaktif**: Ucapan selamat personal sesuai waktu server, jam digital real-time, panel informasi status kendaraan yang sedang diparkir (disertai timer durasi parkir), dan ringkasan riwayat transaksi terakhir.
*   **Manajemen E-Wallet**: Menghubungkan dan melihat saldo e-wallet terintegrasi (GoPay, OVO, DANA, ShopeePay, LinkAja) secara langsung.
*   **Peta & Slot Parkir**: Memantau kapasitas slot parkir yang tersedia secara real-time pada berbagai lokasi (Fakultas Ekonomi Digital, Fakultas Saintek, Fakultas Teknik) untuk jenis kendaraan mobil dan motor.
*   **Registrasi Kendaraan**: Mendaftarkan nomor plat, merk, warna, dan jenis kendaraan (mobil/motor).
*   **QR Code Parkir**: Menghasilkan QR Code check-in & check-out secara otomatis untuk dipindai oleh petugas saat masuk/keluar gerbang parkir.
*   **Riwayat Transaksi**: Detail lengkap riwayat transaksi parkir lengkap dengan durasi parkir, total biaya, status (Parkir, Selesai, Batal), dan cetak struk digital.

### 2. Portal Petugas Gerbang (Petugas Keluar)
*   **Webcam Scanner**: Memindai QR Code dari pengguna menggunakan kamera/webcam laptop secara real-time untuk mempercepat proses check-out.
*   **Input Manual**: Alternatif pencarian transaksi menggunakan kode QR jika kamera scanner bermasalah.
*   **Kalkulasi Biaya Otomatis**: Sistem menghitung durasi parkir dan biaya secara dinamis berdasarkan tarif aktif yang ditetapkan.
*   **Pembayaran Fleksibel**: Mendukung pembayaran tunai (cash) maupun pemotongan saldo otomatis via E-Wallet pengguna yang terhubung.
*   **Daftar Transaksi Aktif**: Memantau seluruh kendaraan yang saat ini masih berada di dalam area parkir.

### 3. Portal Kepala Loket (Administrator/Manager)
*   **Metrik & Statistik**: Grafik transaksi 7 hari terakhir, total pendapatan harian/bulanan, dan status kapasitas parkir keseluruhan.
*   **Manajemen Slot Lokasi**: Menambah, mengubah, atau menghapus lokasi parkir, kapasitas, serta status slot (Tersedia, Terisi, Maintenance).
*   **Manajemen Tarif**: Mengatur tarif awal dan tarif per jam untuk setiap jenis kendaraan (mobil & motor) secara dinamis.
*   **Manajemen Akun**: Mengelola akun petugas gerbang dan pengguna parkir yang terdaftar di dalam sistem.
*   **Laporan & PDF Export**: Menghasilkan laporan transaksi parkir (harian/bulanan) dengan visualisasi bersih dan fitur ekspor ke file PDF.

### 4. Asisten WhatsApp AI ("Kira")
Sistem dilengkapi dengan chatbot WhatsApp terintegrasi pada file [webhook.php](file:///c:/xampp/htdocs/parkir/webhook.php):
*   **Nama Bot**: **Kira**, asisten pintar milik Philip.
*   **Persona**: Santai, ceria, informatif, dan menggunakan gaya bahasa khas Gen Z.
*   **Teknologi**: Menggunakan Gemini 3.1 Flash Lite API (via Google Generative Language API) & Fonnte Gateway.
*   **Integrasi Web Scraping**: Mampu mendeteksi tautan/URL di dalam chat WhatsApp, mengunduh konten web tersebut secara otomatis (hingga 3000 karakter), dan menjawab pertanyaan berdasarkan konten situs web tersebut.
*   **Memori Konteks**: Menyimpan riwayat percakapan hingga 10 baris chat terakhir per nomor pengguna untuk memberikan respon yang kontekstual.

---

## 🛠️ Spesifikasi Teknologi

Sistem dibangun menggunakan tumpukan teknologi modern berikut:
*   **Bahasa Pemrograman**: Native PHP 7.4+ (menggunakan ekstensi `mysqli` dan Object-Oriented style).
*   **Basis Data**: MySQL / MariaDB.
*   **UI/UX**: HTML5, Vanilla CSS3 (Custom Design System dengan Glassmorphism, efek glow, animasi mengambang, serta font Google Fonts *Inter* & *Poppins*). Mendukung mode gelap (*Dark Mode*) bawaan yang sangat premium.
*   **API Pihak Ketiga**:
    *   **Google Gemini API**: Pemrosesan Natural Language Processing (NLP) chatbot WhatsApp.
    *   **Fonnte API**: WhatsApp Gateway Integration.
    *   **QR Server API**: Pembuatan QR Code otomatis.
    *   **Html5-Qrcode**: Pustaka Javascript untuk scanner kamera pada portal petugas.

---

## 📁 Struktur Direktori Proyek

Berikut adalah struktur file penting dari proyek SParking UTN:

```text
parkir/
├── .agent/                  # Konfigurasi AI Agent
├── includes/                # Helper dan Komponen Reusable
│   ├── config.php           # Konfigurasi Database, Session, & Base URL
│   ├── db_helper.php        # Helper kueri database dasar
│   ├── parking_helper.php   # Logika bisnis parkir (hitung biaya, checkout)
│   ├── user_helper.php      # Helper profil pengguna
│   ├── header_staff.php     # Header halaman Petugas & Kepala
│   ├── header_user.php      # Header halaman Pengguna
│   ├── sidebar_kepala.php   # Menu navigasi Kepala Loket
│   ├── sidebar_petugas.php  # Menu navigasi Petugas Keluar
│   └── sidebar_user.php     # Menu navigasi Pengguna
├── pages/                   # Modul Halaman berdasarkan Peran
│   ├── kepala/              # Portal Kepala Loket (Laporan, Slot, Petugas, Tarif)
│   │   ├── dashboard.php
│   │   ├── laporan.php
│   │   ├── lokasi_mgmt.php
│   │   ├── petugas_mgmt.php
│   │   └── tarif.php
│   ├── petugas/             # Portal Petugas Keluar (Scanner, Pembayaran)
│   │   ├── dashboard.php
│   │   └── scan_qr.php
│   └── user/                # Portal Pengguna (E-wallet, Kendaraan, QR Code)
│       ├── dashboard.php
│       ├── ewallet.php
│       ├── kendaraan.php
│       ├── lokasi.php
│       └── qrcode.php
├── uploads/                 # Folder penyimpanan berkas unggahan (Foto Profil)
├── index.php                # Halaman Landing utama (Landing Page)
├── login.php                # Halaman Login Multi-Role
├── setup_db.php             # Skrip instalasi & reset database otomatis
├── parkir.sql               # Skema database MySQL & data contoh awal
└── webhook.php              # Endpoint Webhook Chatbot WhatsApp (Kira)
```

---

## ⚙️ Petunjuk Pemasangan (Setup)

Ikuti langkah-langkah di bawah ini untuk menjalankan SParking UTN di komputer lokal Anda:

### 1. Prasyarat Sistem
*   Instal aplikasi **XAMPP** atau web server lokal sejenis yang mendapat dukungan **PHP 7.4+** dan **MySQL/MariaDB**.

### 2. Pemasangan Berkas
1.  Unduh atau salin seluruh folder proyek ini ke direktori root web server Anda (biasanya `C:\xampp\htdocs\parkir`).

### 3. Konfigurasi Database
Aplikasi memiliki fitur pembuat database otomatis yang sangat praktis:
1.  Jalankan modul **Apache** dan **MySQL** pada panel kontrol XAMPP Anda.
2.  Buka browser dan akses URL: `http://localhost/parkir/setup_db.php`.
3.  Halaman instalasi akan mendeteksi apakah database telah dibuat. Jika belum, sistem akan mengimpor file `parkir.sql` secara otomatis ke database MySQL Anda.
4.  Jika instalasi sukses, Anda akan melihat tombol **Masuk Ke Aplikasi** untuk dialihkan ke halaman masuk.

> [!NOTE]  
> Jika Anda ingin melakukan konfigurasi database secara manual, silakan edit berkas konfigurasi di `includes/config.php` pada bagian berikut:
> ```php
> if ($is_local) {
>     define('DB_HOST', 'localhost');
>     define('DB_USER', 'root');
>     define('DB_PASS', '');
>     define('DB_NAME', 'parkir');
> }
> ```

---

## 🔑 Akun Uji Coba Default

Gunakan kredensial berikut untuk menguji coba fitur masing-masing role:

1.  **Pengguna (User)**:
    *   Username: `philip` atau `kamil`
    *   Password: *(Dapat melakukan registrasi akun baru langsung di halaman login)*
2.  **Petugas Gerbang (Staff)**:
    *   Username: `admin` atau `uus`
3.  **Kepala Loket (Manager)**:
    *   Username: `jack`

*(Catatan: Untuk masuk menggunakan akun demo di atas, silakan cek hash password pada tabel `akun_pengguna` di database atau tambahkan akun baru melalui menu pengelolaan Kepala Loket).*

---

## 💬 Konfigurasi WhatsApp Chatbot

Untuk menggunakan chatbot WhatsApp "Kira":
1.  Buka file `webhook.php`.
2.  Ganti nilai variabel `$geminiApiKey` (pada baris 39) dengan API Key Google Gemini Anda yang valid.
3.  Ganti nilai variabel `$fonnteToken` (pada baris 124) dengan Token Fonnte Anda.
4.  Hubungkan webhook URL dari dashboard Fonnte ke file `webhook.php` yang sudah di-online-kan (misalnya melalui ngrok atau hosting publik).

---

*Dikembangkan untuk memberikan solusi parkir modern yang terintegrasi, estetik, dan efisien.*  
**SParking UTN — Smart & Integrated Parking System**
