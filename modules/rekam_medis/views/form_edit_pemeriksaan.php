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
        font-size: 0.75rem !important;
    }

    .modal-title {
        font-size: 0.95rem;
    }

    .modal .table {
        font-size: 0.8rem;
    }

    .modal label {
        font-size: 0.8rem;
    }

    .btn-sm {
        font-size: 0.75rem;
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
                                <div class="mb-3">
                                    <label>Keluhan Utama</label>
                                    <textarea name="keluhan_utama" class="form-control" rows="2"><?= isset($pemeriksaan['keluhan_utama']) ? $pemeriksaan['keluhan_utama'] : '' ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label>Riwayat Penyakit Sekarang</label>
                                    <textarea name="rps" class="form-control" rows="2"><?= isset($pemeriksaan['rps']) ? $pemeriksaan['rps'] : '' ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label>Riwayat Penyakit Dahulu</label>
                                    <textarea name="rpd" class="form-control" rows="2"><?= isset($pemeriksaan['rpd']) ? $pemeriksaan['rpd'] : '' ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label>Alergi</label>
                                    <input type="text" name="alergi" class="form-control" value="<?= isset($pemeriksaan['alergi']) ? $pemeriksaan['alergi'] : '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom 2 -->
                    <div class="col-md-4">
                        <!-- Pemeriksaan Fisik -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Pemeriksaan Fisik</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label>GCS</label>
                                    <input type="text" name="gcs" class="form-control" value="<?= isset($pemeriksaan['gcs']) ? $pemeriksaan['gcs'] : '456' ?>">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label>TD (mmHg)</label>
                                        <input type="text" name="td" class="form-control" value="<?= isset($pemeriksaan['td']) ? $pemeriksaan['td'] : '120/80' ?>">
                                    </div>
                                    <div class="col-6">
                                        <label>Nadi (x/menit)</label>
                                        <input type="text" name="nadi" class="form-control" value="<?= isset($pemeriksaan['nadi']) ? $pemeriksaan['nadi'] : '90' ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label>RR (x/menit)</label>
                                        <input type="text" name="rr" class="form-control" value="<?= isset($pemeriksaan['rr']) ? $pemeriksaan['rr'] : '16' ?>">
                                    </div>
                                    <div class="col-6">
                                        <label>Suhu (°C)</label>
                                        <input type="text" name="suhu" class="form-control" value="<?= isset($pemeriksaan['suhu']) ? $pemeriksaan['suhu'] : '36.4' ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label>SpO2 (%)</label>
                                        <input type="text" name="spo" class="form-control" value="<?= isset($pemeriksaan['spo']) ? $pemeriksaan['spo'] : '99' ?>">
                                    </div>
                                    <div class="col-6">
                                        <label>BB (kg)</label>
                                        <input type="text" name="bb" class="form-control" value="<?= isset($pemeriksaan['bb']) ? $pemeriksaan['bb'] : '' ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>TB (cm)</label>
                                    <input type="text" name="tb" class="form-control" value="<?= isset($pemeriksaan['tb']) ? $pemeriksaan['tb'] : '' ?>">
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
                                            <textarea name="ultra" id="ultrasonografi" class="form-control" rows="8"><?= isset($pemeriksaan['ultra']) ? $pemeriksaan['ultra'] : '' ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border">
                                                <div class="card-header py-1 bg-light">
                                                    <h6 class="mb-0 small">Template USG</h6>
                                                </div>
                                                <div class="card-body p-2">
                                                    <button type="button" class="btn btn-sm btn-info w-100" data-bs-toggle="modal" data-bs-target="#modalDaftarTemplateUsg">
                                                        <i class="fas fa-list"></i> Lihat Template
                                                    </button>
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
                        <!-- Pemeriksaan Organ -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Pemeriksaan Organ</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <label>Kepala</label>
                                            <select name="kepala" class="form-select">
                                                <option value="Normal" <?= (isset($pemeriksaan['kepala']) && $pemeriksaan['kepala'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['kepala']) && $pemeriksaan['kepala'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['kepala']) && $pemeriksaan['kepala'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Mata</label>
                                            <select name="mata" class="form-select">
                                                <option value="Normal" <?= (isset($pemeriksaan['mata']) && $pemeriksaan['mata'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['mata']) && $pemeriksaan['mata'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['mata']) && $pemeriksaan['mata'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Gigi</label>
                                            <select name="gigi" class="form-select">
                                                <option value="Normal" <?= (isset($pemeriksaan['gigi']) && $pemeriksaan['gigi'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['gigi']) && $pemeriksaan['gigi'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['gigi']) && $pemeriksaan['gigi'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>THT</label>
                                            <select name="tht" class="form-select">
                                                <option value="Normal" <?= (isset($pemeriksaan['tht']) && $pemeriksaan['tht'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['tht']) && $pemeriksaan['tht'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['tht']) && $pemeriksaan['tht'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Thoraks</label>
                                            <select name="thoraks" class="form-select">
                                                <option value="Normal" <?= (isset($pemeriksaan['thoraks']) && $pemeriksaan['thoraks'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['thoraks']) && $pemeriksaan['thoraks'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['thoraks']) && $pemeriksaan['thoraks'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <label>Abdomen</label>
                                            <select name="abdomen" class="form-select">
                                                <option value="Normal" <?= (isset($pemeriksaan['abdomen']) && $pemeriksaan['abdomen'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['abdomen']) && $pemeriksaan['abdomen'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['abdomen']) && $pemeriksaan['abdomen'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Genital</label>
                                            <select name="genital" class="form-select">
                                                <option value="Normal" <?= (isset($pemeriksaan['genital']) && $pemeriksaan['genital'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['genital']) && $pemeriksaan['genital'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['genital']) && $pemeriksaan['genital'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Ekstremitas</label>
                                            <select name="ekstremitas" class="form-select">
                                                <option value="Normal" <?= (isset($pemeriksaan['ekstremitas']) && $pemeriksaan['ekstremitas'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['ekstremitas']) && $pemeriksaan['ekstremitas'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['ekstremitas']) && $pemeriksaan['ekstremitas'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Kulit</label>
                                            <select name="kulit" class="form-select">
                                                <option value="Normal" <?= (isset($pemeriksaan['kulit']) && $pemeriksaan['kulit'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                                <option value="Abnormal" <?= (isset($pemeriksaan['kulit']) && $pemeriksaan['kulit'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                                <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['kulit']) && $pemeriksaan['kulit'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Keterangan Pemeriksaan Fisik</label>
                                    <textarea name="ket_fisik" class="form-control" rows="2"><?= isset($pemeriksaan['ket_fisik']) ? $pemeriksaan['ket_fisik'] : '' ?></textarea>
                                </div>
                            </div>
                        </div>

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
                                            <textarea name="diagnosis" id="diagnosis" class="form-control" rows="2"><?= isset($pemeriksaan['diagnosis']) ? $pemeriksaan['diagnosis'] : '' ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border">
                                                <div class="card-header py-1 bg-light">
                                                    <h6 class="mb-0 small">Riwayat Diagnosis</h6>
                                                </div>
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
                                            <textarea name="tata" id="tatalaksana" class="form-control" rows="4"><?= isset($pemeriksaan['tata']) ? $pemeriksaan['tata'] : '' ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border">
                                                <div class="card-header py-1 bg-light">
                                                    <h6 class="mb-0 small">Template Tatalaksana</h6>
                                                </div>
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
                        <form method="get" class="d-flex">
                            <input type="hidden" name="module" value="rekam_medis">
                            <input type="hidden" name="action" value="edit_pemeriksaan">
                            <input type="hidden" name="no_rawat" value="<?= $pemeriksaan['no_rawat'] ?>">
                            <select name="kategori" class="form-select me-2" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                <option value="fetomaternal" <?= isset($_GET['kategori']) && $_GET['kategori'] == 'fetomaternal' ? 'selected' : '' ?>>Fetomaternal</option>
                                <option value="ginekologi umum" <?= isset($_GET['kategori']) && $_GET['kategori'] == 'ginekologi umum' ? 'selected' : '' ?>>Ginekologi Umum</option>
                                <option value="onkogin" <?= isset($_GET['kategori']) && $_GET['kategori'] == 'onkogin' ? 'selected' : '' ?>>Onkogin</option>
                                <option value="fertilitas" <?= isset($_GET['kategori']) && $_GET['kategori'] == 'fertilitas' ? 'selected' : '' ?>>Fertilitas</option>
                                <option value="uroginekologi" <?= isset($_GET['kategori']) && $_GET['kategori'] == 'uroginekologi' ? 'selected' : '' ?>>Uroginekologi</option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Tabel Template -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
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

                            // Filter berdasarkan kategori
                            $where = "";
                            if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
                                $kategori = $conn->quote($_GET['kategori']);
                                $where = "WHERE kategori_tx = $kategori AND status = 'active'";
                            } else {
                                $where = "WHERE status = 'active'";
                            }

                            // Query untuk mendapatkan template
                            $sql = "SELECT * FROM template_tatalaksana $where ORDER BY kategori_tx ASC, nama_template_tx ASC";
                            $stmt = $conn->query($sql);

                            if ($stmt->rowCount() > 0) {
                                $no = 1;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
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
                        <form method="get" class="d-flex">
                            <input type="hidden" name="module" value="rekam_medis">
                            <input type="hidden" name="action" value="edit_pemeriksaan">
                            <input type="hidden" name="no_rawat" value="<?= $pemeriksaan['no_rawat'] ?>">
                            <select name="kategori_usg" class="form-select me-2" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                <option value="obstetri" <?= isset($_GET['kategori_usg']) && $_GET['kategori_usg'] == 'obstetri' ? 'selected' : '' ?>>Obstetri</option>
                                <option value="ginekologi" <?= isset($_GET['kategori_usg']) && $_GET['kategori_usg'] == 'ginekologi' ? 'selected' : '' ?>>Ginekologi</option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Tabel Template -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
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

                            // Filter berdasarkan kategori
                            $where = "";
                            if (isset($_GET['kategori_usg']) && !empty($_GET['kategori_usg'])) {
                                $kategori = $conn->quote($_GET['kategori_usg']);
                                $where = "WHERE kategori_usg = $kategori AND status = 'active'";
                            } else {
                                $where = "WHERE status = 'active'";
                            }

                            // Query untuk mendapatkan template
                            $sql = "SELECT * FROM template_usg $where ORDER BY kategori_usg ASC, nama_template_usg ASC";
                            $stmt = $conn->query($sql);

                            if ($stmt->rowCount() > 0) {
                                $no = 1;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
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
</script>