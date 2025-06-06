<?php
// Memulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/config.php';
require_once 'config/database.php';

// Fungsi untuk memformat harga
function formatRupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Cek status login
$is_logged_in = isset($_SESSION['user_id']);

// Ambil data layanan yang aktif
try {
    $stmt = $conn->query("SELECT * FROM menu_layanan WHERE status_aktif = 1 ORDER BY kategori, nama_layanan ASC");
    $layanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Kelompokkan layanan berdasarkan kategori
    $layanan_by_kategori = [];
    foreach ($layanan as $item) {
        $layanan_by_kategori[$item['kategori']][] = $item;
    }
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
    $layanan_by_kategori = [];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d6efd">
    <title>Layanan - Sistem Antrian Pasien</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= $base_url ?>/assets/css/styles.css" rel="stylesheet">

    <style>
        /* Base Styles */
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f4f7f6; /* Lighter, softer background */
            overflow-x: hidden;
        }

        /* Main Content Layout */
        .main-content {
            margin-left: 240px;
            padding: 24px;
            transition: margin-left 0.3s ease, width 0.3s ease;
            width: calc(100% - 240px);
            box-sizing: border-box;
        }

        .sidebar.minimized ~ .main-content {
            margin-left: 60px;
            width: calc(100% - 60px);
        }

        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 16px;
            }
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
        }
        .page-header .page-title-icon {
            font-size: 2.25rem; /* Larger icon */
            color: #0d6efd;
        }
        .page-header .page-title {
            font-size: 1.8rem; /* Larger title */
            font-weight: 700;
            color: #343a40;
        }
        .page-header .page-subtitle {
            font-size: 1rem;
            color: #6c757d;
        }

        /* Category Section (if made visible later) */
        .category-section {
            margin-bottom: 2.5rem;
        }
        .category-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        /* New Service Card Styles */
        .service-card {
            background-color: #fff;
            border: 1px solid #e0e7ef; /* Softer border */
            border-radius: 12px; /* More rounded corners */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); /* Softer, more diffused shadow */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%; /* Ensure cards in a row have same height if using flex */
            display: flex;
            flex-direction: column;
            cursor: pointer;
            overflow: hidden; /* To contain potential image or top bar */
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        .service-card .card-body {
            padding: 1.25rem; /* More padding */
            display: flex;
            flex-direction: column;
            flex-grow: 1; /* Allows footer to stick to bottom */
        }

        .service-card .card-title {
            font-size: 1.15rem; /* Slightly larger */
            font-weight: 600; /* Bolder */
            color: #2c3e50;
            margin-bottom: 0.75rem;
            /* Removed white-space, overflow, text-overflow for now, can be re-added if titles are too long */
        }

        .service-card .service-description {
            font-size: 0.9rem;
            color: #5a6570;
            line-height: 1.6;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3; /* Allow 3 lines */
            line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1; /* Takes available space before footer */
        }

        .service-card .service-preparation {
            font-size: 0.8rem;
            background-color: #e9f5ff; /* Light blue */
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
            border-left: 3px solid #007bff; /* Primary blue accent */
            color: #345A7C;
        }
        .service-card .service-preparation strong {
             color: #0056b3;
        }
        .service-card .service-preparation div {
            font-size: 0.8rem;
        }

        .service-card .service-footer {
            margin-top: auto; /* Pushes footer to the bottom */
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0; /* Lighter separator */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .service-card .service-duration {
            font-size: 0.85rem;
            color: #6c757d;
            display: flex;
            align-items: center;
        }
        .service-card .service-duration i {
            margin-right: 0.3rem;
            color: #0d6efd;
        }

        .service-card .service-price {
            font-size: 1.25rem; /* Larger price */
            font-weight: 700;
            color: #007bff; /* Primary color for price */
        }
        
        /* Grid Layout (using Bootstrap classes is preferred, but this is a fallback/enhancement) */
        /* The .services-grid and .service-item classes might be replaced by Bootstrap's row/col */
        /* .services-grid {
            /* If not using Bootstrap row, uncomment and adjust */
            /* display: flex; flex-wrap: wrap; margin-left: -12px; margin-right: -12px; */
        /* } */
        /* .service-item {
            /* If not using Bootstrap col-*, uncomment and adjust padding */
            /* padding-left: 12px; padding-right: 12px; margin-bottom: 24px; */
        /* } */

        /* Fallback for non-Bootstrap grid - adjust as needed if Bootstrap grid is not used for cards */
        @media (max-width: 767.98px) { /* sm and xs */
            /* .service-item { width: 100%; } */
             .page-header .page-title {
                font-size: 1.5rem;
            }
            .page-header .page-subtitle {
                font-size: 0.9rem;
            }
            .service-card .card-title {
                font-size: 1.05rem;
            }
            .service-card .service-description {
                font-size: 0.85rem;
                 -webkit-line-clamp: 2;
                line-clamp: 2;
            }
            .service-card .service-price {
                font-size: 1.1rem;
            }
        }

        /* Modal Styling (copied from original, can be refined if needed) */
        .modal-body .service-detail-item {
            margin-bottom: 0.75rem;
        }
        .modal-body .service-detail-item strong {
            display: block;
            color: #343a40;
            margin-bottom: 0.25rem;
        }
        .modal-body .service-detail-item p {
            margin-bottom: 0;
            color: #495057;
        }
        .modal-body .badge {
            font-size: 0.9rem;
        }

        /* Animation for cards */
        .service-card {
            animation: fadeInScaleUp 0.4s ease-out forwards;
            opacity: 0;
            transform: scale(0.98);
        }
        @keyframes fadeInScaleUp {
            from {
                opacity: 0;
                transform: scale(0.98) translateY(15px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        /* Ensure animation delay is still applied by JS if needed */
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .service-card {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>

<body>
    <?php include_once 'template/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="bi bi-clipboard-heart page-title-icon"></i>
                    </div>
                    <div class="col">
                        <h1 class="page-title mb-1">Layanan Kami</h1>
                        <p class="page-subtitle text-muted">Temukan berbagai layanan kesehatan berkualitas yang kami sediakan untuk Anda.</p>
                    </div>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($layanan_by_kategori)): ?>
                <?php foreach ($layanan_by_kategori as $kategori => $items): ?>
                    <div class="category-section">
                        <h2 class="category-title"><?= htmlspecialchars($kategori) ?></h2>
                        <div class="row g-4">
                            <?php foreach ($items as $item): ?>
                            <div class="col-lg-4 col-md-6 col-sm-12 d-flex align-items-stretch">
                                <div class="card service-card h-100" onclick=\"redirectToRegistration('<?= htmlspecialchars($item['id_layanan']) ?>')\">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?= htmlspecialchars($item['nama_layanan']) ?></h5>
                                        
                                        <?php if (!empty($item['deskripsi'])): ?>
                                            <p class="service-description">
                                                <?= nl2br(htmlspecialchars(mb_strimwidth($item['deskripsi'], 0, 120, "..."))) ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($item['persiapan'])): ?>
                                            <div class="service-preparation">
                                                <small><i class="bi bi-info-circle-fill me-1"></i><strong>Persiapan:</strong></small>
                                                <div class="mt-1 small">
                                                    <?= nl2br(htmlspecialchars(mb_strimwidth($item['persiapan'], 0, 100, "..."))) ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="service-footer mt-auto">
                                            <?php if ($item['durasi_estimasi']): ?>
                                                <span class="service-duration">
                                                    <i class="bi bi-clock-history"></i> <?= htmlspecialchars($item['durasi_estimasi']) ?> menit
                                                </span>
                                            <?php else: ?>
                                                <span class="service-duration"><i class="bi bi-clock-history"></i> Estimasi variatif</span>
                                            <?php endif; ?>
                                            <span class="service-price"><?= formatRupiah($item['harga']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5 my-5 bg-light rounded shadow-sm">
                    <i class="bi bi-inbox-fill display-1 text-info mb-3"></i>
                    <h4 class="mt-3 fw-bold">Belum Ada Layanan Tersedia</h4>
                    <p class="text-muted fs-5">Saat ini belum ada layanan yang dapat ditampilkan. Silakan cek kembali di lain waktu.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS for mobile optimization -->
    <script>
    // Function to redirect to registration form
    function redirectToRegistration(layananId) {
        <?php if ($is_logged_in): ?>
            window.location.href = '<?= $base_url ?>/pendaftaran/form_pendaftaran_pasien.php?layanan=' + layananId;
        <?php else: ?>
            window.location.href = '<?= $base_url ?>/login.php?redirect=<?= urlencode($_SERVER["REQUEST_URI"]) ?>';
        <?php endif; ?>
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Add staggered animation to cards
        const cards = document.querySelectorAll('.service-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
        
        // Add touch feedback for mobile
        cards.forEach(card => {
            card.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
            });
            card.addEventListener('touchend', function() {
                this.style.transform = '';
            });
        });
    });
    </script>
</body>

</html>