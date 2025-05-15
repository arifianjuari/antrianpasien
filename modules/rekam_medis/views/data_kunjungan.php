<?php
// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kunjungan - Praktek Obgin</title>
    <?php include 'template/header.php'; ?>
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .btn-detail {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-detail:hover {
            background-color: var(--primary-dark);
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'template/navbar.php'; ?>
    <?php include 'template/sidebar.php'; ?>

    <div class="content">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <h1 class="h3 mb-0 text-gray-800">Data Kunjungan</h1>
                    <p class="mb-0">Daftar pemeriksaan yang telah dilakukan</p>
                </div>
            </div>

            <!-- Notifikasi -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <!-- Search Box -->
                    <div class="row search-box">
                        <div class="col-md-6">
                            <form action="index.php" method="GET" class="d-flex">
                                <input type="hidden" name="module" value="rekam_medis">
                                <input type="hidden" name="action" value="dataKunjungan">
                                <input type="text" name="search" class="form-control me-2" placeholder="Cari No. RM, Nama Pasien, atau No. Rawat" value="<?= htmlspecialchars($search ?? ''); ?>">
                                <button type="submit" class="btn btn-primary">Cari</button>
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>No. Rawat</th>
                                    <th>No. RM</th>
                                    <th>Nama Pasien</th>
                                    <th>Keluhan Utama</th>
                                    <th>Diagnosis</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($kunjungan)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data kunjungan</td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $no = ($page - 1) * $limit + 1;
                                    foreach ($kunjungan as $k): 
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= date('d-m-Y', strtotime($k['tanggal'])); ?></td>
                                        <td><?= htmlspecialchars($k['no_rawat']); ?></td>
                                        <td><?= htmlspecialchars($k['no_rkm_medis']); ?></td>
                                        <td><?= htmlspecialchars($k['nm_pasien']); ?></td>
                                        <td><?= htmlspecialchars(substr($k['keluhan_utama'], 0, 50) . (strlen($k['keluhan_utama']) > 50 ? '...' : '')); ?></td>
                                        <td><?= htmlspecialchars(substr($k['diagnosis'], 0, 50) . (strlen($k['diagnosis']) > 50 ? '...' : '')); ?></td>
                                        <td>
                                            <a href="index.php?module=rekam_medis&action=detailPemeriksaan&no_rawat=<?= urlencode($k['no_rawat']); ?>" class="btn btn-sm btn-detail">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?module=rekam_medis&action=dataKunjungan&page=<?= ($page - 1); ?>&search=<?= urlencode($search); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);

                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="index.php?module=rekam_medis&action=dataKunjungan&page=1&search=' . urlencode($search) . '">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                            }

                            for ($i = $start_page; $i <= $end_page; $i++) {
                                echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="index.php?module=rekam_medis&action=dataKunjungan&page=' . $i . '&search=' . urlencode($search) . '">' . $i . '</a></li>';
                            }

                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="index.php?module=rekam_medis&action=dataKunjungan&page=' . $total_pages . '&search=' . urlencode($search) . '">' . $total_pages . '</a></li>';
                            }
                            ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?module=rekam_medis&action=dataKunjungan&page=<?= ($page + 1); ?>&search=<?= urlencode($search); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'template/footer.php'; ?>

    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>
