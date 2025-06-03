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
            background-color: #f8f9fa;
            overflow-x: hidden; /* Prevent horizontal scrollbar */
        }
        
        /* Main Content Layout */
        .main-content {
            margin-left: 240px;
            padding: 20px;
            transition: margin-left 0.3s ease, width 0.3s ease;
            width: calc(100% - 240px); /* Width minus sidebar width */
            box-sizing: border-box;
        }
        
        /* Adjust main content when sidebar is minimized */
        .sidebar.minimized ~ .main-content {
            margin-left: 60px;
            width: calc(100% - 60px); /* Width minus minimized sidebar width */
        }
        
        /* Mobile adjustments */
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
        
        /* Card Styles */
        .service-card {
            height: 100%;
            transition: all 0.3s ease;
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            margin-bottom: 10px;
        }

        .service-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.08);
        }
        
        /* Category Styling - Hidden as requested */
        .category-section {
            margin-bottom: 1.5rem;
            background-color: transparent;
            box-shadow: none;
            padding: 0;
        }

        /* Card Hover and Clickable Styling */
        .service-card {
            cursor: pointer;
            position: relative;
        }
        
        .service-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(13, 110, 253, 0.05);
            opacity: 0;
            transition: opacity 0.2s ease;
            border-radius: 10px;
        }
        
        .service-card:hover::after {
            opacity: 1;
        }

        /* Text Styling */
        .service-description {
            color: #6c757d;
            font-size: 0.85rem;
            line-height: 1.4;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        @media (max-width: 767.98px) {
            .service-description {
                margin-bottom: 6px;
                font-size: 0.8rem;
                line-height: 1.3;
            }
        }

        .service-preparation {
            font-size: 0.8rem;
            background-color: #f0f7ff;
            border-radius: 6px;
            padding: 8px 10px;
            margin-top: 8px;
            border-left: 3px solid #0d6efd;
        }
        
        @media (max-width: 767.98px) {
            .service-preparation {
                padding: 6px 8px;
                margin-top: 6px;
                font-size: 0.75rem;
            }
        }

        .service-duration {
            font-size: 0.75rem;
            color: #6c757d;
            display: flex;
            align-items: center;
        }
        
        .service-duration i {
            margin-right: 4px;
            color: #6c757d;
        }

        .service-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #198754;
        }

        /* Layout Styling */
        .category-icon {
            display: none; /* Hide category icon as requested */
        }
        
        /* Grid Layout */
        .services-grid {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -5px;
        }
        
        .service-item {
            padding: 0 5px;
            margin-bottom: 10px;
        }
        
        @media (max-width: 767.98px) {
            .service-item {
                margin-bottom: 8px;
            }
        }
        
        /* Desktop - 4 columns */
        @media (min-width: 992px) {
            .service-item {
                width: 25%;
            }
        }
        
        /* Tablet - 3 columns */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .service-item {
                width: 33.333%;
            }
        }
        
        /* Mobile - 2 columns */
        @media (max-width: 767.98px) {
            .service-item {
                width: 50%;
            }
        }

        .card-title {
            margin-top: 0;
            margin-bottom: 8px;
            font-weight: 700;
            color: #2c3e50;
            font-size: 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-body {
            padding: 1rem;
        }

        .service-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #f0f0f0;
        }
        
        @media (max-width: 767.98px) {
            .service-footer {
                margin-top: 6px;
                padding-top: 6px;
            }
        }
        
        /* Mobile Optimizations */
        @media (max-width: 767.98px) {
            .main-content {
                padding: 10px;
            }
            
            .mobile-hide {
                display: none;
            }
            
            .col-6 {
                padding-left: 5px;
                padding-right: 5px;
            }
            
            .row.g-3 {
                margin-left: -5px;
                margin-right: -5px;
            }
            
            .card-body {
                padding: 0.75rem 0.75rem 0.5rem;
            }
            
            .service-card {
                margin-bottom: 10px;
            }
            
            .container-fluid {
                padding: 0;
            }
            
            .category-section {
                padding: 15px;
                margin-bottom: 20px;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .row.g-4 {
                margin-left: -8px;
                margin-right: -8px;
            }
            
            .col-12, .col-md-6, .col-lg-4 {
                padding-left: 8px;
                padding-right: 8px;
            }
            
            .service-card {
                margin-bottom: 16px;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .category-title {
                font-size: 1.3rem;
            }
        }
        
        /* Animation */
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
            <div class="row mb-3 mb-md-4">
                <div class="col-12">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-clipboard-pulse text-primary me-2" style="font-size: 1.75rem;"></i>
                        <h2 class="page-title mb-0">Layanan Kami</h2>
                    </div>
                    <p class="text-muted mobile-hide">Temukan berbagai layanan kesehatan yang kami sediakan untuk Anda</p>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <?php foreach ($layanan_by_kategori as $kategori => $items): ?>
                <div class="category-section">

                    <div class="row">
                        <div class="col-12">
                            <div class="services-grid">
                                <?php foreach ($items as $item): ?>
                                <div class="service-item">
                                    <div class="card service-card" onclick="redirectToRegistration('<?= $item['id_layanan'] ?>')">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($item['nama_layanan']) ?></h5>

                                            <?php if (!empty($item['deskripsi'])): ?>
                                                <p class="service-description"><?= nl2br(htmlspecialchars($item['deskripsi'])) ?></p>
                                            <?php endif; ?>

                                            <?php if (!empty($item['persiapan'])): ?>
                                                <div class="service-preparation">
                                                    <small><i class="bi bi-info-circle"></i> <strong>Persiapan:</strong></small>
                                                    <div class="mt-1"><?= nl2br(htmlspecialchars($item['persiapan'])) ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <div class="service-footer">
                                                <?php if ($item['durasi_estimasi']): ?>
                                                    <span class="service-duration">
                                                        <i class="bi bi-clock"></i> <?= $item['durasi_estimasi'] ?> menit
                                                    </span>
                                                <?php else: ?>
                                                    <span></span>
                                                <?php endif; ?>

                                                <span class="service-price"><?= formatRupiah($item['harga']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($layanan_by_kategori)): ?>
                <div class="text-center py-5 bg-white rounded shadow-sm">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="mt-3">Belum ada layanan tersedia</h4>
                    <p class="text-muted">Silakan cek kembali di lain waktu</p>
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