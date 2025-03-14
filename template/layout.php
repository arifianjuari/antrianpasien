<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration if not already included
if (!isset($conn)) {
    require_once __DIR__ . '/../config/database.php';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Klinik App'; ?></title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#198754">
    <meta name="description" content="Aplikasi Antrian Pasien">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Antrian Pasien">

    <!-- PWA Icons -->
    <link rel="manifest" href="/assets/pwa/manifest.json">
    <link rel="icon" type="image/png" href="/assets/pwa/icons/icon-192x192.png">
    <link rel="apple-touch-icon" href="/assets/pwa/icons/icon-192x192.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        .main-content {
            margin-left: 280px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
            }
        }

        .sidebar.minimized+.main-content {
            margin-left: 60px;
        }

        /* Perbaikan tampilan alert */
        .alert-dismissible {
            position: relative;
            padding-right: 3.5rem;
        }

        .alert-dismissible .btn-close {
            position: absolute;
            top: 0.5rem;
            right: 1rem;
            padding: 0.5rem;
            color: inherit;
        }

        .alert-success {
            border-left: 4px solid #198754;
        }

        .alert-danger {
            border-left: 4px solid #dc3545;
        }

        /* Tombol Install PWA */
        #install-button {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            padding: 10px 15px;
            background-color: #198754;
            color: white;
            border: none;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        <?php echo isset($additional_css) ? $additional_css : ''; ?>
    </style>
    <?php echo isset($additional_head) ? $additional_head : ''; ?>
</head>

<body>
    <?php
    // Include sidebar
    require_once __DIR__ . '/sidebar.php';
    ?>

    <div class="main-content">
        <?php
        // Display any flash messages
        if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php
        if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php echo $content; ?>
    </div>

    <!-- Tombol Install PWA -->
    <button id="install-button">Instal Aplikasi</button>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (if needed) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- PWA Script -->
    <script src="/assets/pwa/pwa.js"></script>

    <?php if (isset($additional_js)): ?>
        <script>
            <?php echo $additional_js; ?>
        </script>
    <?php endif; ?>

    <?php echo isset($additional_scripts) ? $additional_scripts : ''; ?>
</body>

</html>