<?php
// Definisikan base URL dengan cara yang lebih aman
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];

// Log untuk debugging
error_log("Configuring BASE_URL with host: " . $host);
error_log("HTTPS status: " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off'));

if ($host === 'localhost' || strpos($host, 'localhost:') === 0) {
    $base_url = $protocol . $host . '/antrian%20pasien';
    error_log("Setting localhost BASE_URL: " . $base_url);
} else if ($host === 'www.praktekobgin.com' || $host === 'praktekobgin.com') {
    // Untuk domain produksi, selalu gunakan HTTPS
    $base_url = 'https://' . $host;
    error_log("Setting praktekobgin.com BASE_URL: " . $base_url);
} else {
    $base_url = $protocol . $host;
    error_log("Setting default BASE_URL: " . $base_url);
}

// Pastikan tidak ada trailing slash di akhir URL
$base_url = rtrim($base_url, '/');

// Definisikan konstanta untuk base URL
define('BASE_URL', $base_url);

// Debug information
error_log("Final BASE_URL: " . $base_url);
error_log("HTTP_HOST: " . $host);
error_log("HTTPS: " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off'));
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);

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
