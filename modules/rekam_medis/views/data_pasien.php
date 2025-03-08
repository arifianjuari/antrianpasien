<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="card-title">Data Pasien</h3>
        <div class="d-flex">
            <form action="index.php?module=rekam_medis&action=cari_pasien" method="POST" class="d-flex me-2">
                <div class="input-group">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari pasien..." value="<?= isset($_POST['keyword']) ? htmlspecialchars($_POST['keyword']) : '' ?>">
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

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>No. RM</th>
                            <th>Nama Pasien</th>
                            <th>Usia</th>
                            <th>Kecamatan</th>
                            <th>Pekerjaan</th>
                            <th>Nomor Telepon</th>
                            <th>Aksi</th>
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
                                <tr>
                                    <td><?= $offset + $index + 1 ?></td>
                                    <td><?= htmlspecialchars($p['no_rkm_medis']) ?></td>
                                    <td><?= htmlspecialchars($p['nm_pasien']) ?></td>
                                    <td><?= htmlspecialchars($p['umur'] ?? '-') ?> th</td>
                                    <td><?= htmlspecialchars($nama_kecamatan) ?></td>
                                    <td><?= htmlspecialchars($p['pekerjaan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($p['no_tlp'] ?? '-') ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $p['no_rkm_medis'] ?>" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Lihat Rekam Medis">
                                                <i class="fas fa-file-medical"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm hapus-pasien"
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
                                <td colspan="8" class="text-center">Tidak ada data pasien</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-3">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="index.php?module=rekam_medis&action=data_pasien&page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    &laquo;
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?module=rekam_medis&action=data_pasien&page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="index.php?module=rekam_medis&action=data_pasien&page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    &raquo;
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Hapus Pasien -->
<div class="modal fade" id="hapusPasienModal" tabindex="-1" aria-labelledby="hapusPasienModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hapusPasienModalLabel">Konfirmasi Hapus Pasien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data pasien:</p>
                <p class="fw-bold" id="namaPasienHapus"></p>
                <form id="formHapusPasien" action="index.php?module=rekam_medis&action=hapusPasien" method="POST">
                    <input type="hidden" name="no_rkm_medis" id="noRmHapus">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formHapusPasien" class="btn btn-danger">Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

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

                // Debug info
                console.log('Form disubmit');
                console.log('Action:', this.action);
                console.log('Method:', this.method);
                console.log('No RM:', this.querySelector('[name="no_rkm_medis"]').value);

                // Submit form
                this.submit();
            });
        }
    });
</script>