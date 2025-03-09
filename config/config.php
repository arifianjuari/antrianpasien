<?php
// Definisikan base URL dengan cara yang lebih aman
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];

if ($host === 'localhost' || strpos($host, 'localhost:') === 0) {
    $base_url = $protocol . $host . '/antrian%20pasien';
} else if ($host === 'www.praktekobgin.com' || $host === 'praktekobgin.com') {
    $base_url = $protocol . $host;
} else {
    $base_url = $protocol . $host;
}

// Pastikan tidak ada trailing slash di akhir URL
$base_url = rtrim($base_url, '/');

// Definisikan konstanta untuk base URL
define('BASE_URL', $base_url);

// Debug information
error_log("Base URL: " . $base_url);
