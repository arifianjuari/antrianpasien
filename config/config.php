<?php
// Definisikan base URL dengan cara yang lebih aman
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];

if ($host === 'localhost' || strpos($host, 'localhost:') === 0) {
    $base_url = $protocol . $host . '/antrian%20pasien';
} else {
    $base_url = $protocol . $host;
}

// Pastikan tidak ada trailing slash di akhir URL
$base_url = rtrim($base_url, '/');
