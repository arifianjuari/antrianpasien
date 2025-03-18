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
    <link rel="icon" type="image/png" href="/assets/pwa/icons/praktekobgin_icon192.png">
    <link rel="apple-touch-icon" href="/assets/pwa/icons/praktekobgin_icon192.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #198754;
            --secondary-color: #0d6efd;
            --accent-color: #f8f9fa;
            --text-color: #333;
            --border-radius: 12px;
        }

        body {
            background-color: #f5f5f5;
            background-image: linear-gradient(135deg, #f8f9fa 0%, #d4edda 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', Arial, sans-serif;
            padding: 20px 0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 2.8rem;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .login-container:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .header-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-align: center;
            font-size: 2rem;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: #6c757d;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .form-control {
            border-radius: var(--border-radius);
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.15);
        }

        .input-group-text {
            border-radius: var(--border-radius) 0 0 var(--border-radius);
            background-color: #f8f9fa;
        }

        .form-control {
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 38px;
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: var(--border-radius);
            padding: 12px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background-color: #146c43;
            border-color: #146c43;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(20, 108, 67, 0.2);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .links-container {
            display: flex;
            justify-content: space-between;
            margin-top: 1.8rem;
            font-size: 0.9rem;
        }

        .links-container a {
            color: var(--secondary-color);
            text-decoration: none;
            transition: all 0.3s;
            position: relative;
        }

        .links-container a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: -2px;
            left: 0;
            background-color: var(--secondary-color);
            transition: width 0.3s;
        }

        .links-container a:hover::after {
            width: 100%;
        }

        .links-container a:hover {
            color: #0a58ca;
        }

        .alert {
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid transparent;
        }

        .alert-danger {
            border-left-color: #dc3545;
            background-color: #f8d7da;
        }

        .alert-success {
            border-left-color: var(--primary-color);
            background-color: #d1e7dd;
        }

        /* Tombol Install PWA */
        #install-button {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            padding: 12px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        #install-button:hover {
            background-color: #146c43;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 2rem 1.5rem;
                margin: 0 15px;
            }

            .links-container {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-container">
            <div class="header-container">
                <h2>Selamat Datang</h2>
                <p class="subtitle">Silakan login untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="mb-4">
                    <label for="username" class="form-label fw-medium">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Masukkan username" required autocomplete="username">
                    </div>
                </div>

                <div class="mb-4 position-relative">
                    <label for="password" class="form-label fw-medium">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                    </div>
                    <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                        <label class="form-check-label" for="remember_me">Ingat saya</label>
                    </div>
                    <a href="forgot_password.php" class="text-decoration-none" style="font-size: 0.9rem; color: var(--secondary-color);">Lupa password?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-4 d-flex align-items-center justify-content-center">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>

                <div class="text-center">
                    <p class="mb-0" style="color: #6c757d;">Belum punya akun? <a href="register.php" class="fw-medium">Daftar Sekarang</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Tombol Install PWA -->
    <button id="install-button">
        <i class="bi bi-download me-2"></i>Instal Aplikasi
    </button>

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

        // Animasi sederhana saat loading
        document.addEventListener('DOMContentLoaded', function() {
            const loginContainer = document.querySelector('.login-container');
            loginContainer.style.opacity = '0';
            loginContainer.style.transform = 'translateY(20px)';

            setTimeout(() => {
                loginContainer.style.opacity = '1';
                loginContainer.style.transform = 'translateY(0)';
            }, 200);
            
            // Fokus ke field username setelah animasi
            setTimeout(() => {
                document.getElementById('username').focus();
            }, 300);
        });
        
        // Validasi form sederhana
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (username === '' || password === '') {
                e.preventDefault();
                alert('Mohon isi semua field yang diperlukan');
            }
        });
    </script>
</body>

</html>