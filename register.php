<?php
session_start();
require_once 'config_auth.php';
require_once 'security_helpers.php';
require_once 'config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request, silakan coba lagi';
    } else {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Check rate limiting
        $rate_limit = checkRateLimit($_SERVER['REMOTE_ADDR'], 'register');
        if (!$rate_limit['allowed']) {
            $error = $rate_limit['message'];
        } else {
            // Validate input
            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = 'Semua field harus diisi';
            } else {
                // Validate password strength
                $password_check = isPasswordStrong($password);
                if (!$password_check['valid']) {
                    $error = $password_check['message'];
                }
                // Validate email
                else {
                    $email_check = isEmailValid($email);
                    if (!$email_check['valid']) {
                        $error = $email_check['message'];
                    }
                    // Check password match
                    elseif ($password !== $confirm_password) {
                        $error = 'Password tidak cocok';
                    } else {
                        try {
                            // Check if username or email already exists
                            $stmt = $auth_conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                            $stmt->execute([$username, $email]);

                            if ($stmt->rowCount() > 0) {
                                $error = 'Username atau email sudah terdaftar';
                            } else {
                                // Generate email verification token
                                $verification_token = bin2hex(random_bytes(32));
                                $token_hash = hash('sha256', $verification_token);
                                $token_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

                                // Hash password and insert new user
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $stmt = $auth_conn->prepare("INSERT INTO users (username, email, password, role, status, email_verification_token, email_verification_expires) VALUES (?, ?, ?, 'user', 'pending', ?, ?)");
                                $stmt->execute([$username, $email, $hashed_password, $token_hash, $token_expires]);

                                $user_id = $auth_conn->lastInsertId();

                                // Log registration
                                logActivity($user_id, 'register', 'Pendaftaran berhasil');

                                // Send verification email
                                $verification_link = "http://{$_SERVER['HTTP_HOST']}/verify_email.php?token=" . urlencode($verification_token);
                                $to = $email;
                                $subject = "Verifikasi Email - Sistem Antrian Pasien";
                                $message = "Terima kasih telah mendaftar di Sistem Antrian Pasien.\n\n";
                                $message .= "Silakan klik link berikut untuk memverifikasi email Anda:\n";
                                $message .= $verification_link . "\n\n";
                                $message .= "Link ini akan kadaluarsa dalam 24 jam.\n";
                                $headers = "From: noreply@example.com";

                                if (mail($to, $subject, $message, $headers)) {
                                    $success = 'Pendaftaran berhasil! Silakan cek email Anda untuk verifikasi.';
                                } else {
                                    $error = 'Gagal mengirim email verifikasi. Silakan hubungi admin.';
                                }
                            }
                        } catch (PDOException $e) {
                            $error = 'Pendaftaran gagal. Silakan coba lagi nanti.';
                            error_log("Registration error: " . $e->getMessage());
                        }
                    }
                }
            }
        }
    }
}

// Generate new CSRF token
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Antrian Pasien</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }

        .register-form {
            max-width: 400px;
            padding: 15px;
            margin: auto;
            margin-top: 100px;
        }

        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .password-strength {
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="register-form">
            <h2 class="text-center mb-4">Register</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST" action="" autocomplete="off" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3 position-relative">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                    <div class="password-strength" id="passwordStrength"></div>
                </div>
                <div class="mb-3 position-relative">
                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <i class="bi bi-eye password-toggle" id="toggleConfirmPassword"></i>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
                <div class="text-center mt-3">
                    <p>Sudah punya akun? <a href="<?= $base_url ?>/login.php">Login di sini</a></p>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePasswordVisibility(inputId, toggleId) {
            const input = document.getElementById(inputId);
            const toggle = document.getElementById(toggleId);

            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                toggle.classList.toggle('bi-eye');
                toggle.classList.toggle('bi-eye-slash');
            });
        }

        togglePasswordVisibility('password', 'togglePassword');
        togglePasswordVisibility('confirm_password', 'toggleConfirmPassword');

        // Password strength checker
        const password = document.getElementById('password');
        const strengthMeter = document.getElementById('passwordStrength');

        password.addEventListener('input', function() {
            const val = password.value;
            let strength = 0;
            let message = '';

            if (val.length >= 8) strength++;
            if (val.match(/[A-Z]/)) strength++;
            if (val.match(/[a-z]/)) strength++;
            if (val.match(/[0-9]/)) strength++;
            if (val.match(/[^A-Za-z0-9]/)) strength++;

            switch (strength) {
                case 0:
                case 1:
                    message = '<span class="text-danger">Sangat Lemah</span>';
                    break;
                case 2:
                    message = '<span class="text-warning">Lemah</span>';
                    break;
                case 3:
                    message = '<span class="text-info">Sedang</span>';
                    break;
                case 4:
                    message = '<span class="text-primary">Kuat</span>';
                    break;
                case 5:
                    message = '<span class="text-success">Sangat Kuat</span>';
                    break;
            }

            strengthMeter.innerHTML = 'Kekuatan Password: ' + message;
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;

            if (password !== confirm) {
                e.preventDefault();
                alert('Password tidak cocok!');
            }
        });
    </script>
</body>

</html>