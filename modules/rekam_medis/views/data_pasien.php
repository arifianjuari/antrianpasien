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
                                            <button type="button" class="btn btn-warning btn-sm edit-pasien"
                                                data-bs-toggle="modal" data-bs-target="#editPasienModal"
                                                data-pasien='<?= htmlspecialchars(json_encode($p)) ?>'>
                                                <i class="fas fa-edit"></i>
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

<!-- Modal Edit Pasien -->
<div class="modal fade" id="editPasienModal" tabindex="-1" aria-labelledby="editPasienModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPasienModalLabel">Edit Data Pasien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPasienForm" action="index.php?module=rekam_medis&action=update_pasien&redirect=false" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="no_rkm_medis" id="edit_no_rkm_medis">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_nama_pasien" class="form-label">Nama Pasien</label>
                            <input type="text" class="form-control" id="edit_nama_pasien" name="nama_pasien" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="edit_jenis_kelamin" name="jenis_kelamin" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_tgl_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="edit_tgl_lahir" name="tgl_lahir" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_no_tlp" class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" id="edit_no_tlp" name="no_tlp">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="edit_alamat" name="alamat" rows="2"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="edit_kd_kab" class="form-label">Kabupaten/Kota</label>
                            <select class="form-select" id="edit_kd_kab" name="kd_kab">
                                <?php foreach ($kabupaten as $kab): ?>
                                    <option value="<?= $kab['kd_kab'] ?>"><?= $kab['nm_kab'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_kd_kec" class="form-label">Kecamatan</label>
                            <select class="form-select" id="edit_kd_kec" name="kd_kec">
                                <?php foreach ($kecamatan as $kec): ?>
                                    <option value="<?= $kec['kd_kec'] ?>"><?= $kec['nm_kec'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_kd_kel" class="form-label">Kelurahan/Desa</label>
                            <select class="form-select" id="edit_kd_kel" name="kd_kel">
                                <?php foreach ($kelurahan as $kel): ?>
                                    <option value="<?= $kel['kd_kel'] ?>"><?= $kel['nm_kel'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_pekerjaan" class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" id="edit_pekerjaan" name="pekerjaan">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_kd_pj" class="form-label">Cara Bayar</label>
                            <select class="form-select" id="edit_kd_pj" name="kd_pj">
                                <?php foreach ($cara_bayar as $cb): ?>
                                    <option value="<?= $cb['kd_pj'] ?>"><?= $cb['nm_pj'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_nm_ibu" class="form-label">Nama Ibu</label>
                            <input type="text" class="form-control" id="edit_nm_ibu" name="nm_ibu">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_namakeluarga" class="form-label">Nama Keluarga</label>
                            <input type="text" class="form-control" id="edit_namakeluarga" name="namakeluarga">
                        </div>
                    </div>

                    <input type="hidden" id="edit_tgl_daftar" name="tgl_daftar">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi event listener untuk tombol edit
        const editButtons = document.querySelectorAll('.edit-pasien');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const pasienData = JSON.parse(this.getAttribute('data-pasien'));

                // Isi form dengan data pasien
                document.getElementById('edit_no_rkm_medis').value = pasienData.no_rkm_medis;
                document.getElementById('edit_nama_pasien').value = pasienData.nm_pasien;
                document.getElementById('edit_jenis_kelamin').value = pasienData.jk;
                document.getElementById('edit_tgl_lahir').value = pasienData.tgl_lahir;
                document.getElementById('edit_alamat').value = pasienData.alamat;
                document.getElementById('edit_no_tlp').value = pasienData.no_tlp;
                document.getElementById('edit_pekerjaan').value = pasienData.pekerjaan;
                document.getElementById('edit_kd_kec').value = pasienData.kd_kec;
                document.getElementById('edit_kd_kel').value = pasienData.kd_kel;
                document.getElementById('edit_kd_kab').value = pasienData.kd_kab;
                document.getElementById('edit_nm_ibu').value = pasienData.nm_ibu;
                document.getElementById('edit_namakeluarga').value = pasienData.namakeluarga;
                document.getElementById('edit_kd_pj').value = pasienData.kd_pj;
                document.getElementById('edit_tgl_daftar').value = pasienData.tgl_daftar;
            });
        });

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>