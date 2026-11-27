<?php
require_once 'koneksi.php';

$hari_ini        = date('Y-m-d');
$tanggal_mulai   = $_GET['tanggal_mulai'] ?? $hari_ini;
$tanggal_selesai = $_GET['tanggal_selesai'] ?? $hari_ini;

if ($tanggal_mulai > $tanggal_selesai) {
    $temp = $tanggal_mulai;
    $tanggal_mulai = $tanggal_selesai;
    $tanggal_selesai = $temp;
}

$nama_file = "Laporan_Absensi_" . $tanggal_mulai . "_sd_" . $tanggal_selesai . ".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . $nama_file);

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
          ORDER BY l.waktu_scan ASC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $tanggal_mulai, $tanggal_selesai);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="7">
                LAPORAN ABSENSI KARYAWAN - PERIODE <?= date('d/m/Y', strtotime($tanggal_mulai)) ?> s/d <?= date('d/m/Y', strtotime($tanggal_selesai)) ?>
            </th>
        </tr>
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
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['pin']) ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['divisi']) ?></td>
                <td><?= date('d-m-Y', strtotime($row['waktu_scan'])) ?></td>
                <td><?= date('H:i:s', strtotime($row['waktu_scan'])) ?> WIB</td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['metode']) ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>