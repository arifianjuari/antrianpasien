<?php
// Definisikan base URL dengan cara yang lebih aman
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];

if ($host === 'localhost' || strpos($host, 'localhost:') === 0) {
    $base_url = $protocol . $host . '/antrian%20pasien';
} else if ($host === 'www.praktekobgin.com' || $host === 'praktekobgin.com') {
    // Untuk domain produksi, selalu gunakan HTTPS
    $base_url = 'https://' . $host;
} else {
    $base_url = $protocol . $host;
}

// Pastikan tidak ada trailing slash di akhir URL
$base_url = rtrim($base_url, '/');

// Definisikan konstanta untuk base URL
define('BASE_URL', $base_url);

// Debug information
error_log("Base URL: " . $base_url);
error_log("HTTP_HOST: " . $host);
error_log("HTTPS: " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off'));

// Definisikan konstanta untuk path
define('ROOT_PATH', dirname(__DIR__));
define('MODULES_PATH', ROOT_PATH . '/modules');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Pastikan direktori logs ada
if (!file_exists(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}
