<?php
// Definisikan base URL
// Untuk pengembangan lokal
// $base_url = 'http://localhost/antrian%20pasien';

// Untuk server produksi (uncomment dan sesuaikan saat deploy ke hosting)
$base_url = 'https://www.praktekobgin.com'; // Ganti dengan domain Anda yang sebenarnya

// Atau gunakan cara dinamis yang lebih aman (opsional)
// $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
// $host = $_SERVER['HTTP_HOST'];
// $base_url = $protocol . $host; // Tidak perlu menambahkan '/antrian%20pasien' di public_html

// Pastikan tidak ada trailing slash di akhir URL
$base_url = rtrim($base_url, '/');
