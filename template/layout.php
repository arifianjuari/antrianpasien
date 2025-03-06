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

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (if needed) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <?php if (isset($additional_js)): ?>
        <script>
            <?php echo $additional_js; ?>
        </script>
    <?php endif; ?>

    <?php echo isset($additional_scripts) ? $additional_scripts : ''; ?>
</body>

</html>