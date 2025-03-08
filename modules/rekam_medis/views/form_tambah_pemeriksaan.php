<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tambah Kunjungan Baru</h6>
            <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <strong>Error:</strong> <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['warning'])): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Peringatan:</strong> <?= $_SESSION['warning'] ?>
                    <?php unset($_SESSION['warning']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <strong>Sukses:</strong> <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']) ?>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="150">No. Rekam Medis</th>
                            <td><?= $pasien['no_rkm_medis'] ?></td>
                        </tr>
                        <tr>
                            <th>Nama Pasien</th>
                            <td><?= $pasien['nm_pasien'] ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Registrasi</th>
                            <td><?= date('d-m-Y') ?></td>
                        </tr>
                        <tr>
                            <th>Jam Registrasi</th>
                            <td><?= date('H:i:s') ?></td>
                        </tr>
                        <tr>
                            <th>No. Rawat</th>
                            <td><?= $no_rawat ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <form action="index.php?module=rekam_medis&action=simpan_pemeriksaan" method="post" id="formTambahKunjungan">
                <input type="hidden" name="no_rkm_medis" value="<?= $pasien['no_rkm_medis'] ?>">
                <input type="hidden" name="no_rawat" value="<?= $no_rawat ?>">
                <input type="hidden" name="tgl_registrasi" value="<?= date('Y-m-d') ?>">
                <input type="hidden" name="jam_reg" value="<?= date('H:i:s') ?>">

                <div class="form-group row">
                    <label for="status_bayar" class="col-sm-2 col-form-label">Status Bayar</label>
                    <div class="col-sm-4">
                        <select name="status_bayar" id="status_bayar" class="form-control" required>
                            <option value="">-- Pilih Status Bayar --</option>
                            <option value="Belum Bayar" selected>Belum Bayar</option>
                            <option value="Sudah Bayar">Sudah Bayar</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary" id="btnSimpan">
                        <i class="fas fa-save"></i> Simpan Kunjungan
                    </button>
                    <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('formTambahKunjungan').addEventListener('submit', function(e) {
        // Disable tombol submit untuk mencegah klik ganda
        document.getElementById('btnSimpan').disabled = true;
        document.getElementById('btnSimpan').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });
</script>