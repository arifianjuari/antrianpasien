<?php
require_once 'config_auth.php';
require_once 'config/config.php';

$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $token_hash = hash('sha256', $token);

    try {
        // Find user with matching token that hasn't expired
        $stmt = $auth_conn->prepare("SELECT id FROM users WHERE email_verification_token = ? AND email_verification_expires > NOW() AND email_verified = 0");
        $stmt->execute([$token_hash]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Update user as verified
            $stmt = $auth_conn->prepare("UPDATE users SET email_verified = 1, email_verification_token = NULL, email_verification_expires = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Log verification
            require_once 'security_helpers.php';
            logActivity($user['id'], 'email_verified', 'Email berhasil diverifikasi');

            $success = 'Email berhasil diverifikasi! Sekarang Anda dapat login.';
        } else {
            $error = 'Token verifikasi tidak valid atau sudah kadaluarsa.';
        }
    } catch (PDOException $e) {
        $error = 'Terjadi kesalahan. Silakan coba lagi nanti.';
        error_log("Email verification error: " . $e->getMessage());
    }
} else {
    $error = 'Token verifikasi tidak ditemukan.';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Sistem Antrian Pasien</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }

        .verification-container {
            max-width: 400px;
            padding: 15px;
            margin: auto;
            margin-top: 100px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="verification-container">
            <h2 class="mb-4">Verifikasi Email</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                    <div class="mt-3">
                        <a href="<?= $base_url ?>/login.php" class="btn btn-primary">Kembali ke Login</a>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <div class="mt-3">
                        <a href="<?= $base_url ?>/login.php" class="btn btn-primary">Login Sekarang</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>