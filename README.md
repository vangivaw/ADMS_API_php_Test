# Sistem Monitoring Absensi Sidik Jari Berbasis Cloud (ADMS)

Aplikasi web berbasis **PHP Native** dan **MySQL** untuk menerima, menyimpan, dan memantau log kehadiran karyawan secara *real-time* dari mesin absensi biometrik yang mendukung protokol **ADMS (*Automatic Data Master Server* / Push Protocol)**.

Sistem ini dirancang untuk menggantikan proses penarikan data absensi manual menggunakan *flashdisk* dengan integrasi otomatis langsung ke *web hosting*.

## Fitur Utama

- **ADMS Push API Receiver (`api_adms.php`):** Menerima data *push* HTTP langsung dari mesin absensi (mendukung format standar ADMS `ATTLOG` tab-delimited maupun JSON untuk pengujian).
- **Autentikasi Keamanan API:** Validasi token rahasia (`token` parameter atau *header* `X-API-TOKEN`) untuk menolak permintaan tidak sah (`401 Unauthorized`).
- **Auto-Status Kehadiran:** Klasifikasi otomatis status scan (`MASUK`, `TERLAMBAT`, `PULANG`) serta identifikasi metode verifikasi (Sidik Jari, Wajah, Kartu RFID).
- **Dashboard Monitoring Real-Time (`index.php`):**
  - Filter rentang tanggal kustom (*Dari Tanggal* – *Sampai Tanggal*).
  - Tombol pintas periode (**Hari Ini**, **7 Hari Terakhir**, **1 Bulan Penuh**).
  - Ringkasan statistik kehadiran dan *auto-refresh* setiap 15 detik untuk pemantauan harian.
- **Ekspor Laporan Excel (`export_excel.php`):** Mengunduh rekapitulasi absensi format `.xls` sesuai rentang tanggal yang dipilih tanpa pustaka eksternal.

## Teknologi yang Digunakan

- **Backend:** PHP 8.x (MySQLi Prepared Statements)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Protokol Perangkat:** ADMS / iClock HTTP Push Protocol

## Panduan Instalasi

1. **Clone Repositori**
   ```bash
   git clone [https://github.com/vangivaw/ADMS_API_php_Test.git]
   cd ADMS_API_php_Test
   ```

2. **Konfigurasi Database**
   - Buat database baru di MySQL/phpMyAdmin bernama `db_absensi`.
   - Impor file `database.sql` untuk membuat tabel `karyawan` dan `log_absensi`.

3. **Atur Kredensial Koneksi & Token**
   - Salin `config.example.php` menjadi `config.php`:
     ```bash
     cp config.example.php config.php
     ```
   - Sesuaikan kredensial database dan nilai `API_TOKEN` di dalam `config.php`.

4. **Konfigurasi Mesin Fingerprint (ADMS)**
   - Masuk ke menu **Comm / Cloud Server Setting** pada mesin absensi.
   - Arahkan alamat server ke domain/IP hosting Anda dengan menyertakan parameter token (atau gunakan aturan `.htaccess` untuk jalur `/iclock/cdata`).

## Pengujian API (Postman / cURL)

Sebelum menghubungkan mesin fisik, uji *endpoint* API menggunakan cURL atau Postman (`POST` dengan *Body* `raw` -> `Text`, dipisahkan karakter `Tab`):

```bash
curl -X POST "http://localhost/adms_api_php_test/api_adms.php?token=TOKEN_ANDA" \
     -H "Content-Type: text/plain" \
     -d "K-001	2026-11-27 07:45:12	0	1"
```

Respons berhasil akan mengembalikan teks polos `OK` dengan kode status `200`.

## Lisensi

Proyek ini dirilis di bawah lisensi [MIT](https://opensource.org/licenses/MIT).
