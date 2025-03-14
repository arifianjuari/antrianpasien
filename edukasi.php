<?php
// Memulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/config.php';
require_once 'config/database.php';

// Ambil parameter kategori dari URL jika ada
$selected_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Ambil parameter pencarian dari URL jika ada
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Daftar kategori
$kategori_list = [
    'onkogin' => 'Onkogin',
    'endokrin' => 'Endokrin',
    'infertilitas' => 'Infertilitas',
    'fetomaternal' => 'Fetomaternal',
    'urogin' => 'Urogin',
    'tips_kesehatan' => 'Tips Kesehatan'
];

try {
    // Buat query dasar
    $query = "SELECT * FROM edukasi WHERE status_aktif = 1";
    $params = [];

    // Tambahkan filter kategori jika ada
    if (!empty($selected_kategori)) {
        $query .= " AND kategori = :kategori";
        $params[':kategori'] = $selected_kategori;
    }

    // Tambahkan pencarian jika ada
    if (!empty($search)) {
        $query .= " AND (judul LIKE :search OR isi_edukasi LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Tambahkan pengurutan
    $query .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $artikels = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
    $artikels = [];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edukasi Kesehatan - Sistem Antrian Pasien</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= $base_url ?>/assets/css/styles.css" rel="stylesheet">

    <style>
        .article-card {
            height: 100%;
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .article-category {
            position: absolute;
            bottom: 10px;
            right: 10px;
        }

        .article-summary {
            color: #6c757d;
            font-size: 0.9rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .article-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 2.5rem;
        }

        .category-filter {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            gap: 0.5rem;
            padding: 0.5rem 0;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .category-filter::-webkit-scrollbar {
            display: none;
        }

        .category-filter .btn {
            white-space: nowrap;
            flex-shrink: 0;
        }

        .search-form {
            position: relative;
            z-index: 1;
        }

        .search-form .input-group {
            width: 100%;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .page-title-section {
            flex: 1;
        }

        .search-section {
            width: 300px;
            margin-left: 1rem;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
            }

            .search-section {
                width: 100%;
                margin-left: 0;
                margin-top: 1rem;
            }

            .search-form {
                max-width: 100% !important;
            }
        }
    </style>
</head>

<body>
    <?php include_once 'template/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Header dengan Search -->
            <div class="page-header">
                <div class="page-title-section">
                    <h2 class="page-title">Edukasi Kesehatan</h2>
                    <p class="text-muted">Temukan berbagai artikel informatif seputar kesehatan</p>
                </div>
                <div class="search-section">
                    <form action="" method="GET" class="search-form">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search"
                                placeholder="Cari artikel..." value="<?= htmlspecialchars($search) ?>">
                            <?php if (!empty($selected_kategori)): ?>
                                <input type="hidden" name="kategori" value="<?= htmlspecialchars($selected_kategori) ?>">
                            <?php endif; ?>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Kategori Filter -->
            <div class="category-filter mb-4">
                <a href="<?= $base_url ?>/edukasi.php"
                    class="btn <?= empty($selected_kategori) ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Semua
                </a>
                <?php foreach ($kategori_list as $key => $nama_kategori): ?>
                    <a href="<?= $base_url ?>/edukasi.php?kategori=<?= urlencode($key) ?>"
                        class="btn <?= $selected_kategori === $key ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <?= $nama_kategori ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <?php foreach ($artikels as $artikel): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card article-card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <a href="<?= $base_url ?>/edukasi/<?= htmlspecialchars($artikel['slug']) ?>"
                                        class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($artikel['judul']) ?>
                                    </a>
                                </h5>

                                <?php if (!empty($artikel['isi_edukasi'])): ?>
                                    <p class="article-summary mb-3">
                                        <?= htmlspecialchars(substr(strip_tags($artikel['isi_edukasi']), 0, 150)) . '...' ?>
                                    </p>
                                <?php endif; ?>

                                <div class="article-meta">
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('d F Y', strtotime($artikel['created_at'])) ?>
                                </div>

                                <div class="mt-3">
                                    <a href="<?= $base_url ?>/edukasi-detail.php?id=<?= htmlspecialchars($artikel['id_edukasi']) ?>"
                                        class="btn btn-outline-primary btn-sm">
                                        Baca Selengkapnya
                                    </a>
                                </div>

                                <span class="badge bg-primary article-category">
                                    <?= htmlspecialchars($artikel['kategori']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($artikels)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-journal-x display-1 text-muted"></i>
                    <h4 class="mt-3">Belum ada artikel</h4>
                    <?php if (!empty($search)): ?>
                        <p class="text-muted">Tidak ditemukan artikel yang sesuai dengan pencarian Anda</p>
                        <a href="<?= $base_url ?>/edukasi.php" class="btn btn-primary mt-2">
                            Lihat Semua Artikel
                        </a>
                    <?php else: ?>
                        <p class="text-muted">Silakan cek kembali di lain waktu</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>