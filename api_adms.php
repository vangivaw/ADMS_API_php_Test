<?php
require_once 'koneksi.php';

// Token keamanan rahasia (samakan dengan pengaturan di mesin/middleware)
define('API_TOKEN', 'PERCETAKAN_SECRET_2026');

// 1. Validasi Token Otentikasi
$token_masuk = $_GET['token'] ?? $_SERVER['HTTP_X_API_TOKEN'] ?? '';
if ($token_masuk !== API_TOKEN) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Token tidak valid']);
    exit;
}

// 2. Handshake awal mesin ADMS (Metode GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: text/plain');
    echo "OK";
    exit;
}

// 3. Penerimaan Log Kehadiran (Metode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_data = file_get_contents('php://input');
    $records = [];

    // Cek apakah payload berbentuk JSON (untuk pengujian API / middleware)
    $json = json_decode($raw_data, true);
    if (is_array($json) && isset($json['pin'], $json['waktu_scan'])) {
        $records[] = [
            'pin'        => trim($json['pin']),
            'waktu_scan' => trim($json['waktu_scan']),
            'kode_status' => $json['status_code'] ?? null,
            'kode_verif' => $json['verify_code'] ?? 1
        ];
    } else {
        // Parsing format standar ADMS ATTLOG (dipisahkan baris baru & tab)
        // Format umum mesin: PIN \t YYYY-MM-DD HH:MM:SS \t Status \t Verify
        $lines = explode("\n", trim($raw_data));
        foreach ($lines as $line) {
            $cols = explode("\t", trim($line));
            if (count($cols) >= 2 && !empty($cols[0])) {
                $records[] = [
                    'pin'        => trim($cols[0]),
                    'waktu_scan' => trim($cols[1]),
                    'kode_status' => $cols[2] ?? null,
                    'kode_verif' => $cols[3] ?? 1
                ];
            }
        }
    }

    $berhasil = 0;
    $stmt = mysqli_prepare($conn, "INSERT INTO log_absensi (pin, waktu_scan, status, metode) VALUES (?, ?, ?, ?)");

    foreach ($records as $row) {
        $pin = $row['pin'];
        $waktu = date('Y-m-d H:i:s', strtotime($row['waktu_scan']));
        $jam = date('H:i:s', strtotime($waktu));

        // Penentuan Status Kehadiran (Aturan jam masuk standar 08:00 WIB)
        if ($row['kode_status'] === '1' || $jam >= '15:00:00') {
            $status = 'PULANG';
        } elseif ($jam > '08:00:00') {
            $status = 'TERLAMBAT';
        } else {
            $status = 'MASUK';
        }

        // Mapping metode verifikasi mesin
        $metode = ($row['kode_verif'] == 15) ? 'Wajah' : (($row['kode_verif'] == 4) ? 'Kartu RFID' : 'Sidik Jari');

        mysqli_stmt_bind_param($stmt, "ssss", $pin, $waktu, $status, $metode);
        if (mysqli_stmt_execute($stmt)) {
            $berhasil++;
        }
    }

    // Mesin ADMS wajib menerima balasan teks "OK" agar menghapus antrean buffer lokal
    header('Content-Type: text/plain');
    echo "OK";
    exit;
}
