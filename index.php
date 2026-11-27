<?php
require_once 'koneksi.php';

$hari_ini   = date('Y-m-d');
$awal_bulan = date('Y-m-01');
$akhir_bulan = date('Y-m-t');
$tujuh_hari = date('Y-m-d', strtotime('-6 days'));

// Default tampilan saat pertama dibuka: Hari Ini
$tanggal_mulai   = $_GET['tanggal_mulai'] ?? $hari_ini;
$tanggal_selesai = $_GET['tanggal_selesai'] ?? $hari_ini;

// Validasi jika pengguna memasukkan tanggal mulai lebih besar dari tanggal selesai
$pesan_error = '';
if ($tanggal_mulai > $tanggal_selesai) {
    $pesan_error = 'Rentang tanggal tidak valid: "Dari Tanggal" tidak boleh melebihi "Sampai Tanggal".';
    $temp = $tanggal_mulai;
    $tanggal_mulai = $tanggal_selesai;
    $tanggal_selesai = $temp;
}

$query = "SELECT 
            l.pin, 
            COALESCE(k.nama, 'Karyawan Belum Terdaftar') AS nama, 
            COALESCE(k.divisi, '-') AS divisi, 
            l.waktu_scan, 
            l.status, 
            l.metode 
          FROM log_absensi l
          LEFT JOIN karyawan k ON l.pin = k.pin
          WHERE l.waktu_scan >= CONCAT(?, ' 00:00:00') 
            AND l.waktu_scan <= CONCAT(?, ' 23:59:59')
          ORDER BY l.waktu_scan DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $tanggal_mulai, $tanggal_selesai);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Simpan hasil ke array agar bisa dihitung ringkasannya sebelum ditampilkan
$data_absensi = [];
$ringkasan = ['MASUK' => 0, 'TERLAMBAT' => 0, 'PULANG' => 0];
while ($row = mysqli_fetch_assoc($result)) {
    $data_absensi[] = $row;
    if (isset($ringkasan[$row['status']])) {
        $ringkasan[$row['status']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Laporan Absensi</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f6f9; margin: 0; padding: 24px; color: #333; }
        .container { max-width: 1120px; margin: auto; background: #fff; padding: 24px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 18px; border-bottom: 1px solid #eee; padding-bottom: 16px; }
        .header h2 { margin: 0; font-size: 20px; color: #1e293b; }
        .filter-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 14px; }
        .date-inputs { display: flex; align-items: flex-end; flex-wrap: wrap; gap: 10px; }
        .field { display: flex; flex-direction: column; gap: 4px; }
        .field label { font-size: 12px; font-weight: 600; color: #475569; }
        input[type="date"] { padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: #fff; }
        .presets { display: flex; gap: 6px; flex-wrap: wrap; }
        .btn { padding: 8px 14px; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-filter { background: #2563eb; color: #fff; }
        .btn-filter:hover { background: #1d4ed8; }
        .btn-preset { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
        .btn-preset:hover { background: #e2e8f0; }
        .btn-preset.active { background: #eff6ff; color: #1d4ed8; border-color: #93c5fd; }
        .btn-excel { background: #16a34a; color: #fff; }
        .btn-excel:hover { background: #15803d; }
        .summary-bar { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; font-size: 13px; }
        .summary-item { background: #f8fafc; border: 1px solid #e2e8f0; padding: 6px 12px; border-radius: 6px; }
        .alert { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 11px 14px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        tr:hover { background: #f1f5f9; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; }
        .masuk { background: #dcfce7; color: #166534; }
        .terlambat { background: #fee2e2; color: #991b1b; }
        .pulang { background: #dbeafe; color: #1e40af; }
        .empty { text-align: center; padding: 30px; color: #64748b; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h2>Laporan Absensi Karyawan</h2>
            <small style="color: #64748b;">
                Periode: <strong><?= date('d/m/Y', strtotime($tanggal_mulai)) ?></strong> 
                s/d <strong><?= date('d/m/Y', strtotime($tanggal_selesai)) ?></strong>
            </small>
        </div>

        <a href="export_excel.php?tanggal_mulai=<?= urlencode($tanggal_mulai) ?>&tanggal_selesai=<?= urlencode($tanggal_selesai) ?>" class="btn btn-excel">
            Export to Excel
        </a>
    </div>

    <?php if ($pesan_error): ?>
        <div class="alert"><?= htmlspecialchars($pesan_error) ?></div>
    <?php endif; ?>

    <div class="filter-box">
        <form method="GET" class="date-inputs">
            <div class="field">
                <label for="tanggal_mulai">Dari Tanggal</label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?= htmlspecialchars($tanggal_mulai) ?>" required>
            </div>
            <div class="field">
                <label for="tanggal_selesai">Sampai Tanggal</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?= htmlspecialchars($tanggal_selesai) ?>" required>
            </div>
            <button type="submit" class="btn btn-filter">Tampilkan</button>
        </form>

        <div class="presets">
            <a href="?tanggal_mulai=<?= $hari_ini ?>&tanggal_selesai=<?= $hari_ini ?>" 
               class="btn btn-preset <?= ($tanggal_mulai === $hari_ini && $tanggal_selesai === $hari_ini) ? 'active' : '' ?>">
               Hari Ini
            </a>
            <a href="?tanggal_mulai=<?= $tujuh_hari ?>&tanggal_selesai=<?= $hari_ini ?>" 
               class="btn btn-preset <?= ($tanggal_mulai === $tujuh_hari && $tanggal_selesai === $hari_ini) ? 'active' : '' ?>">
               7 Hari Terakhir
            </a>
            <a href="?tanggal_mulai=<?= $awal_bulan ?>&tanggal_selesai=<?= $akhir_bulan ?>" 
               class="btn btn-preset <?= ($tanggal_mulai === $awal_bulan && $tanggal_selesai === $akhir_bulan) ? 'active' : '' ?>">
               1 Bulan Penuh
            </a>
        </div>
    </div>

    <div class="summary-bar">
        <div class="summary-item">Total Log: <strong><?= count($data_absensi) ?></strong></div>
        <div class="summary-item">Masuk Tepat Waktu: <strong style="color:#166534;"><?= $ringkasan['MASUK'] ?></strong></div>
        <div class="summary-item">Terlambat: <strong style="color:#991b1b;"><?= $ringkasan['TERLAMBAT'] ?></strong></div>
        <div class="summary-item">Pulang: <strong style="color:#1e40af;"><?= $ringkasan['PULANG'] ?></strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Karyawan</th>
                <th>Nama Karyawan</th>
                <th>Divisi</th>
                <th>Tanggal</th>
                <th>Waktu Scan</th>
                <th>Status</th>
                <th>Metode</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($data_absensi) > 0): ?>
                <?php foreach ($data_absensi as $row): ?>
                    <?php
                        $kelas_status   = strtolower($row['status']);
                        $tanggal_format = date('d-m-Y', strtotime($row['waktu_scan']));
                        $waktu_format   = date('H:i:s', strtotime($row['waktu_scan'])) . ' WIB';
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['pin']) ?></strong></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['divisi']) ?></td>
                        <td><?= $tanggal_format ?></td>
                        <td><?= $waktu_format ?></td>
                        <td><span class="badge <?= $kelas_status ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                        <td><?= htmlspecialchars($row['metode']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty">
                        Tidak ada data log absensi pada periode <?= date('d-m-Y', strtotime($tanggal_mulai)) ?> s/d <?= date('d-m-Y', strtotime($tanggal_selesai)) ?>.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($tanggal_selesai >= $hari_ini): ?>
<script>
    // Auto-refresh hanya aktif jika rentang tanggal mencakup hari ini
    setTimeout(() => {
        window.location.reload();
    }, 15000);
</script>
<?php endif; ?>

</body>
</html>