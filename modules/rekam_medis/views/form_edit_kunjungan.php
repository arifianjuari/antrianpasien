<?php
// Pastikan tidak ada output sebelum header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah ada data kunjungan
if (!isset($kunjungan) || !$kunjungan) {
    $_SESSION['error'] = 'Data kunjungan tidak ditemukan';
    header('Location: index.php?module=rekam_medis');
    exit;
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Edit Data Kunjungan</h6>
            <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $kunjungan['no_rkm_medis'] ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php unset($_SESSION['error']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php unset($_SESSION['success']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['warning'])): ?>
                <div class="alert alert-warning alert-dismissible fade show">
                    <?= $_SESSION['warning'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?php unset($_SESSION['warning']) ?>
                </div>
            <?php endif; ?>

            <!-- Informasi Pasien -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="150">No. Rawat</th>
                            <td><?= $kunjungan['no_rawat'] ?></td>
                        </tr>
                        <tr>
                            <th>No. Rekam Medis</th>
                            <td><?= $kunjungan['no_rkm_medis'] ?></td>
                        </tr>
                        <tr>
                            <th>Nama Pasien</th>
                            <td><?= $kunjungan['nm_pasien'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <form action="index.php?module=rekam_medis&action=update_kunjungan" method="post">
                <input type="hidden" name="no_rawat" value="<?= $kunjungan['no_rawat'] ?>">
                <input type="hidden" name="no_rkm_medis" value="<?= $kunjungan['no_rkm_medis'] ?>">

                <!-- Tanggal dan Waktu -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Registrasi</label>
                            <input type="date" name="tgl_registrasi" class="form-control" value="<?= $kunjungan['tgl_registrasi'] ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Jam Registrasi</label>
                            <input type="time" name="jam_reg" class="form-control" value="<?= $kunjungan['jam_reg'] ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Status Bayar -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status Bayar</label>
                            <select name="status_bayar" class="form-control" required>
                                <option value="Sudah Bayar" <?= $kunjungan['status_bayar'] == 'Sudah Bayar' ? 'selected' : '' ?>>Sudah Bayar</option>
                                <option value="Belum Bayar" <?= $kunjungan['status_bayar'] == 'Belum Bayar' ? 'selected' : '' ?>>Belum Bayar</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $kunjungan['no_rkm_medis'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>