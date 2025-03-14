<?php
// File untuk menguji PWA

// Fungsi untuk menampilkan status PWA
function checkPWAStatus()
{
    $status = [
        'service_worker' => file_exists(__DIR__ . '/assets/pwa/sw.js'),
        'manifest' => file_exists(__DIR__ . '/assets/pwa/manifest.json'),
        'offline_page' => file_exists(__DIR__ . '/offline.html'),
        'icons' => [
            '72x72' => file_exists(__DIR__ . '/assets/pwa/icons/praktekobgin_icon72x72.png'),
            '96x96' => file_exists(__DIR__ . '/assets/pwa/icons/praktekobgin_icon96x96.png'),
            '128' => file_exists(__DIR__ . '/assets/pwa/icons/praktekobgin_icon128.png'),
            '144' => file_exists(__DIR__ . '/assets/pwa/icons/praktekobgin_icon144.png'),
            '192' => file_exists(__DIR__ . '/assets/pwa/icons/praktekobgin_icon192.png'),
            '384' => file_exists(__DIR__ . '/assets/pwa/icons/praktekobgin_icon384.png'),
            '512' => file_exists(__DIR__ . '/assets/pwa/icons/praktekobgin_icon512.png')
        ],
        'htaccess' => file_exists(__DIR__ . '/assets/pwa/.htaccess'),
        'register_sw' => file_exists(__DIR__ . '/assets/pwa/register-sw.js')
    ];

    return $status;
}

// Cek status PWA
$pwaStatus = checkPWAStatus();

// Cek apakah semua file PWA ada
$allFilesExist = !in_array(false, array_merge(
    [$pwaStatus['service_worker'], $pwaStatus['manifest'], $pwaStatus['offline_page'], $pwaStatus['htaccess'], $pwaStatus['register_sw']],
    $pwaStatus['icons']
));

// Cek apakah browser mendukung PWA
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isAndroid = strpos($userAgent, 'Android') !== false;
$isIOS = strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false;
$isMobile = $isAndroid || $isIOS || strpos($userAgent, 'Mobile') !== false;

// Cek apakah request dari PWA
$isPWA = isset($_SERVER['HTTP_SEC_FETCH_MODE']) && $_SERVER['HTTP_SEC_FETCH_MODE'] === 'navigate';

// Tampilkan halaman
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWA Test - Praktek Obgin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="manifest" href="/assets/pwa/manifest.json">
    <meta name="theme-color" content="#198754">
    <style>
        .status-ok {
            color: #198754;
        }

        .status-error {
            color: #dc3545;
        }

        .card {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <h1 class="mb-4">PWA Test - Praktek Obgin</h1>

        <div class="card">
            <div class="card-header">
                Status PWA
            </div>
            <div class="card-body">
                <h5 class="card-title">Status Keseluruhan</h5>
                <p class="card-text <?php echo $allFilesExist ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $allFilesExist ? 'Semua file PWA tersedia' : 'Beberapa file PWA tidak tersedia'; ?>
                </p>

                <h5 class="card-title mt-3">Detail Status</h5>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Service Worker
                        <span class="badge <?php echo $pwaStatus['service_worker'] ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $pwaStatus['service_worker'] ? 'OK' : 'Missing'; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Manifest
                        <span class="badge <?php echo $pwaStatus['manifest'] ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $pwaStatus['manifest'] ? 'OK' : 'Missing'; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Offline Page
                        <span class="badge <?php echo $pwaStatus['offline_page'] ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $pwaStatus['offline_page'] ? 'OK' : 'Missing'; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        .htaccess
                        <span class="badge <?php echo $pwaStatus['htaccess'] ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $pwaStatus['htaccess'] ? 'OK' : 'Missing'; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Register SW
                        <span class="badge <?php echo $pwaStatus['register_sw'] ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $pwaStatus['register_sw'] ? 'OK' : 'Missing'; ?>
                        </span>
                    </li>
                </ul>

                <h5 class="card-title mt-3">Icons</h5>
                <ul class="list-group">
                    <?php foreach ($pwaStatus['icons'] as $size => $exists): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Icon <?php echo $size; ?>
                            <span class="badge <?php echo $exists ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $exists ? 'OK' : 'Missing'; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Browser Info
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        User Agent
                        <span><?php echo htmlspecialchars($userAgent); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Android
                        <span class="badge <?php echo $isAndroid ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $isAndroid ? 'Ya' : 'Tidak'; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        iOS
                        <span class="badge <?php echo $isIOS ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $isIOS ? 'Ya' : 'Tidak'; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Mobile
                        <span class="badge <?php echo $isMobile ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $isMobile ? 'Ya' : 'Tidak'; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        PWA Mode
                        <span class="badge <?php echo $isPWA ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $isPWA ? 'Ya' : 'Tidak'; ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Pengujian
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" id="test-offline">Uji Mode Offline</button>
                    <button class="btn btn-danger" id="test-error">Uji Error 500</button>
                    <button class="btn btn-warning" id="clear-cache">Hapus Cache</button>
                    <button class="btn btn-success" id="reload-sw">Reload Service Worker</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Register service worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/assets/pwa/sw.js', {
                        scope: '/'
                    })
                    .then(registration => {
                        console.log('ServiceWorker berhasil didaftarkan dengan scope:', registration.scope);
                    })
                    .catch(error => {
                        console.error('ServiceWorker gagal didaftarkan:', error);
                    });
            });
        }

        // Uji mode offline
        document.getElementById('test-offline').addEventListener('click', () => {
            alert('Untuk menguji mode offline, aktifkan mode pesawat pada perangkat Anda atau matikan koneksi internet, lalu refresh halaman.');
        });

        // Uji error 500
        document.getElementById('test-error').addEventListener('click', () => {
            fetch('/trigger-error.php')
                .then(response => {
                    if (!response.ok) {
                        alert('Error ' + response.status + ' berhasil dipicu!');
                    } else {
                        alert('Gagal memicu error.');
                    }
                })
                .catch(error => {
                    alert('Error berhasil dipicu: ' + error);
                });
        });

        // Hapus cache
        document.getElementById('clear-cache').addEventListener('click', () => {
            if ('caches' in window) {
                caches.keys().then(cacheNames => {
                    return Promise.all(
                        cacheNames.map(cacheName => {
                            return caches.delete(cacheName);
                        })
                    );
                }).then(() => {
                    alert('Cache berhasil dihapus!');
                    window.location.reload();
                });
            } else {
                alert('Cache API tidak didukung di browser ini.');
            }
        });

        // Reload service worker
        document.getElementById('reload-sw').addEventListener('click', () => {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(registrations => {
                    for (let registration of registrations) {
                        registration.unregister();
                    }
                }).then(() => {
                    window.location.reload();
                });
            } else {
                alert('Service Worker tidak didukung di browser ini.');
            }
        });
    </script>
</body>

</html>