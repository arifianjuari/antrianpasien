<?php
// Memulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/google_drive.php';

// Ambil parameter kategori dari URL jika ada
$selected_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Ambil parameter pencarian dari URL jika ada
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Daftar kategori sesuai dengan enum di database
$kategori_list = [
    'fetomaternal',
    'ginekologi umum',
    'onkogin',
    'fertilitas',
    'uroginekologi'
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
        $query .= " AND (judul LIKE :search OR isi_edukasi LIKE :search OR tag LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Urutkan berdasarkan urutan_tampil jika ditampilkan di beranda, kemudian berdasarkan created_at
    $query .= " ORDER BY CASE WHEN ditampilkan_beranda = 1 THEN urutan_tampil ELSE 999999 END, created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $artikels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Tampilkan query dan hasil
    error_log("Query: " . $query);
    error_log("Params: " . print_r($params, true));
    error_log("Results: " . print_r($artikels, true));
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
    error_log($error_message);
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
    <link href="assets/css/styles.css" rel="stylesheet">

    <style>
        .article-card {
            height: 100%;
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .article-image {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: calc(0.375rem - 1px);
            border-top-right-radius: calc(0.375rem - 1px);
        }

        .article-category {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .article-summary {
            color: #6c757d;
            font-size: 0.9rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .article-meta {
            font-size: 0.85rem;
            color: #6c757d;
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

        .article-tags {
            margin-bottom: 1rem;
        }

        .article-tags .badge {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
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
                <?php foreach ($kategori_list as $kategori): ?>
                    <a href="<?= $base_url ?>/edukasi.php?kategori=<?= urlencode($kategori) ?>"
                        class="btn <?= $selected_kategori === $kategori ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <?= ucwords(htmlspecialchars($kategori)) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <?php if (empty($artikels)): ?>
                <div class="alert alert-info" role="alert">
                    Belum ada artikel edukasi yang tersedia.
                    <?php if (!empty($search) || !empty($selected_kategori)): ?>
                        <br>
                        Coba cari dengan kata kunci lain atau lihat semua artikel
                        <a href="<?= $base_url ?>/edukasi.php" class="alert-link">di sini</a>.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($artikels as $artikel): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card article-card">
                                <?php if (!empty($artikel['link_gambar'])): ?>
                                    <a href="artikel_detail.php?id=<?= htmlspecialchars($artikel['id_edukasi']) ?>">
                                        <img src="<?= getDriveImageUrl($artikel['link_gambar']) ?>"
                                            class="article-image" alt="<?= htmlspecialchars($artikel['judul']) ?>">
                                    </a>
                                <?php else: ?>
                                    <a href="artikel_detail.php?id=<?= htmlspecialchars($artikel['id_edukasi']) ?>">
                                        <img src="<?= $base_url ?>/assets/images/default-article.jpg"
                                            class="article-image" alt="Default Image">
                                    </a>
                                <?php endif; ?>

                                <span class="badge bg-primary article-category">
                                    <?= ucwords(htmlspecialchars($artikel['kategori'])) ?>
                                </span>

                                <div class="card-body">
                                    <h5 class="card-title mb-3">
                                        <a href="artikel_detail.php?id=<?= htmlspecialchars($artikel['id_edukasi']) ?>"
                                            class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($artikel['judul']) ?>
                                        </a>
                                    </h5>

                                    <?php if (!empty($artikel['isi_edukasi'])): ?>
                                        <p class="article-summary mb-3">
                                            <?= htmlspecialchars(substr(strip_tags($artikel['isi_edukasi']), 0, 200)) ?>...
                                        </p>
                                        <a href="artikel_detail.php?id=<?= htmlspecialchars($artikel['id_edukasi']) ?>"
                                            class="btn btn-sm btn-outline-primary mb-3">Baca Selengkapnya</a>
                                    <?php endif; ?>

                                    <?php if (!empty($artikel['tag'])): ?>
                                        <div class="article-tags">
                                            <?php foreach (explode(',', $artikel['tag']) as $tag): ?>
                                                <span class="badge bg-secondary"><?= trim(htmlspecialchars($tag)) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="article-meta">
                                        <i class="bi bi-calendar3"></i>
                                        <?= date('d F Y', strtotime($artikel['created_at'])) ?>
                                        <?php if (!empty($artikel['sumber'])): ?>
                                            <span class="ms-2">
                                                <i class="bi bi-link-45deg"></i>
                                                <?= htmlspecialchars($artikel['sumber']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>