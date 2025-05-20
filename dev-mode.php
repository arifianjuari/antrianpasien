<?php
/**
 * Development Mode Detection
 * 
 * File ini digunakan untuk mendeteksi apakah aplikasi berjalan di mode development (localhost)
 * dan menerapkan konfigurasi yang sesuai
 */

// Cek apakah berjalan di localhost
$is_localhost = isset($_SERVER['HTTP_HOST']) && 
               (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
                strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);

// Define konstanta untuk digunakan di seluruh aplikasi
define('IS_DEV_ENV', $is_localhost);

// Log informasi untuk debugging
if (IS_DEV_ENV) {
    error_log('App berjalan di mode development (localhost)');
    
    // Set header untuk development
    header('Access-Control-Allow-Origin: *'); // Allow CORS in development
    header('X-Development-Mode: true');       // Custom header to identify dev mode
}
?>
