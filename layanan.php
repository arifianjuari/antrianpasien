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
    <title>Layanan - Sistem Antrian Pasien</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= $base_url ?>/assets/css/styles.css" rel="stylesheet">

    <style>
        .service-card {
            height: 100%;
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .category-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .category-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background-color: #0d6efd;
        }

        .service-icon {
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }

        .badge-booking {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .service-description {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .service-preparation {
            font-size: 0.85rem;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }

        .service-duration {
            font-size: 0.9rem;
            color: #495057;
        }

        .service-price {
            font-size: 1.25rem;
            font-weight: bold;
            color: #198754;
        }

        .category-section {
            margin-bottom: 3rem;
        }

        .category-icon {
            font-size: 1.5rem;
            margin-right: 0.5rem;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <?php include_once 'template/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="page-title">Layanan Kami</h2>
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
                                            <i class="bi bi-calendar-check"></i> Dapat Dibooking
                                        </span>
                                    <?php endif; ?>

                                    <div class="card-body">
                                        <div class="service-icon">
                                            <?php
                                            switch ($kategori) {
                                                case 'Konsultasi':
                                                    echo '<i class="bi bi-chat-dots"></i>';
                                                    break;
                                                case 'Tindakan':
                                                    echo '<i class="bi bi-bandaid"></i>';
                                                    break;
                                                case 'Pemeriksaan':
                                                    echo '<i class="bi bi-clipboard2-pulse"></i>';
                                                    break;
                                                case 'Paket':
                                                    echo '<i class="bi bi-box"></i>';
                                                    break;
                                                default:
                                                    echo '<i class="bi bi-grid"></i>';
                                            }
                                            ?>
                                        </div>

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

                                        <div class="d-flex justify-content-between align-items-center mt-3">
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
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="mt-3">Belum ada layanan tersedia</h4>
                    <p class="text-muted">Silakan cek kembali di lain waktu</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>