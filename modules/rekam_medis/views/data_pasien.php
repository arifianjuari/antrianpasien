<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0 text-primary"><i class="fas fa-users me-2"></i>Data Pasien</h3>
                        <div class="d-flex">
                            <form action="index.php?module=rekam_medis&action=cari_pasien" method="POST" class="d-flex me-2">
                                <div class="input-group">
                                    <input type="text" name="keyword" class="form-control border-primary" placeholder="Cari pasien..." value="<?= isset($_POST['keyword']) ? htmlspecialchars($_POST['keyword']) : '' ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                            <a href="index.php?module=rekam_medis&action=tambah_pasien" class="btn btn-success">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <div><?= $_SESSION['success'] ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div><?= $_SESSION['error'] ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tablePasien">
                            <thead class="table-light text-primary">
                                <tr>
                                    <th class="text-center" width="5%">No</th>
                                    <th width="10%">No. RM</th>
                                    <th width="20%">Nama Pasien</th>
                                    <th width="8%">Usia</th>
                                    <th width="15%">Kecamatan</th>
                                    <th width="15%">Pekerjaan</th>
                                    <th width="12%">Nomor Telepon</th>
                                    <th class="text-center" width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($pasien) > 0): ?>
                                    <?php foreach ($pasien as $index => $p): ?>
                                        <?php
                                        // Mendapatkan nama kecamatan
                                        $nama_kecamatan = '-';
                                        foreach ($kecamatan as $kec) {
                                            if ($kec['kd_kec'] == $p['kd_kec']) {
                                                $nama_kecamatan = $kec['nm_kec'];
                                                break;
                                            }
                                        }
                                        ?>
                                        <tr class="border-bottom">
                                            <td class="text-center"><?= $offset + $index + 1 ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['no_rkm_medis']) ?></span></td>
                                            <td class="fw-medium"><?= htmlspecialchars($p['nm_pasien']) ?></td>
                                            <td><span class="badge bg-info text-white"><?= htmlspecialchars($p['umur'] ?? '-') ?> th</span></td>
                                            <td><?= htmlspecialchars($nama_kecamatan) ?></td>
                                            <td><?= htmlspecialchars($p['pekerjaan'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($p['no_tlp'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $p['no_rkm_medis'] ?>&source=data_pasien" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Lihat Rekam Medis">
                                                        <i class="fas fa-file-medical"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm hapus-pasien"
                                                        data-bs-toggle="modal" data-bs-target="#hapusPasienModal"
                                                        data-no-rm="<?= $p['no_rkm_medis'] ?>"
                                                        data-nama="<?= htmlspecialchars($p['nm_pasien']) ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">Tidak ada data pasien</h5>
                                                <p class="text-muted small">Silahkan tambahkan data pasien baru</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="mt-4">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link text-primary" href="index.php?module=rekam_medis&action=data_pasien&page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php 
                                    // Show limited page numbers with ellipsis
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    if ($start_page > 1) {
                                        echo '<li class="page-item"><a class="page-link text-primary" href="index.php?module=rekam_medis&action=data_pasien&page=1' . (!empty($search) ? '&search=' . urlencode($search) : '') . '">1</a></li>';
                                        if ($start_page > 2) {
                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        }
                                    }
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link <?= $i == $page ? '' : 'text-primary' ?>" href="index.php?module=rekam_medis&action=data_pasien&page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; 
                                    
                                    if ($end_page < $total_pages) {
                                        if ($end_page < $total_pages - 1) {
                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        }
                                        echo '<li class="page-item"><a class="page-link text-primary" href="index.php?module=rekam_medis&action=data_pasien&page=' . $total_pages . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $total_pages . '</a></li>';
                                    }
                                    ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link text-primary" href="index.php?module=rekam_medis&action=data_pasien&page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Hapus Pasien -->
<div class="modal fade" id="hapusPasienModal" tabindex="-1" aria-labelledby="hapusPasienModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="hapusPasienModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus Pasien</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-user-times fa-4x text-danger mb-3"></i>
                    <p class="mb-1">Apakah Anda yakin ingin menghapus data pasien:</p>
                    <p class="fw-bold fs-5" id="namaPasienHapus"></p>
                    <p class="text-muted small">Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait pasien ini.</p>
                </div>
                <form id="formHapusPasien" action="index.php?module=rekam_medis&action=hapusPasien" method="POST">
                    <input type="hidden" name="no_rkm_medis" id="noRmHapus">
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
                <button type="submit" form="formHapusPasien" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize DataTable for better table functionality
        if (typeof $.fn.DataTable !== 'undefined') {
            $('#tablePasien').DataTable({
                "paging": false,
                "ordering": true,
                "info": false,
                "searching": false,
                "responsive": true,
                "language": {
                    "emptyTable": "Tidak ada data pasien"
                }
            });
        }

        // Script untuk modal hapus pasien
        const hapusPasienModal = document.getElementById('hapusPasienModal');
        if (hapusPasienModal) {
            hapusPasienModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const noRm = button.getAttribute('data-no-rm');
                const nama = button.getAttribute('data-nama');

                document.getElementById('noRmHapus').value = noRm;
                document.getElementById('namaPasienHapus').textContent = `${nama} (${noRm})`;
            });

            // Tambahkan event listener untuk form submit
            const formHapusPasien = document.getElementById('formHapusPasien');
            formHapusPasien.addEventListener('submit', function(e) {
                e.preventDefault();

                // Submit form
                this.submit();
            });
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);

        // Add hover effect to table rows
        const tableRows = document.querySelectorAll('#tablePasien tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseover', function() {
                this.classList.add('bg-light');
            });
            row.addEventListener('mouseout', function() {
                this.classList.remove('bg-light');
            });
        });
    });
</script>