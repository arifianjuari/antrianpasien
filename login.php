<?php
require_once 'error_display.php';

// Periksa apakah session sudah dimulai dengan cara yang kompatibel dengan berbagai versi PHP
if (function_exists('session_status')) {
    // PHP 5.4.0 atau lebih baru
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
} else {
    // PHP versi lama
    if (!headers_sent()) {
        @session_start();
    }
}

require_once 'config/koneksi.php';
require_once 'security_helpers.php';
require_once 'config/config.php';

$error = '';
$success = '';

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token']) && isset($_COOKIE['remember_user'])) {
    $remember_token = $_COOKIE['remember_token'];
    $user_id = $_COOKIE['remember_user'];

    if ($token_data = validateRememberToken($user_id, $remember_token)) {
        $stmt = $pdo->prepare("SELECT id, username, role, status FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['status'] === 'active') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Regenerate remember me token for security
            setRememberMeCookie($user['id'], generateRememberToken());
            header('Location: ' . $base_url . '/pendaftaran/form_pendaftaran_pasien.php');
            exit();
        }
    }
}

// Jika sudah login, redirect ke halaman utama
if (isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . '/pendaftaran/form_pendaftaran_pasien.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request, silakan coba lagi';
    } else {
        // Check rate limiting
        $rate_limit = checkRateLimit($_SERVER['REMOTE_ADDR']);
        if (!$rate_limit['allowed']) {
            $error = $rate_limit['message'];
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, username, password, role, status, email_verified FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    if ($user['status'] === 'active') {
                        if ($user['email_verified']) {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['role'] = $user['role'];

                            // Set remember me cookie if requested
                            if ($remember_me) {
                                setRememberMeCookie($user['id'], generateRememberToken());
                            }

                            // Log successful login
                            logActivity($user['id'], 'Login', 'Login berhasil');

                            header('Location: ' . $base_url . '/pendaftaran/form_pendaftaran_pasien.php');
                            exit();
                        } else {
                            $error = 'Silakan verifikasi email Anda terlebih dahulu';
                        }
                    } else {
                        $error = 'Akun Anda menunggu persetujuan atau telah ditolak';
                    }
                } else {
                    $error = 'Username atau password salah';
                    // Log failed login attempt
                    logActivity(0, 'Login gagal', "Username: $username");
                }
            } catch (PDOException $e) {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
                error_log("Login error: " . $e->getMessage());
            }
        }
    }
}

// Generate new CSRF token
$csrf_token = generateCSRFToken();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Antrian Pasien</title>

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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            max-width: 400px;
            padding: 2rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 38px;
            cursor: pointer;
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
    </style>
</head>

<body>
    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">Login</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST" action="" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3 position-relative">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                    <label class="form-check-label" for="remember_me">Ingat saya</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <div class="text-center mt-3">
                    <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                    <p><a href="forgot_password.php">Lupa password?</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Tombol Install PWA -->
    <button id="install-button">Instal Aplikasi</button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- PWA Script -->
    <script src="/assets/pwa/pwa.js"></script>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    </script>
</body>

</html>