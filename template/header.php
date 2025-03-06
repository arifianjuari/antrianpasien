<?php
// Tambahkan ini di awal file header.php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <!-- ... kode lainnya ... -->
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
</body>

</html>