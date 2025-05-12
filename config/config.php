<?php
// Definisikan base URL dengan cara yang lebih aman
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];

// Get current directory name regardless of spaces or special characters
$current_dir = basename(dirname(__DIR__));

// Debug current directory
error_log('Current directory name: ' . $current_dir);

if ($host === 'localhost' || strpos($host, 'localhost:') === 0) {
    // Use the exact folder name from the filesystem
    $base_url = $protocol . $host . '/' . rawurlencode($current_dir);
    error_log('Using local environment path: ' . $base_url);
} else if ($host === 'www.praktekobgin.com' || $host === 'praktekobgin.com') {
    // Untuk domain produksi, selalu gunakan HTTPS
    $base_url = 'https://' . $host;
    error_log('Using production environment path: ' . $base_url);
} else {
    $base_url = $protocol . $host;
    error_log('Using fallback environment path: ' . $base_url);
}

// Debug current directory structure
error_log('Full directory path: ' . dirname(__DIR__));

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

// Konfigurasi upload
$config['upload_path'] = __DIR__ . '/../uploads'; // Path absolut untuk development
// Untuk produksi, ubah menjadi:
// $config['upload_path'] = '/home/username/public_html/uploads'; // Sesuaikan dengan path server
