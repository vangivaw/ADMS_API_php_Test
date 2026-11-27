# Changelog

Seluruh perubahan penting pada proyek **Sistem Monitoring Absensi ADMS** didokumentasikan pada file ini.

## [1.1.0] - 2026-11-27

### Added
- Filter rentang tanggal kustom (*Dari Tanggal* dan *Sampai Tanggal*) pada halaman dashboard (`index.php`).
- Tombol pintas periode waktu (**Hari Ini**, **7 Hari Terakhir**, dan **1 Bulan Penuh**).
- Bar ringkasan statistik jumlah log (`Total Log`, `Masuk Tepat Waktu`, `Terlambat`, dan `Pulang`) sesuai periode terpilih.
- Kolom **Tanggal** pada tabel tampilan dashboard dan hasil unduhan Excel untuk memudahkan pembacaan data lintas hari.

### Changed
- Pembaruan `export_excel.php` agar mengekspor data berdasarkan parameter `tanggal_mulai` dan `tanggal_selesai`.
- Logika *auto-refresh* 15 detik hanya diaktifkan apabila rentang filter mencakup hari berjalan.

## [1.0.0] - 2026-11-25

### Added
- Rilis awal (*Minimum Viable Product*).
- Skema database MySQL (`database.sql`) dengan tabel `karyawan` dan `log_absensi`.
- Endpoint penerima data ADMS (`api_adms.php`) dengan validasi token otentikasi dan parsing format `ATTLOG`.
- Dashboard pemantauan kehadiran harian (`index.php`) dan fitur ekspor laporan harian ke Excel (`export_excel.php`).
- Konfigurasi `.htaccess` untuk routing jalur bawaan mesin `/iclock/cdata`.