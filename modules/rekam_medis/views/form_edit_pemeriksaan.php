<?php
// Pastikan tidak ada output sebelum header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah ada data pemeriksaan
if (!isset($pemeriksaan) || !$pemeriksaan) {
    $_SESSION['error'] = 'Data pemeriksaan tidak ditemukan';
    header('Location: index.php?module=rekam_medis');
    exit;
}
?>

<style>
    /* CSS untuk mengatur ukuran font */
    .form-control,
    .form-select {
        font-size: 0.875rem;
    }

    .card-title {
        font-size: 1rem;
    }

    label {
        font-size: 0.875rem;
    }

    .table {
        font-size: 0.875rem;
    }

    /* CSS untuk fitur template */
    .card .small {
        font-size: 0.6rem !important;
    }

    .modal-title {
        font-size: 0.8rem;
    }

    .modal .table {
        font-size: 0.7rem;
    }

    .modal label {
        font-size: 0.7rem;
    }

    .btn-sm {
        font-size: 0.6rem;
    }

    /* CSS untuk warna teks tombol info */
    .btn-info {
        color: #fff !important;
    }

    .btn-info:hover {
        color: #fff !important;
    }
</style>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Edit Pemeriksaan Kandungan</h6>
            <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']) ?>
                </div>
            <?php endif; ?>

            <form action="index.php?module=rekam_medis&action=update_pemeriksaan" method="post">
                <input type="hidden" name="no_rawat" value="<?= $pemeriksaan['no_rawat'] ?>">
                <input type="hidden" name="no_rkm_medis" value="<?= $pasien['no_rkm_medis'] ?>">

                <div class="row">
                    <!-- Kolom 1 -->
                    <div class="col-md-4">
                        <!-- Data Pasien -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Data Pasien</h6>
                            </div>
                            <div class="card-body">
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
                                        <th>Tanggal Lahir</th>
                                        <td><?= date('d-m-Y', strtotime($pasien['tgl_lahir'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Pemeriksaan</th>
                                        <td><?= date('d-m-Y H:i', strtotime($pemeriksaan['tanggal'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>No. Rawat</th>
                                        <td><?= $pemeriksaan['no_rawat'] ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Anamnesis -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Anamnesis</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <label>Keluhan Utama</label>
                                            <textarea name="keluhan_utama" class="form-control form-control-sm" rows="2"><?= isset($pemeriksaan['keluhan_utama']) ? $pemeriksaan['keluhan_utama'] : '' ?></textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label>Riwayat Penyakit Sekarang</label>
                                            <textarea name="rps" class="form-control form-control-sm" rows="2"><?= isset($pemeriksaan['rps']) ? $pemeriksaan['rps'] : '' ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <label>Riwayat Penyakit Dahulu</label>
                                            <textarea name="rpd" class="form-control form-control-sm" rows="2"><?= isset($pemeriksaan['rpd']) ? $pemeriksaan['rpd'] : '' ?></textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label>Alergi</label>
                                            <input type="text" name="alergi" class="form-control form-control-sm" value="<?= isset($pemeriksaan['alergi']) ? $pemeriksaan['alergi'] : '' ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pemeriksaan Fisik -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Pemeriksaan Fisik</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-4">
                                        <div class="mb-2">
                                            <label>GCS</label>
                                            <input type="text" name="gcs" class="form-control form-control-sm" value="<?= isset($pemeriksaan['gcs']) ? $pemeriksaan['gcs'] : '456' ?>">
                                        </div>
                                        <div class="mb-2">
                                            <label>TD (mmHg)</label>
                                            <input type="text" name="td" class="form-control form-control-sm" value="<?= isset($pemeriksaan['td']) ? $pemeriksaan['td'] : '120/80' ?>">
                                        </div>
                                        <div class="mb-2">
                                            <label>Nadi (x/menit)</label>
                                            <input type="text" name="nadi" class="form-control form-control-sm" value="<?= isset($pemeriksaan['nadi']) ? $pemeriksaan['nadi'] : '90' ?>">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-2">
                                            <label>RR (x/menit)</label>
                                            <input type="text" name="rr" class="form-control form-control-sm" value="<?= isset($pemeriksaan['rr']) ? $pemeriksaan['rr'] : '16' ?>">
                                        </div>
                                        <div class="mb-2">
                                            <label>Suhu (°C)</label>
                                            <input type="text" name="suhu" class="form-control form-control-sm" value="<?= isset($pemeriksaan['suhu']) ? $pemeriksaan['suhu'] : '36.4' ?>">
                                        </div>
                                        <div class="mb-2">
                                            <label>SpO2 (%)</label>
                                            <input type="text" name="spo" class="form-control form-control-sm" value="<?= isset($pemeriksaan['spo']) ? $pemeriksaan['spo'] : '99' ?>">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-2">
                                            <label>BB (kg)</label>
                                            <input type="number" name="bb" class="form-control form-control-sm" value="<?= isset($pemeriksaan['bb']) ? $pemeriksaan['bb'] : '' ?>" step="0.01" min="0" max="500" placeholder="Gunakan titik untuk desimal" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                                            <small class="text-muted">Gunakan titik (.) untuk desimal, bukan koma</small>
                                        </div>
                                        <div class="mb-2">
                                            <label>TB (cm)</label>
                                            <input type="number" name="tb" class="form-control form-control-sm" value="<?= isset($pemeriksaan['tb']) ? $pemeriksaan['tb'] : '' ?>" step="0.1" min="0" max="300" placeholder="Gunakan titik untuk desimal" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                                            <small class="text-muted">Gunakan titik (.) untuk desimal, bukan koma</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom 2 -->
                    <div class="col-md-4">
                        <!-- Pemeriksaan Organ -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Pemeriksaan Organ</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-4">
                                        <div class="mb-2">
                                            <label>Kepala</label>
                                            <select name="kepala" class="form-select form-select-sm">
                                                <option value="Normal" <?= (isset($pemeriksaan['kepala']) && $pemeriksaan['kepala'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['kepala']) && $pemeriksaan['kepala'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['kepala']) && $pemeriksaan['kepala'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Mata</label>
                                            <select name="mata" class="form-select form-select-sm">
                                                <option value="Normal" <?= (isset($pemeriksaan['mata']) && $pemeriksaan['mata'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['mata']) && $pemeriksaan['mata'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['mata']) && $pemeriksaan['mata'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Gigi</label>
                                            <select name="gigi" class="form-select form-select-sm">
                                                <option value="Normal" <?= (isset($pemeriksaan['gigi']) && $pemeriksaan['gigi'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['gigi']) && $pemeriksaan['gigi'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['gigi']) && $pemeriksaan['gigi'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-2">
                                            <label>THT</label>
                                            <select name="tht" class="form-select form-select-sm">
                                                <option value="Normal" <?= (isset($pemeriksaan['tht']) && $pemeriksaan['tht'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['tht']) && $pemeriksaan['tht'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['tht']) && $pemeriksaan['tht'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Thoraks</label>
                                            <select name="thoraks" class="form-select form-select-sm">
                                                <option value="Normal" <?= (isset($pemeriksaan['thoraks']) && $pemeriksaan['thoraks'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['thoraks']) && $pemeriksaan['thoraks'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['thoraks']) && $pemeriksaan['thoraks'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Abdomen</label>
                                            <select name="abdomen" class="form-select form-select-sm">
                                                <option value="Normal" <?= (isset($pemeriksaan['abdomen']) && $pemeriksaan['abdomen'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['abdomen']) && $pemeriksaan['abdomen'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['abdomen']) && $pemeriksaan['abdomen'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-2">
                                            <label>Genital</label>
                                            <select name="genital" class="form-select form-select-sm">
                                                <option value="Normal" <?= (isset($pemeriksaan['genital']) && $pemeriksaan['genital'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['genital']) && $pemeriksaan['genital'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['genital']) && $pemeriksaan['genital'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Ekstremitas</label>
                                            <select name="ekstremitas" class="form-select form-select-sm">
                                                <option value="Normal" <?= (isset($pemeriksaan['ekstremitas']) && $pemeriksaan['ekstremitas'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['ekstremitas']) && $pemeriksaan['ekstremitas'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['ekstremitas']) && $pemeriksaan['ekstremitas'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Kulit</label>
                                            <select name="kulit" class="form-select form-select-sm">
                                                <option value="Normal" <?= (isset($pemeriksaan['kulit']) && $pemeriksaan['kulit'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['kulit']) && $pemeriksaan['kulit'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['kulit']) && $pemeriksaan['kulit'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label>Keterangan Pemeriksaan Fisik</label>
                                    <textarea name="ket_fisik" class="form-control form-control-sm" rows="1"><?= isset($pemeriksaan['ket_fisik']) ? $pemeriksaan['ket_fisik'] : '' ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Pemeriksaan Penunjang -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Pemeriksaan Penunjang</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label>Ultrasonografi</label>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <textarea name="ultra" id="ultrasonografi" class="form-control" rows="10"><?= isset($pemeriksaan['ultra']) ? $pemeriksaan['ultra'] : '' ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border mb-2">

                                                <div class="card-body p-2">
                                                    <button type="button" class="btn btn-sm btn-info w-100" data-bs-toggle="modal" data-bs-target="#modalDaftarTemplateUsg">
                                                        <i class="fas fa-list"></i> Lihat Template USG
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card border">

                                                <div class="card-body p-2">
                                                    <a href="javascript:void(0)" onclick="printUsg()" class="btn btn-sm btn-success w-100">
                                                        <i class="fas fa-print"></i> Cetak Hasil USG
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Laboratorium</label>
                                    <textarea name="lab" class="form-control" rows="2"><?= isset($pemeriksaan['lab']) ? $pemeriksaan['lab'] : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom 3 -->
                    <div class="col-md-4">
                        <!-- Diagnosis & Tatalaksana -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Diagnosis & Tatalaksana</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label>Diagnosis</label>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <textarea name="diagnosis" id="diagnosis" class="form-control" rows="3"><?= isset($pemeriksaan['diagnosis']) ? $pemeriksaan['diagnosis'] : '' ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border">

                                                <div class="card-body p-2">
                                                    <button type="button" class="btn btn-sm btn-info w-100" data-bs-toggle="modal" data-bs-target="#modalRiwayatDiagnosis">
                                                        <i class="fas fa-history"></i> Lihat Riwayat
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Tatalaksana</label>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <textarea name="tata" id="tatalaksana" class="form-control" rows="3"><?= isset($pemeriksaan['tata']) ? $pemeriksaan['tata'] : '' ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border">

                                                <div class="card-body p-2">
                                                    <button type="button" class="btn btn-sm btn-info w-100" data-bs-toggle="modal" data-bs-target="#modalDaftarTemplate">
                                                        <i class="fas fa-list"></i> Lihat Template
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Edukasi</label>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <textarea name="edukasi" id="edukasi" class="form-control" rows="3"><?= isset($pemeriksaan['edukasi']) ? $pemeriksaan['edukasi'] : '' ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border">

                                                <div class="card-body p-2">
                                                    <button type="button" class="btn btn-sm btn-info w-100" data-bs-toggle="modal" data-bs-target="#modalDaftarEdukasi">
                                                        <i class="fas fa-list"></i> Lihat Template
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Resume</label>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <textarea name="resume" id="resume" class="form-control" rows="12"><?= isset($pemeriksaan['resume']) ? $pemeriksaan['resume'] : '' ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border">
                                                <div class="card-body p-2">
                                                    <button type="button" class="btn btn-sm btn-info w-100 mb-2" onclick="masukkanIdentitasPasien()">
                                                        <i class="fas fa-user-plus"></i> Identitas
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-info w-100 mb-2" onclick="masukkanStatusObstetri()">
                                                        <i class="fas fa-female"></i> Status Obstetri
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-info w-100 mb-2" onclick="masukkanStatusGinekologi()">
                                                        <i class="fas fa-venus"></i> Status Ginekologi
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-info w-100 mb-2" onclick="masukkanPemeriksaanFisik()">
                                                        <i class="fas fa-stethoscope"></i> Periksa Fisik
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-info w-100 mb-2" onclick="masukkanHasilUSG()">
                                                        <i class="fas fa-heartbeat"></i> Hasil USG
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-info w-100 mb-2" onclick="masukkanDiagnosis()">
                                                        <i class="fas fa-tag"></i> Diagnosis
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-info w-100 mb-2" onclick="masukkanTatalaksana()">
                                                        <i class="fas fa-file-medical"></i> Tatalaksana
                                                    </button>
                                                    <a href="javascript:void(0)" onclick="printResume()" class="btn btn-sm btn-success w-100">
                                                        <i class="fas fa-print"></i> Cetak Resume
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Resep</label>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <textarea name="resep" id="resep" class="form-control" rows="6"><?= isset($pemeriksaan['resep']) ? $pemeriksaan['resep'] : '' ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border mb-2">
                                                <div class="card-body p-2">
                                                    <button type="button" class="btn btn-sm btn-info w-100" data-bs-toggle="modal" data-bs-target="#modalDaftarTemplateResep">
                                                        <i class="fas fa-list"></i> Lihat Daftar
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card border">
                                                <div class="card-body p-2">
                                                    <a href="javascript:void(0)" onclick="printResep()" class="btn btn-sm btn-success w-100">
                                                        <i class="fas fa-print"></i> Cetak Hasil Resep
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Tanggal Kontrol</label>
                                    <input type="date" name="tanggal_kontrol" class="form-control" value="<?= isset($pemeriksaan['tanggal_kontrol']) ? $pemeriksaan['tanggal_kontrol'] : '' ?>">
                                </div>

                                <div class="mb-3">
                                    <label>Atensi</label>
                                    <select name="atensi" class="form-select">
                                        <option value="0" <?= (isset($pemeriksaan['atensi']) && $pemeriksaan['atensi'] == '0') ? 'selected' : '' ?>>Tidak</option>
                                        <option value="1" <?= (isset($pemeriksaan['atensi']) && $pemeriksaan['atensi'] == '1') ? 'selected' : '' ?>>Ya</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Daftar Template Tatalaksana -->
<div class="modal fade" id="modalDaftarTemplate" tabindex="-1" aria-labelledby="modalDaftarTemplateLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDaftarTemplateLabel">Daftar Template Tatalaksana</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filter Kategori -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="filter_kategori_tatalaksana" class="form-select me-2">
                            <option value="">Semua Kategori</option>
                            <option value="fetomaternal">Fetomaternal</option>
                            <option value="ginekologi umum">Ginekologi Umum</option>
                            <option value="onkogin">Onkogin</option>
                            <option value="fertilitas">Fertilitas</option>
                            <option value="uroginekologi">Uroginekologi</option>
                        </select>
                    </div>
                </div>

                <!-- Tabel Template -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tabelTemplateTatalaksana">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Nama Template</th>
                                <th width="40%">Isi Template</th>
                                <th width="15%">Kategori</th>
                                <th width="10%">Tags</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Koneksi ke database
                            global $conn;

                            // Query untuk mendapatkan semua template
                            $sql = "SELECT * FROM template_tatalaksana WHERE status = 'active' ORDER BY kategori_tx ASC, nama_template_tx ASC";
                            $stmt = $conn->query($sql);

                            if ($stmt->rowCount() > 0) {
                                $no = 1;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr class='template-row' data-kategori='" . htmlspecialchars($row['kategori_tx']) . "'>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_template_tx']) . "</td>";
                                    echo "<td><div style='max-height: 100px; overflow-y: auto;'>" . nl2br(htmlspecialchars($row['isi_template_tx'])) . "</div></td>";
                                    echo "<td>" . ucwords($row['kategori_tx']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tags'] ?? '-') . "</td>";
                                    echo "<td><button type='button' class='btn btn-sm btn-primary w-100' onclick='gunakanTemplate(" . json_encode($row['isi_template_tx']) . ")'><i class='fas fa-check'></i> Gunakan</button></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>Tidak ada template tersedia</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Daftar Template USG -->
<div class="modal fade" id="modalDaftarTemplateUsg" tabindex="-1" aria-labelledby="modalDaftarTemplateUsgLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDaftarTemplateUsgLabel">Daftar Template USG</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filter Kategori -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="filter_kategori_usg" class="form-select me-2">
                            <option value="">Semua Kategori</option>
                            <option value="obstetri">Obstetri</option>
                            <option value="ginekologi">Ginekologi</option>
                        </select>
                    </div>
                </div>

                <!-- Tabel Template -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tabelTemplateUsg">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Nama Template</th>
                                <th width="40%">Isi Template</th>
                                <th width="15%">Kategori</th>
                                <th width="10%">Tags</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Koneksi ke database
                            global $conn;

                            // Query untuk mendapatkan semua template
                            $sql = "SELECT * FROM template_usg WHERE status = 'active' ORDER BY kategori_usg ASC, nama_template_usg ASC";
                            $stmt = $conn->query($sql);

                            if ($stmt->rowCount() > 0) {
                                $no = 1;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr class='template-row' data-kategori='" . htmlspecialchars($row['kategori_usg']) . "'>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_template_usg']) . "</td>";
                                    echo "<td><div style='max-height: 100px; overflow-y: auto;'>" . nl2br(htmlspecialchars($row['isi_template_usg'])) . "</div></td>";
                                    echo "<td>" . ucwords($row['kategori_usg']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tags'] ?? '-') . "</td>";
                                    echo "<td><button type='button' class='btn btn-sm btn-success w-100' onclick='gunakanTemplateUsg(" . json_encode($row['isi_template_usg']) . ")'><i class='fas fa-check'></i> Gunakan</button></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>Tidak ada template tersedia</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Riwayat Diagnosis -->
<div class="modal fade" id="modalRiwayatDiagnosis" tabindex="-1" aria-labelledby="modalRiwayatDiagnosisLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRiwayatDiagnosisLabel">Riwayat Diagnosis Pasien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Tanggal</th>
                                <th width="65%">Diagnosis</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Ambil no_rkm_medis dari data pasien
                            $no_rkm_medis = $pasien['no_rkm_medis'];

                            // Koneksi ke database
                            global $conn;

                            // Query untuk mendapatkan riwayat diagnosis
                            $sql = "SELECT 
                                    pmrk.tanggal, 
                                    pmrk.diagnosis 
                                FROM penilaian_medis_ralan_kandungan pmrk
                                JOIN reg_periksa rp ON pmrk.no_rawat = rp.no_rawat
                                WHERE rp.no_rkm_medis = :no_rkm_medis 
                                AND pmrk.diagnosis IS NOT NULL 
                                AND pmrk.diagnosis != ''
                                ORDER BY pmrk.tanggal DESC";

                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':no_rkm_medis', $no_rkm_medis, PDO::PARAM_STR);
                            $stmt->execute();

                            if ($stmt->rowCount() > 0) {
                                $no = 1;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td>" . date('d-m-Y', strtotime($row['tanggal'])) . "</td>";
                                    echo "<td><div style='max-height: 100px; overflow-y: auto;'>" . nl2br(htmlspecialchars($row['diagnosis'])) . "</div></td>";
                                    echo "<td>
                                            <button type='button' class='btn btn-sm btn-primary w-100' onclick='gunakanDiagnosis(" . json_encode($row['diagnosis']) . ")'>
                                                <i class='fas fa-copy'></i> Gunakan
                                            </button>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>Tidak ada riwayat diagnosis</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Daftar Template Resep -->
<div class="modal fade" id="modalDaftarTemplateResep" tabindex="-1" aria-labelledby="modalDaftarTemplateResepLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDaftarTemplateResepLabel">Daftar Formularium</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filter Kategori -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="filter_kategori_obat" class="form-select me-2">
                            <option value="">Semua Kategori</option>
                            <option value="Analgesik" <?= isset($_GET['kategori_obat']) && $_GET['kategori_obat'] == 'Analgesik' ? 'selected' : '' ?>>Analgesik</option>
                            <option value="Antibiotik" <?= isset($_GET['kategori_obat']) && $_GET['kategori_obat'] == 'Antibiotik' ? 'selected' : '' ?>>Antibiotik</option>
                            <option value="Antiinflamasi" <?= isset($_GET['kategori_obat']) && $_GET['kategori_obat'] == 'Antiinflamasi' ? 'selected' : '' ?>>Antiinflamasi</option>
                            <option value="Antihipertensi" <?= isset($_GET['kategori_obat']) && $_GET['kategori_obat'] == 'Antihipertensi' ? 'selected' : '' ?>>Antihipertensi</option>
                            <option value="Antidiabetes" <?= isset($_GET['kategori_obat']) && $_GET['kategori_obat'] == 'Antidiabetes' ? 'selected' : '' ?>>Antidiabetes</option>
                            <option value="Vitamin dan Suplemen" <?= isset($_GET['kategori_obat']) && $_GET['kategori_obat'] == 'Vitamin dan Suplemen' ? 'selected' : '' ?>>Vitamin dan Suplemen</option>
                            <option value="Hormon" <?= isset($_GET['kategori_obat']) && $_GET['kategori_obat'] == 'Hormon' ? 'selected' : '' ?>>Hormon</option>
                            <option value="Obat Kulit" <?= isset($_GET['kategori_obat']) && $_GET['kategori_obat'] == 'Obat Kulit' ? 'selected' : '' ?>>Obat Kulit</option>
                            <option value="Obat Mata" <?= isset($_GET['kategori_obat']) && $_GET['kategori_obat'] == 'Obat Mata' ? 'selected' : '' ?>>Obat Mata</option>
                            <option value="Obat Saluran Pencernaan" <?= isset($_GET['kategori_obat']) && $_GET['kategori_obat'] == 'Obat Saluran Pencernaan' ? 'selected' : '' ?>>Obat Saluran Pencernaan</option>
                            <option value="Obat Saluran Pernapasan" <?= isset($_GET['kategori_obat']) && $_GET['kategori_obat'] == 'Obat Saluran Pernapasan' ? 'selected' : '' ?>>Obat Saluran Pernapasan</option>
                            <option value="Lainnya" <?= isset($_GET['kategori_obat']) && $_GET['kategori_obat'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="search_generik" class="form-control" placeholder="Cari nama generik...">
                    </div>
                </div>

                <!-- Tabel Formularium -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tabelFormularium">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="checkAll" class="form-check-input">
                                </th>
                                <th width="20%">Nama Obat</th>
                                <th width="15%">Nama Generik</th>
                                <th width="15%">Bentuk & Dosis</th>
                                <th width="15%">Kategori</th>
                                <th width="15%">Catatan</th>
                                <th width="15%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Koneksi ke database
                            global $conn;

                            // Query untuk mendapatkan semua data formularium
                            $sql = "SELECT * FROM formularium WHERE status_aktif = 1 ORDER BY nama_obat ASC";
                            $stmt = $conn->query($sql);

                            if ($stmt->rowCount() > 0) {
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $bentuk_dosis = $row['bentuk_sediaan'] . ' ' . $row['dosis'];
                                    echo "<tr class='obat-row' data-kategori='" . htmlspecialchars($row['kategori']) . "'>";
                                    echo "<td><input type='checkbox' class='form-check-input obat-checkbox' data-nama='" . htmlspecialchars($row['nama_obat']) . "' data-bentuk-dosis='" . htmlspecialchars($bentuk_dosis) . "' data-catatan='" . htmlspecialchars($row['catatan_obat']) . "' data-generik='" . htmlspecialchars($row['nama_generik']) . "'></td>";
                                    echo "<td>" . htmlspecialchars($row['nama_obat']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_generik']) . "</td>";
                                    echo "<td>" . htmlspecialchars($bentuk_dosis) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['kategori']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['catatan_obat']) . "</td>";
                                    echo "<td><span class='badge bg-success'>Aktif</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Tidak ada data obat</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="tambahkanObatTerpilih()">Tambahkan Obat Terpilih</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Daftar Template Edukasi -->
<div class="modal fade" id="modalDaftarEdukasi" tabindex="-1" aria-labelledby="modalDaftarEdukasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDaftarEdukasiLabel">Daftar Template Edukasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filter Kategori dan Pencarian -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="filter_kategori_edukasi" class="form-select me-2">
                            <option value="">Semua Kategori</option>
                            <option value="fetomaternal">Fetomaternal</option>
                            <option value="ginekologi umum">Ginekologi Umum</option>
                            <option value="onkogin">Onkogin</option>
                            <option value="fertilitas">Fertilitas</option>
                            <option value="uroginekologi">Uroginekologi</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="search_edukasi" class="form-control" placeholder="Cari judul atau isi edukasi...">
                    </div>
                </div>

                <!-- Tabel Template -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tabelTemplateEdukasi">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Judul</th>
                                <th width="40%">Isi Edukasi</th>
                                <th width="15%">Kategori</th>
                                <th width="10%">Tags</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Koneksi ke database
                            global $conn;

                            // Query untuk mendapatkan semua template edukasi
                            $sql = "SELECT * FROM edukasi WHERE status_aktif = 1 ORDER BY kategori ASC, judul ASC";
                            $stmt = $conn->query($sql);

                            if ($stmt->rowCount() > 0) {
                                $no = 1;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr class='template-row' data-kategori='" . htmlspecialchars($row['kategori']) . "' data-judul='" . htmlspecialchars($row['judul']) . "'>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['judul']) . "</td>";
                                    echo "<td><div style='max-height: 100px; overflow-y: auto;'>" . $row['isi_edukasi'] . "</div></td>";
                                    echo "<td>" . ucwords($row['kategori']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tag'] ?? '-') . "</td>";
                                    echo "<td><button type='button' class='btn btn-sm btn-primary w-100' onclick='gunakanTemplateEdukasi(" . json_encode($row['isi_edukasi']) . ")'><i class='fas fa-check'></i> Gunakan</button></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>Tidak ada template edukasi tersedia</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Daftar Template Resume -->
<div class="modal fade" id="modalDaftarTemplateResume" tabindex="-1" aria-labelledby="modalDaftarTemplateResumeLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDaftarTemplateResumeLabel">Daftar Template Resume</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filter Kategori dan Pencarian -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="filter_kategori_resume" class="form-select me-2">
                            <option value="">Semua Kategori</option>
                            <option value="fetomaternal">Fetomaternal</option>
                            <option value="ginekologi umum">Ginekologi Umum</option>
                            <option value="onkogin">Onkogin</option>
                            <option value="fertilitas">Fertilitas</option>
                            <option value="uroginekologi">Uroginekologi</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="search_resume" class="form-control" placeholder="Cari resume...">
                    </div>
                </div>

                <!-- Tabel Template -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tabelTemplateResume">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Judul</th>
                                <th width="40%">Isi Resume</th>
                                <th width="15%">Kategori</th>
                                <th width="10%">Tags</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Koneksi ke database
                            global $conn;

                            // Query untuk mendapatkan semua template resume (jika tabel sudah ada)
                            $sql = "SELECT * FROM template_resume WHERE status_aktif = 1 ORDER BY kategori ASC, judul ASC";
                            try {
                                $stmt = $conn->query($sql);

                                if ($stmt && $stmt->rowCount() > 0) {
                                    $no = 1;
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr class='template-row' data-kategori='" . htmlspecialchars($row['kategori']) . "' data-judul='" . htmlspecialchars($row['judul']) . "'>";
                                        echo "<td>" . $no++ . "</td>";
                                        echo "<td>" . htmlspecialchars($row['judul']) . "</td>";
                                        echo "<td><div style='max-height: 100px; overflow-y: auto;'>" . $row['isi_resume'] . "</div></td>";
                                        echo "<td>" . ucwords($row['kategori']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['tag'] ?? '-') . "</td>";
                                        echo "<td><button type='button' class='btn btn-sm btn-primary w-100' onclick='gunakanTemplateResume(" . json_encode($row['isi_resume']) . ")'><i class='fas fa-check'></i> Gunakan</button></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>Tidak ada template resume tersedia</td></tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='6' class='text-center'>Fitur template resume belum tersedia</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function gunakanTemplate(isi) {
        const currentValue = document.getElementById('tatalaksana').value;
        if (currentValue && currentValue.trim() !== '') {
            document.getElementById('tatalaksana').value = currentValue + '\n\n' + isi;
        } else {
            document.getElementById('tatalaksana').value = isi;
        }
        $('#modalDaftarTemplate').modal('hide');
    }

    function printUsg() {
        // Ambil isi dari textarea ultrasonografi
        const isiUsg = document.getElementById('ultrasonografi').value.trim();

        // Validasi isi USG
        if (!isiUsg) {
            alert('Mohon isi data hasil USG terlebih dahulu sebelum mencetak');
            return;
        }

        const noRawat = '<?= $pemeriksaan['no_rawat'] ?>';
        const namaPasien = '<?= $pasien['nm_pasien'] ?>';
        const noRm = '<?= $pasien['no_rkm_medis'] ?>';

        // Redirect ke halaman print dengan parameter
        const url = 'modules/rekam_medis/print_usg.php?isi=' + encodeURIComponent(isiUsg) +
            '&no_rawat=' + encodeURIComponent(noRawat) +
            '&nama=' + encodeURIComponent(namaPasien) +
            '&no_rm=' + encodeURIComponent(noRm);

        // Buka di tab baru
        window.open(url, '_blank');
    }

    function printResume() {
        // Ambil isi dari textarea resume
        const isiResume = document.getElementById('resume').value.trim();

        // Validasi isi resume
        if (!isiResume) {
            alert('Mohon isi data resume terlebih dahulu sebelum mencetak');
            return;
        }

        const noRawat = '<?= $pemeriksaan['no_rawat'] ?>';
        const namaPasien = '<?= $pasien['nm_pasien'] ?>';
        const noRm = '<?= $pasien['no_rkm_medis'] ?>';

        // Redirect ke halaman print dengan parameter
        const url = 'modules/rekam_medis/print_resume.php?isi=' + encodeURIComponent(isiResume) +
            '&no_rawat=' + encodeURIComponent(noRawat) +
            '&nama=' + encodeURIComponent(namaPasien) +
            '&no_rm=' + encodeURIComponent(noRm);

        // Buka di tab baru
        window.open(url, '_blank');
    }

    function gunakanTemplateUsg(isi) {
        const currentValue = document.getElementById('ultrasonografi').value;
        if (currentValue && currentValue.trim() !== '') {
            document.getElementById('ultrasonografi').value = currentValue + '\n\n' + isi;
        } else {
            document.getElementById('ultrasonografi').value = isi;
        }
        $('#modalDaftarTemplateUsg').modal('hide');
    }

    function gunakanDiagnosis(isi) {
        document.getElementById('diagnosis').value = isi;
        $('#modalRiwayatDiagnosis').modal('hide');
    }

    function gunakanTemplateEdukasi(isi) {
        const currentValue = document.getElementById('edukasi').value;

        // Hapus escape karakter yang mungkin ada
        const cleanedIsi = isi.replace(/\\n/g, '\n').replace(/\\"/g, '"').replace(/\\'/g, "'");

        // Konversi HTML ke teks biasa
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = cleanedIsi;
        const textContent = tempDiv.textContent || tempDiv.innerText || '';

        // Bersihkan spasi dan baris kosong berlebihan
        const cleanedContent = textContent
            .replace(/^\s+|\s+$/g, '') // Hapus whitespace di awal dan akhir
            .replace(/\n\s*\n\s*\n/g, '\n\n'); // Ubah 3 atau lebih baris kosong menjadi 2

        if (currentValue && currentValue.trim() !== '') {
            document.getElementById('edukasi').value = currentValue + '\n\n' + cleanedContent;
        } else {
            document.getElementById('edukasi').value = cleanedContent;
        }
        $('#modalDaftarEdukasi').modal('hide');
    }

    function gunakanTemplateResume(isi) {
        const currentValue = document.getElementById('resume').value;

        // Hapus escape karakter yang mungkin ada
        const cleanedIsi = isi.replace(/\\n/g, '\n').replace(/\\"/g, '"').replace(/\\'/g, "'");

        // Konversi HTML ke teks biasa
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = cleanedIsi;
        const textContent = tempDiv.textContent || tempDiv.innerText || '';

        // Bersihkan spasi dan baris kosong berlebihan
        const cleanedContent = textContent
            .replace(/^\s+|\s+$/g, '') // Hapus whitespace di awal dan akhir
            .replace(/\n\s*\n\s*\n/g, '\n\n'); // Ubah 3 atau lebih baris kosong menjadi 2

        if (currentValue && currentValue.trim() !== '') {
            document.getElementById('resume').value = currentValue + '\n\n' + cleanedContent;
        } else {
            document.getElementById('resume').value = cleanedContent;
        }
        $('#modalDaftarTemplateResume').modal('hide');
    }

    // Fungsi untuk menangani checkbox "Pilih Semua"
    document.getElementById('checkAll').addEventListener('change', function() {
        var checkboxes = document.getElementsByClassName('obat-checkbox');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });

    // Fungsi untuk menambahkan obat yang dipilih ke field resep
    function tambahkanObatTerpilih() {
        var checkboxes = document.getElementsByClassName('obat-checkbox');
        var resepField = document.getElementById('resep');
        var obatTerpilih = [];

        for (var checkbox of checkboxes) {
            if (checkbox.checked) {
                var namaObat = checkbox.getAttribute('data-nama');
                var bentukDosis = checkbox.getAttribute('data-bentuk-dosis');
                var catatan = checkbox.getAttribute('data-catatan');
                var split = bentukDosis.split(' ');
                var bentukSediaan = split[0];
                var dosis = split.slice(1).join(' ');

                // Format: [nama_obat] [bentuk_sediaan]     No.
                //          [dosis]
                var textObat = namaObat + ' ' + bentukSediaan + '     No.X';
                textObat += '\n         ' + dosis;

                if (catatan) {
                    textObat += '\n\tCatatan: ' + catatan;
                }

                obatTerpilih.push(textObat);
            }
        }

        if (obatTerpilih.length > 0) {
            var currentValue = resepField.value;
            var newValue = obatTerpilih.join('\n\n');

            if (currentValue && currentValue.trim() !== '') {
                resepField.value = currentValue + '\n\n' + newValue;
            } else {
                resepField.value = newValue;
            }
        }

        $('#modalDaftarTemplateResep').modal('hide');
    }

    function hitungUmur(tanggalLahir) {
        var today = new Date();
        var birthDate = new Date(tanggalLahir);
        var age = today.getFullYear() - birthDate.getFullYear();
        var monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        return age;
    }

    // Fungsi untuk update dan menjaga format data di resume
    function updateResumeFormat() {
        var resumeField = document.getElementById('resume');
        var resumeText = resumeField.value.trim();

        // Jika kosong, tidak perlu diproses
        if (resumeText === '') {
            return;
        }

        // Data yang akan diambil dari resume
        var identitasData = '';
        var statusObstetriData = '';
        var statusGinekologiData = '';
        var pemeriksaanFisikData = '';
        var hasilUSGData = '';
        var diagnosisData = '';
        var tatalaksanaData = '';
        var otherData = '';

        // Ekstrak data identitas (jika ada)
        if (resumeText.includes("Nama:") && resumeText.includes("Tanggal Lahir:") && resumeText.includes("Umur:")) {
            var identitasMatch = resumeText.match(/Nama:.*\nTanggal Lahir:.*\nUmur:.*tahun\n\n/s);
            if (identitasMatch) {
                identitasData = identitasMatch[0];
                resumeText = resumeText.replace(identitasData, '');
            }
        }

        // Ekstrak data status obstetri (jika ada)
        if (resumeText.includes("STATUS OBSTETRI:")) {
            var obstetriMatch = resumeText.match(/STATUS OBSTETRI:\n(?:.*\n)+?\n+/s);
            if (obstetriMatch) {
                statusObstetriData = obstetriMatch[0];
                resumeText = resumeText.replace(statusObstetriData, '');
            }
        }

        // Ekstrak data status ginekologi (jika ada)
        if (resumeText.includes("STATUS GINEKOLOGI:")) {
            var ginekologiMatch = resumeText.match(/STATUS GINEKOLOGI:\n(?:.*\n)+?\n+/s);
            if (ginekologiMatch) {
                statusGinekologiData = ginekologiMatch[0];
                resumeText = resumeText.replace(statusGinekologiData, '');
            }
        }

        // Ekstrak data pemeriksaan fisik (jika ada)
        if (resumeText.includes("PEMERIKSAAN FISIK:")) {
            var fisikMatch = resumeText.match(/PEMERIKSAAN FISIK:\n(?:.*\n)+?\n+/s);
            if (fisikMatch) {
                pemeriksaanFisikData = fisikMatch[0];
                resumeText = resumeText.replace(pemeriksaanFisikData, '');
            }
        }

        // Ekstrak data hasil USG (jika ada)
        if (resumeText.includes("PEMERIKSAAN USG:")) {
            var usgMatch = resumeText.match(/PEMERIKSAAN USG:\n(?:.*\n)+?\n+/s);
            if (usgMatch) {
                hasilUSGData = usgMatch[0];
                resumeText = resumeText.replace(hasilUSGData, '');
            }
        }

        // Ekstrak data diagnosis (jika ada)
        if (resumeText.includes("DIAGNOSIS:")) {
            var diagnosisMatch = resumeText.match(/DIAGNOSIS:\n(?:.*\n)+?\n+/s);
            if (diagnosisMatch) {
                diagnosisData = diagnosisMatch[0];
                resumeText = resumeText.replace(diagnosisData, '');
            }
        }

        // Ekstrak data tatalaksana (jika ada)
        if (resumeText.includes("TATALAKSANA:")) {
            var tatalaksanaMatch = resumeText.match(/TATALAKSANA:\n(?:.*\n)+?\n+/s);
            if (tatalaksanaMatch) {
                tatalaksanaData = tatalaksanaMatch[0];
                resumeText = resumeText.replace(tatalaksanaData, '');
            }
        }

        // Sisa data lainnya
        otherData = resumeText.trim();

        // Susun ulang data sesuai urutan yang diinginkan
        var newResumeText = '';
        if (identitasData) newResumeText += identitasData;
        if (statusObstetriData) newResumeText += statusObstetriData;
        if (statusGinekologiData) newResumeText += statusGinekologiData;
        if (pemeriksaanFisikData) newResumeText += pemeriksaanFisikData;
        if (hasilUSGData) newResumeText += hasilUSGData;
        if (diagnosisData) newResumeText += diagnosisData;
        if (tatalaksanaData) newResumeText += tatalaksanaData;
        if (otherData) newResumeText += otherData + '\n\n';

        // Update field resume dengan tambahan satu baris kosong
        resumeField.value = newResumeText.trim() + '\n';
    }

    function masukkanIdentitasPasien() {
        // Ambil data pasien dari tabel
        var namaPasien = "<?= $pasien['nm_pasien'] ?>";
        var tglLahir = "<?= date('d-m-Y', strtotime($pasien['tgl_lahir'])) ?>";
        var umur = hitungUmur("<?= $pasien['tgl_lahir'] ?>");

        // Format identitas pasien
        var identitasPasien = "Nama: " + namaPasien + "\n";
        identitasPasien += "Tanggal Lahir: " + tglLahir + "\n";
        identitasPasien += "Umur: " + umur + " tahun\n\n";

        // Sisipkan ke field resume
        var resumeField = document.getElementById('resume');
        resumeField.value = identitasPasien + resumeField.value;

        // Update format data
        updateResumeFormat();
    }

    function masukkanStatusObstetri() {
        // Cek jenis kelamin, hanya lanjutkan jika pasien perempuan
        var jenisKelamin = "<?= isset($pasien['jk']) ? $pasien['jk'] : '' ?>";

        if (jenisKelamin !== 'P') {
            alert('Status obstetri hanya berlaku untuk pasien perempuan');
            return;
        }

        <?php
        // Ambil data obstetri dari database menggunakan query yang disarankan
        $no_rawat = $pemeriksaan['no_rawat'];
        $obstetri_data = array(
            'gravida' => '0',
            'paritas' => '0',
            'abortus' => '0',
            'tanggal_hpht' => '-',
            'tanggal_tp' => '-',
            'tanggal_tp_penyesuaian' => '-',
            'tb' => '0',
            'faktor_risiko_umum' => '-',
            'faktor_risiko_obstetri' => '-',
            'faktor_risiko_preeklampsia' => '-',
            'hasil_faktor_risiko' => '-'
        );

        try {
            $sql = "SELECT s.*
                    FROM reg_periksa r 
                    JOIN status_obstetri s ON r.no_rkm_medis = s.no_rkm_medis
                    WHERE r.no_rawat = :no_rawat";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':no_rawat', $no_rawat, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $obstetri_data = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            // Handle error jika terjadi kesalahan pada query
            error_log("Error fetching obstetri data: " . $e->getMessage());
        }
        ?>

        // Ambil data obstetri dari data yang telah diambil dari database
        var gravida = "<?= isset($obstetri_data['gravida']) ? $obstetri_data['gravida'] : '0' ?>";
        var paritas = "<?= isset($obstetri_data['paritas']) ? $obstetri_data['paritas'] : '0' ?>";
        var abortus = "<?= isset($obstetri_data['abortus']) ? $obstetri_data['abortus'] : '0' ?>";
        var tanggalHpht = "<?= isset($obstetri_data['tanggal_hpht']) && $obstetri_data['tanggal_hpht'] != '0000-00-00' ? date('d-m-Y', strtotime($obstetri_data['tanggal_hpht'])) : '-' ?>";
        var tanggalTp = "<?= isset($obstetri_data['tanggal_tp']) && $obstetri_data['tanggal_tp'] != '0000-00-00' ? date('d-m-Y', strtotime($obstetri_data['tanggal_tp'])) : '-' ?>";
        var tanggalTpPenyesuaian = "<?= isset($obstetri_data['tanggal_tp_penyesuaian']) && $obstetri_data['tanggal_tp_penyesuaian'] != '0000-00-00' ? date('d-m-Y', strtotime($obstetri_data['tanggal_tp_penyesuaian'])) : '-' ?>";
        var tb = "<?= isset($obstetri_data['tb']) ? $obstetri_data['tb'] : '0' ?>";
        var faktorRisikoUmum = "<?= isset($obstetri_data['faktor_risiko_umum']) ? $obstetri_data['faktor_risiko_umum'] : '-' ?>";
        var faktorRisikoObstetri = "<?= isset($obstetri_data['faktor_risiko_obstetri']) ? $obstetri_data['faktor_risiko_obstetri'] : '-' ?>";
        var faktorRisikoPreeklampsia = "<?= isset($obstetri_data['faktor_risiko_preeklampsia']) ? $obstetri_data['faktor_risiko_preeklampsia'] : '-' ?>";
        var hasilFaktorRisiko = "<?= isset($obstetri_data['hasil_faktor_risiko']) ? $obstetri_data['hasil_faktor_risiko'] : '-' ?>";

        // Format status obstetri
        var statusObstetriText = "STATUS OBSTETRI:\n";
        statusObstetriText += "G" + gravida + "P" + paritas + "A" + abortus;

        if (tanggalHpht && tanggalHpht !== '-') {
            statusObstetriText += "\nHPHT: " + tanggalHpht;
        }

        if (tanggalTpPenyesuaian && tanggalTpPenyesuaian !== '-') {
            statusObstetriText += "\nTP: " + tanggalTpPenyesuaian;
        }



        // Tambahkan faktor risiko jika ada
        var adaFaktorRisiko = false;
        var faktorRisikoText = "\nFaktor Risiko:";

        if (faktorRisikoUmum && faktorRisikoUmum !== '-') {
            faktorRisikoText += "\n" + faktorRisikoUmum;
            adaFaktorRisiko = true;
        }

        if (faktorRisikoObstetri && faktorRisikoObstetri !== '-') {
            faktorRisikoText += " + " + faktorRisikoObstetri;
            adaFaktorRisiko = true;
        }

        if (faktorRisikoPreeklampsia && faktorRisikoPreeklampsia !== '-') {
            faktorRisikoText += "\nFaktor Risiko PE: " + faktorRisikoPreeklampsia;
            adaFaktorRisiko = true;
        }

        if (adaFaktorRisiko) {
            statusObstetriText += faktorRisikoText;

            if (hasilFaktorRisiko && hasilFaktorRisiko !== '-') {
                statusObstetriText += "\nHasil Analisis Faktor Risiko: " + hasilFaktorRisiko;
            }
        }

        statusObstetriText += "\n\n";

        // Sisipkan ke field resume
        var resumeField = document.getElementById('resume');
        resumeField.value += statusObstetriText;

        // Update format data
        updateResumeFormat();
    }

    function masukkanStatusGinekologi() {
        // Cek jenis kelamin, hanya lanjutkan jika pasien perempuan
        var jenisKelamin = "<?= isset($pasien['jk']) ? $pasien['jk'] : '' ?>";

        if (jenisKelamin !== 'P') {
            alert('Status ginekologi hanya berlaku untuk pasien perempuan');
            return;
        }

        <?php
        // Ambil data ginekologi dari database menggunakan query langsung
        $no_rkm_medis = $pasien['no_rkm_medis']; // Ambil no_rkm_medis dari data pasien

        // Inisialisasi array data default
        $ginekologi_data = array(
            'Parturien' => '0',
            'Abortus' => '0',
            'Hari_pertama_haid_terakhir' => null,
            'Kontrasepsi_terakhir' => 'Tidak Ada',
            'lama_menikah_th' => '0'
        );

        try {
            // Query langsung ke tabel status_ginekologi dengan no_rkm_medis
            // Perhatikan bahwa nama kolom menggunakan kapital di awal (seperti dalam model StatusGinekologi.php)
            $sql = "SELECT * FROM status_ginekologi WHERE no_rkm_medis = :no_rkm_medis ORDER BY created_at DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':no_rkm_medis', $no_rkm_medis, PDO::PARAM_STR);
            $stmt->execute();

            // Debug output
            error_log("Query status_ginekologi untuk no_rkm_medis: " . $no_rkm_medis);

            if ($stmt->rowCount() > 0) {
                $ginekologi_data = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Data ginekologi ditemukan: " . json_encode($ginekologi_data));
            } else {
                error_log("Tidak ada data ginekologi untuk no_rkm_medis: " . $no_rkm_medis);
            }
        } catch (PDOException $e) {
            // Handle error jika terjadi kesalahan pada query
            error_log("Error fetching ginekologi data: " . $e->getMessage());
        }
        ?>

        // Format status ginekologi
        var statusGinekologiText = "STATUS GINEKOLOGI:\n";

        // Ambil data dengan konversi tipe yang benar dan memperhatikan nama kolom
        var parturien = <?= isset($ginekologi_data['Parturien']) ? intval($ginekologi_data['Parturien']) : 0 ?>;
        var abortus = <?= isset($ginekologi_data['Abortus']) ? intval($ginekologi_data['Abortus']) : 0 ?>;
        var hariPertamaHaidTerakhir = "<?= isset($ginekologi_data['Hari_pertama_haid_terakhir']) && $ginekologi_data['Hari_pertama_haid_terakhir'] && $ginekologi_data['Hari_pertama_haid_terakhir'] != '0000-00-00' ? date('d-m-Y', strtotime($ginekologi_data['Hari_pertama_haid_terakhir'])) : '-' ?>";
        var kontrasepsiTerakhir = "<?= isset($ginekologi_data['Kontrasepsi_terakhir']) ? $ginekologi_data['Kontrasepsi_terakhir'] : 'Tidak Ada' ?>";
        var lamaMenikahTh = <?= isset($ginekologi_data['lama_menikah_th']) ? intval($ginekologi_data['lama_menikah_th']) : 0 ?>;

        console.log("Data ginekologi yang diambil:");
        console.log("Parturien:", parturien);
        console.log("Abortus:", abortus);
        console.log("HPHT:", hariPertamaHaidTerakhir);
        console.log("Kontrasepsi:", kontrasepsiTerakhir);
        console.log("Lama Menikah:", lamaMenikahTh);

        // Selalu tampilkan data parturien (jumlah persalinan)
        statusGinekologiText += "P" + parturien;

        // Selalu tampilkan data abortus (jumlah keguguran)
        statusGinekologiText += "Ab" + abortus + "\n";

        // Tampilkan Hari Pertama Haid Terakhir
        statusGinekologiText += "HPHT: " + (hariPertamaHaidTerakhir !== '-' ? hariPertamaHaidTerakhir : "(tidak ada data)") + "\n";

        // Tampilkan Kontrasepsi Terakhir
        statusGinekologiText += "KB Terakhir: " + kontrasepsiTerakhir + "\n";

        // Selalu tampilkan Lama Menikah
        statusGinekologiText += "Lama Menikah: " + lamaMenikahTh + " tahun\n";

        statusGinekologiText += "\n";

        // Sisipkan ke field resume
        var resumeField = document.getElementById('resume');
        resumeField.value += statusGinekologiText;

        // Update format data
        updateResumeFormat();
    }

    function masukkanPemeriksaanFisik() {
        // Hanya ambil data keterangan pemeriksaan fisik
        var ket_fisik = document.getElementsByName('ket_fisik')[0].value;

        if (ket_fisik.trim() === '') {
            alert('Keterangan pemeriksaan fisik kosong. Silakan isi terlebih dahulu.');
            return;
        }

        // Format pemeriksaan fisik
        var pemeriksaanFisik = "PEMERIKSAAN FISIK:\n" + ket_fisik + "\n\n";

        // Sisipkan ke field resume
        var resumeField = document.getElementById('resume');
        resumeField.value += (resumeField.value ? "\n" : "") + pemeriksaanFisik;

        // Update format data
        updateResumeFormat();
    }

    function masukkanHasilUSG() {
        // Ambil isi dari textarea ultrasonografi
        const isiUsg = document.getElementById('ultrasonografi').value.trim();

        // Validasi isi USG
        if (!isiUsg) {
            alert('Mohon isi data hasil USG terlebih dahulu sebelum menambahkan ke resume');
            return;
        }

        // Format pemeriksaan USG
        var pemeriksaanUSG = "PEMERIKSAAN USG:\n" + isiUsg + "\n\n";

        // Sisipkan ke field resume
        var resumeField = document.getElementById('resume');
        resumeField.value += (resumeField.value ? "\n" : "") + pemeriksaanUSG;

        // Update format data
        updateResumeFormat();
    }

    function masukkanDiagnosis() {
        // Ambil isi dari textarea diagnosis
        const isiDiagnosis = document.getElementById('diagnosis').value.trim();

        // Validasi isi diagnosis
        if (!isiDiagnosis) {
            alert('Mohon isi data diagnosis terlebih dahulu sebelum menambahkan ke resume');
            return;
        }

        // Format diagnosis
        var diagnosisText = "DIAGNOSIS:\n" + isiDiagnosis + "\n\n";

        // Sisipkan ke field resume
        var resumeField = document.getElementById('resume');
        resumeField.value += (resumeField.value ? "\n" : "") + diagnosisText;

        // Update format data
        updateResumeFormat();
    }

    function masukkanTatalaksana() {
        // Ambil isi dari textarea tatalaksana
        const isiTatalaksana = document.getElementById('tatalaksana').value.trim();

        // Validasi isi tatalaksana
        if (!isiTatalaksana) {
            alert('Mohon isi data tatalaksana terlebih dahulu sebelum menambahkan ke resume');
            return;
        }

        // Format tatalaksana
        var tatalaksanaText = "TATALAKSANA:\n" + isiTatalaksana + "\n\n";

        // Sisipkan ke field resume
        var resumeField = document.getElementById('resume');
        resumeField.value += (resumeField.value ? "\n" : "") + tatalaksanaText;

        // Update format data
        updateResumeFormat();
    }

    function printResep() {
        // Ambil isi dari textarea resep
        const isiResep = document.getElementById('resep').value.trim();

        // Validasi isi resep
        if (!isiResep) {
            alert('Mohon isi data resep terlebih dahulu sebelum mencetak');
            return;
        }

        const noRawat = '<?= $pemeriksaan['no_rawat'] ?>';
        const namaPasien = '<?= $pasien['nm_pasien'] ?>';
        const noRm = '<?= $pasien['no_rkm_medis'] ?>';

        // Redirect ke halaman print dengan parameter
        const url = 'modules/rekam_medis/print_resep.php?isi=' + encodeURIComponent(isiResep) +
            '&no_rawat=' + encodeURIComponent(noRawat) +
            '&nama=' + encodeURIComponent(namaPasien) +
            '&no_rm=' + encodeURIComponent(noRm);

        // Buka di tab baru
        window.open(url, '_blank');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Filter untuk template USG
        document.getElementById('filter_kategori_usg').addEventListener('change', function() {
            var kategori = this.value;
            var rows = document.querySelectorAll('#tabelTemplateUsg tbody tr.template-row');

            rows.forEach(function(row) {
                if (kategori === '' || row.getAttribute('data-kategori') === kategori) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // Perbarui nomor urut yang ditampilkan
            var visibleRows = document.querySelectorAll('#tabelTemplateUsg tbody tr.template-row:not([style*="display: none"])');
            visibleRows.forEach(function(row, index) {
                row.cells[0].textContent = index + 1;
            });

            // Tampilkan pesan jika tidak ada data
            var tbody = document.querySelector('#tabelTemplateUsg tbody');
            var noDataRow = document.querySelector('#tabelTemplateUsg tbody tr:not(.template-row)');

            if (visibleRows.length === 0) {
                if (!noDataRow) {
                    var tr = document.createElement('tr');
                    tr.className = 'no-data-row';
                    tr.innerHTML = '<td colspan="6" class="text-center">Tidak ada template tersedia untuk kategori ini</td>';
                    tbody.appendChild(tr);
                } else {
                    noDataRow.style.display = '';
                }
            } else {
                if (noDataRow) {
                    noDataRow.style.display = 'none';
                }
            }
        });

        // Filter untuk template tatalaksana
        document.getElementById('filter_kategori_tatalaksana').addEventListener('change', function() {
            var kategori = this.value;
            var rows = document.querySelectorAll('#tabelTemplateTatalaksana tbody tr.template-row');

            rows.forEach(function(row) {
                if (kategori === '' || row.getAttribute('data-kategori') === kategori) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // Perbarui nomor urut yang ditampilkan
            var visibleRows = document.querySelectorAll('#tabelTemplateTatalaksana tbody tr.template-row:not([style*="display: none"])');
            visibleRows.forEach(function(row, index) {
                row.cells[0].textContent = index + 1;
            });

            // Tampilkan pesan jika tidak ada data
            var tbody = document.querySelector('#tabelTemplateTatalaksana tbody');
            var noDataRow = document.querySelector('#tabelTemplateTatalaksana tbody tr:not(.template-row)');

            if (visibleRows.length === 0) {
                if (!noDataRow) {
                    var tr = document.createElement('tr');
                    tr.className = 'no-data-row';
                    tr.innerHTML = '<td colspan="6" class="text-center">Tidak ada template tersedia untuk kategori ini</td>';
                    tbody.appendChild(tr);
                } else {
                    noDataRow.style.display = '';
                }
            } else {
                if (noDataRow) {
                    noDataRow.style.display = 'none';
                }
            }
        });

        // Filter untuk template edukasi
        function filterTemplateEdukasi() {
            var kategori = document.getElementById('filter_kategori_edukasi').value;
            var searchText = document.getElementById('search_edukasi').value.toLowerCase();
            var rows = document.querySelectorAll('#tabelTemplateEdukasi tbody tr.template-row');
            var hasVisibleRows = false;

            rows.forEach(function(row) {
                var rowKategori = row.getAttribute('data-kategori');
                var rowJudul = row.getAttribute('data-judul').toLowerCase();
                var rowIsi = row.cells[2].textContent.toLowerCase();
                var rowTags = row.cells[4].textContent.toLowerCase();

                var matchesKategori = kategori === '' || rowKategori === kategori;
                var matchesSearch = searchText === '' ||
                    rowJudul.includes(searchText) ||
                    rowIsi.includes(searchText) ||
                    rowTags.includes(searchText);

                if (matchesKategori && matchesSearch) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Perbarui nomor urut yang ditampilkan
            var visibleRows = document.querySelectorAll('#tabelTemplateEdukasi tbody tr.template-row:not([style*="display: none"])');
            visibleRows.forEach(function(row, index) {
                row.cells[0].textContent = index + 1;
            });

            // Tampilkan pesan jika tidak ada data
            var tbody = document.querySelector('#tabelTemplateEdukasi tbody');
            var noDataRow = document.querySelector('#tabelTemplateEdukasi tbody tr.no-data-row');

            if (!hasVisibleRows) {
                if (!noDataRow) {
                    var tr = document.createElement('tr');
                    tr.className = 'no-data-row';
                    tr.innerHTML = '<td colspan="6" class="text-center">Tidak ada template edukasi yang sesuai dengan kriteria pencarian</td>';
                    tbody.appendChild(tr);
                } else {
                    noDataRow.style.display = '';
                }
            } else {
                if (noDataRow) {
                    noDataRow.style.display = 'none';
                }
            }
        }

        document.getElementById('filter_kategori_edukasi').addEventListener('change', filterTemplateEdukasi);
        document.getElementById('search_edukasi').addEventListener('input', filterTemplateEdukasi);

        // Filter untuk template resume
        function filterTemplateResume() {
            var kategori = document.getElementById('filter_kategori_resume').value;
            var searchText = document.getElementById('search_resume').value.toLowerCase();
            var rows = document.querySelectorAll('#tabelTemplateResume tbody tr.template-row');
            var hasVisibleRows = false;

            rows.forEach(function(row) {
                var rowKategori = row.getAttribute('data-kategori');
                var rowJudul = row.getAttribute('data-judul').toLowerCase();
                var rowIsi = row.cells[2].textContent.toLowerCase();
                var rowTags = row.cells[4].textContent.toLowerCase();

                var matchesKategori = kategori === '' || rowKategori === kategori;
                var matchesSearch = searchText === '' ||
                    rowJudul.includes(searchText) ||
                    rowIsi.includes(searchText) ||
                    rowTags.includes(searchText);

                if (matchesKategori && matchesSearch) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Perbarui nomor urut yang ditampilkan
            var visibleRows = document.querySelectorAll('#tabelTemplateResume tbody tr.template-row:not([style*="display: none"])');
            visibleRows.forEach(function(row, index) {
                row.cells[0].textContent = index + 1;
            });

            // Tampilkan pesan jika tidak ada data
            var tbody = document.querySelector('#tabelTemplateResume tbody');
            var noDataRow = document.querySelector('#tabelTemplateResume tbody tr.no-data-row');

            if (!hasVisibleRows) {
                if (!noDataRow) {
                    var tr = document.createElement('tr');
                    tr.className = 'no-data-row';
                    tr.innerHTML = '<td colspan="6" class="text-center">Tidak ada template resume yang sesuai dengan kriteria pencarian</td>';
                    tbody.appendChild(tr);
                } else {
                    noDataRow.style.display = '';
                }
            } else {
                if (noDataRow) {
                    noDataRow.style.display = 'none';
                }
            }
        }

        document.getElementById('filter_kategori_resume').addEventListener('change', filterTemplateResume);
        document.getElementById('search_resume').addEventListener('input', filterTemplateResume);
    });
</script>