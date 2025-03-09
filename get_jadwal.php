<?php
// File: get_jadwal.php (root)
// Deskripsi: Proxy untuk pendaftaran/get_jadwal.php untuk mengatasi masalah CORS

// Matikan pelaporan error PHP untuk mencegah output HTML
error_reporting(0);
ini_set('display_errors', 0);

// Pastikan tidak ada output sebelum header
ob_start();

// Tambahkan header CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With');
header('Access-Control-Max-Age: 86400'); // 24 jam

// Tangani preflight request OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Bersihkan output buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // Kirim header dan keluar
    http_response_code(200);
    exit;
}

// Teruskan request ke pendaftaran/get_jadwal.php
require_once 'pendaftaran/get_jadwal.php';
