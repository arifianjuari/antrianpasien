<?php
// Memulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/google_drive.php';

// Ambil ID artikel dari URL
$id_edukasi = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id_edukasi)) {
    header("Location: edukasi.php");
    exit;
}

try {
    // Ambil detail artikel
    $query = "SELECT * FROM edukasi WHERE id_edukasi = :id AND status_aktif = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $id_edukasi]);
    $artikel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$artikel) {
        header("Location: edukasi.php");
        exit;
    }
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
    error_log($error_message);
    header("Location: edukasi.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artikel['judul']) ?> - Sistem Antrian Pasien</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="assets/css/styles.css" rel="stylesheet">

    <style>
        .article-header {
            position: relative;
            margin-bottom: 2rem;
        }

        .article-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 1.5rem;
        }

        .article-category {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            padding: 0.5em 1em;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .article-meta {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .article-meta i {
            margin-right: 0.25rem;
        }

        .article-meta span {
            margin-right: 1rem;
        }

        .article-content {
            line-height: 1.8;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .article-tags {
            margin: 2rem 0;
        }

        .article-tags .badge {
            margin-right: 0.5rem;
            padding: 0.5em 1em;
            font-size: 0.9rem;
        }

        .article-source {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }

        @media (max-width: 768px) {
            .article-image {
                height: 250px;
            }
        }
    </style>
</head>

<body>
    <?php include_once 'template/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <!-- Tombol Kembali -->
                    <a href="edukasi.php" class="btn btn-outline-primary mb-4">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Artikel
                    </a>

                    <article class="article-detail">
                        <header class="article-header">
                            <?php if (!empty($artikel['link_gambar'])): ?>
                                <img src="<?= getDriveImageUrl($artikel['link_gambar']) ?>"
                                    class="article-image" alt="<?= htmlspecialchars($artikel['judul']) ?>">
                            <?php endif; ?>

                            <span class="article-category">
                                <?= ucwords(htmlspecialchars($artikel['kategori'])) ?>
                            </span>

                            <h1 class="display-4 mb-3"><?= htmlspecialchars($artikel['judul']) ?></h1>

                            <div class="article-meta">
                                <span>
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('d F Y', strtotime($artikel['created_at'])) ?>
                                </span>
                                <?php if (!empty($artikel['sumber'])): ?>
                                    <span>
                                        <i class="bi bi-link-45deg"></i>
                                        <?= htmlspecialchars($artikel['sumber']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </header>

                        <div class="article-content">
                            <?= nl2br(htmlspecialchars($artikel['isi_edukasi'])) ?>
                        </div>

                        <?php if (!empty($artikel['tag'])): ?>
                            <div class="article-tags">
                                <?php foreach (explode(',', $artikel['tag']) as $tag): ?>
                                    <span class="badge bg-secondary"><?= trim(htmlspecialchars($tag)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($artikel['sumber'])): ?>
                            <div class="article-source">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Sumber: <?= htmlspecialchars($artikel['sumber']) ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </article>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>