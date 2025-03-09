<?php
// Pastikan tidak ada output sebelum header
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Bersihkan output buffer jika ada
if (ob_get_length()) ob_clean();

// Set header dengan benar
header('Content-Type: application/json');

// Kirim respons JSON error
echo json_encode([
    'found' => false,
    'error' => 'Terjadi kesalahan server internal. Silakan coba lagi nanti.'
]);
exit;
