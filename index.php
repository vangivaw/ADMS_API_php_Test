<?php
require_once 'koneksi.php';

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

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
          ORDER BY l.waktu_scan DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $tanggal);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Absensi Harian</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 24px;
            color: #333;
        }

        .container {
            max-width: 1050px;
            margin: auto;
            background: #fff;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 16px;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
            color: #1e293b;
        }

        .controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        input[type="date"] {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn {
            padding: 9px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-filter {
            background: #2563eb;
            color: #fff;
        }

        .btn-excel {
            background: #16a34a;
            color: #fff;
        }

        .btn-excel:hover {
            background: #15803d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
        }

        tr:hover {
            background: #f1f5f9;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
        }

        .masuk {
            background: #dcfce7;
            color: #166534;
        }

        .terlambat {
            background: #fee2e2;
            color: #991b1b;
        }

        .pulang {
            background: #dbeafe;
            color: #1e40af;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #64748b;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <div>
                <h2>Laporan Absensi Harian Karyawan</h2>
                <small style="color: #64748b;">Pemantauan Real-Time Mesin Fingerprint ADMS</small>
            </div>

            <div class="controls">
                <form method="GET" style="display:flex; gap:8px;">
                    <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                    <button type="submit" class="btn btn-filter">Tampilkan</button>
                </form>
                <a href="export_excel.php?tanggal=<?= urlencode($tanggal) ?>" class="btn btn-excel">
                    Export to Excel
                </a>
            </div>
        </div>

        <table>
            <thead>
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
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php
                        $kelas_status = strtolower($row['status']);
                        $waktu_format = date('H:i:s', strtotime($row['waktu_scan'])) . ' WIB';
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['pin']) ?></strong></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['divisi']) ?></td>
                            <td><?= $waktu_format ?></td>
                            <td><span class="badge <?= $kelas_status ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                            <td><?= htmlspecialchars($row['metode']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty">Belum ada data log absensi pada tanggal <?= date('d-m-Y', strtotime($tanggal)) ?>.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Auto-refresh halaman setiap 15 detik untuk memantau scan baru secara real-time
        setTimeout(() => {
            window.location.reload();
        }, 15000);
    </script>

</body>

</html>