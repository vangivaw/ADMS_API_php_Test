<?php
require_once 'koneksi.php';

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Absensi_" . $tanggal . ".xls");

$query = "SELECT 
            l.pin, 
            COALESCE(k.nama, 'Karyawan Belum Terdaftar') AS nama, 
            COALESCE(k.divisi, '-') AS divisi, 
            l.waktu_scan, 
            l.status, 
            l.metode 
          FROM log_absensi l
          LEFT JOIN karyawan k ON l.pin = k.pin
          WHERE DATE(l.waktu_scan) = ?
          ORDER BY l.waktu_scan ASC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $tanggal);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="6">LAPORAN ABSENSI HARIAN - TANGGAL <?= date('d/m/Y', strtotime($tanggal)) ?></th>
        </tr>
        <tr>
            <th>ID Karyawan</th>
            <th>Nama Karyawan</th>
            <th>Divisi</th>
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
                <td><?= date('H:i:s', strtotime($row['waktu_scan'])) ?> WIB</td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['metode']) ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>