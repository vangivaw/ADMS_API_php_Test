<?php
date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$user = "root";       // Sesuaikan dengan username database hosting
$pass = "";           // Sesuaikan dengan password database hosting
$db   = "db_absensi_percetakan";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    http_response_code(500);
    die("Koneksi database gagal: " . mysqli_connect_error());
}
