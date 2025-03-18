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
        }
        
        /* Card Styles */
        .service-card {
            height: 100%;
            transition: all 0.3s ease;
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 15px;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        /* Category Styling */
        .category-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 25px;
            font-weight: 700;
            color: #2c3e50;
        }

        .category-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #0d6efd, #0dcaf0);
            border-radius: 2px;
        }

        /* Badge Styling */
        .badge-booking {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 6px 10px;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 30px;
            box-shadow: 0 2px 5px rgba(13, 110, 253, 0.2);
            z-index: 2;
        }

        /* Text Styling */
        .service-description {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .service-preparation {
            font-size: 0.85rem;
            background-color: #f0f7ff;
            border-radius: 8px;
            padding: 12px 15px;
            margin-top: 15px;
            border-left: 3px solid #0d6efd;
        }

        .service-duration {
            font-size: 0.85rem;
            color: #495057;
            display: flex;
            align-items: center;
        }
        
        .service-duration i {
            margin-right: 5px;
            color: #6c757d;
        }

        .service-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #198754;
        }

        /* Layout Styling */
        .category-section {
            margin-bottom: 3rem;
            padding: 1rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .category-icon {
            font-size: 1.5rem;
            margin-right: 0.5rem;
            vertical-align: middle;
            color: #0d6efd;
        }

        .card-title {
            margin-top: 0;
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .service-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }
        
        /* Mobile Optimizations */
        @media (max-width: 767.98px) {
            .main-content {
                padding: 10px;
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
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-clipboard-pulse text-primary me-2" style="font-size: 1.75rem;"></i>
                        <h2 class="page-title mb-0">Layanan Kami</h2>
                    </div>
                    <p class="text-muted">Temukan berbagai layanan kesehatan yang kami sediakan untuk Anda</p>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <?php foreach ($layanan_by_kategori as $kategori => $items): ?>
                <div class="category-section">
                    <h3 class="category-title mb-4">
                        <i class="bi 
                        <?php
                        switch ($kategori) {
                            case 'Konsultasi':
                                echo 'bi-chat-dots';
                                break;
                            case 'Tindakan':
                                echo 'bi-bandaid';
                                break;
                            case 'Pemeriksaan':
                                echo 'bi-clipboard2-pulse';
                                break;
                            case 'Paket':
                                echo 'bi-box';
                                break;
                            default:
                                echo 'bi-grid';
                        }
                        ?> category-icon"></i>
                        <?= $kategori ?>
                    </h3>

                    <div class="row g-4">
                        <?php foreach ($items as $item): ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card service-card">
                                    <?php if ($item['dapat_dibooking']): ?>
                                        <span class="badge bg-primary badge-booking">
                                            <i class="bi bi-calendar-check me-1"></i> Dapat Dibooking
                                        </span>
                                    <?php endif; ?>

                                    <div class="card-body">
                                        <h5 class="card-title mb-3"><?= htmlspecialchars($item['nama_layanan']) ?></h5>

                                        <?php if (!empty($item['deskripsi'])): ?>
                                            <p class="service-description mb-3"><?= nl2br(htmlspecialchars($item['deskripsi'])) ?></p>
                                        <?php endif; ?>

                                        <?php if (!empty($item['persiapan'])): ?>
                                            <div class="service-preparation">
                                                <strong><i class="bi bi-info-circle"></i> Persiapan:</strong><br>
                                                <?= nl2br(htmlspecialchars($item['persiapan'])) ?>
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

                                        <?php if ($item['dapat_dibooking']): ?>
                                            <div class="mt-3">
                                                <?php if ($is_logged_in): ?>
                                                    <a href="<?= $base_url ?>/pendaftaran/form_pendaftaran_pasien.php?layanan=<?= $item['id_layanan'] ?>"
                                                        class="btn btn-primary w-100">
                                                        <i class="bi bi-calendar-plus"></i> Booking Sekarang
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?= $base_url ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                                                        class="btn btn-primary w-100">
                                                        <i class="bi bi-box-arrow-in-right"></i> Login untuk Booking
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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