<?php
// Definisikan base URL
// Untuk pengembangan lokal
$base_url = 'http://localhost/antrian%20pasien';

// Untuk server produksi (uncomment dan sesuaikan saat deploy ke hosting)
// $base_url = 'https://www.domain-anda.com';

// Atau gunakan cara dinamis yang lebih aman (opsional)
// $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
// $host = $_SERVER['HTTP_HOST'];
// $base_url = $protocol . $host . '/antrian%20pasien'; // Sesuaikan path jika perlu

// Pastikan tidak ada trailing slash di akhir URL
$base_url = rtrim($base_url, '/');
