<?php
/**
 * No Cache Header Setting
 * 
 * File ini digunakan untuk mengatur header yang mencegah caching saat di development mode
 */

// Deteksi jika aplikasi berjalan di localhost
$is_localhost = isset($_SERVER['HTTP_HOST']) && 
                (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
                 strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);

// Jika di localhost, set header no-cache
if ($is_localhost) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Tanggal di masa lalu
}
?>
