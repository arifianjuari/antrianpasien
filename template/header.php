<?php
// Tambahkan ini di awal file header.php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Praktek Obgin</title>

    <!-- Meta tags untuk PWA -->
    <meta name="theme-color" content="#198754">
    <meta name="description" content="Aplikasi Praktek Obgin">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Praktek Obgin">

    <!-- Favicon dan ikon PWA -->
    <link rel="icon" href="<?= $base_url ?>/assets/pwa/icons/praktekobgin_icon72x72.png">
    <link rel="apple-touch-icon" href="<?= $base_url ?>/assets/pwa/icons/praktekobgin_icon192.png">

    <!-- Manifest PWA -->
    <link rel="manifest" href="<?= $base_url ?>/assets/pwa/manifest.json">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Service Worker Registration -->
    <script src="<?= $base_url ?>/assets/pwa/register-sw.js"></script>
</head>

<body>
    <!-- ... kode lainnya ... -->
    <!-- Jika belum login, tampilkan link login yang benar -->
    <?php if (!isset($_SESSION['user_id'])): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= $base_url ?>/login.php">Login</a>
        </li>
    <?php endif; ?>
    <!-- ... kode lainnya ... -->
</body>

</html>