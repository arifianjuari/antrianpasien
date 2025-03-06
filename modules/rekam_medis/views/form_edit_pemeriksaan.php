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
                            <th>Tanggal Lahir</th>
                            <td><?= date('d-m-Y', strtotime($pasien['tgl_lahir'])) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="150">Tanggal Pemeriksaan</th>
                            <td><?= date('d-m-Y H:i', strtotime($pemeriksaan['tanggal'])) ?></td>
                        </tr>
                        <tr>
                            <th>No. Rawat</th>
                            <td><?= $pemeriksaan['no_rawat'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <form action="index.php?module=rekam_medis&action=update_pemeriksaan" method="post">
                <input type="hidden" name="no_rawat" value="<?= $pemeriksaan['no_rawat'] ?>">
                <input type="hidden" name="no_rkm_medis" value="<?= $pasien['no_rkm_medis'] ?>">

                <!-- Anamnesis -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Anamnesis</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Keluhan Utama</label>
                            <textarea name="keluhan_utama" class="form-control" rows="3" required><?= isset($pemeriksaan['keluhan_utama']) ? $pemeriksaan['keluhan_utama'] : '' ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Riwayat Penyakit Sekarang</label>
                            <textarea name="rps" class="form-control" rows="3"><?= isset($pemeriksaan['rps']) ? $pemeriksaan['rps'] : '' ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Riwayat Penyakit Dahulu</label>
                            <textarea name="rpd" class="form-control" rows="3"><?= isset($pemeriksaan['rpd']) ? $pemeriksaan['rpd'] : '' ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Alergi</label>
                            <input type="text" name="alergi" class="form-control" value="<?= isset($pemeriksaan['alergi']) ? $pemeriksaan['alergi'] : '' ?>">
                        </div>
                    </div>
                </div>

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

                <!-- Pemeriksaan Organ -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Pemeriksaan Organ</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Kepala</label>
                                    <select name="kepala" class="form-control">
                                        <option value="Normal" <?= (isset($pemeriksaan['kepala']) && $pemeriksaan['kepala'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                        <option value="Abnormal" <?= (isset($pemeriksaan['kepala']) && $pemeriksaan['kepala'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                        <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['kepala']) && $pemeriksaan['kepala'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Mata</label>
                                    <select name="mata" class="form-control">
                                        <option value="Normal" <?= (isset($pemeriksaan['mata']) && $pemeriksaan['mata'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                        <option value="Abnormal" <?= (isset($pemeriksaan['mata']) && $pemeriksaan['mata'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                        <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['mata']) && $pemeriksaan['mata'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Gigi</label>
                                    <select name="gigi" class="form-control">
                                        <option value="Normal" <?= (isset($pemeriksaan['gigi']) && $pemeriksaan['gigi'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                        <option value="Abnormal" <?= (isset($pemeriksaan['gigi']) && $pemeriksaan['gigi'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                        <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['gigi']) && $pemeriksaan['gigi'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>THT</label>
                                    <select name="tht" class="form-control">
                                        <option value="Normal" <?= (isset($pemeriksaan['tht']) && $pemeriksaan['tht'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                        <option value="Abnormal" <?= (isset($pemeriksaan['tht']) && $pemeriksaan['tht'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                        <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['tht']) && $pemeriksaan['tht'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Thoraks</label>
                                    <select name="thoraks" class="form-control">
                                        <option value="Normal" <?= (isset($pemeriksaan['thoraks']) && $pemeriksaan['thoraks'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                        <option value="Abnormal" <?= (isset($pemeriksaan['thoraks']) && $pemeriksaan['thoraks'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                        <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['thoraks']) && $pemeriksaan['thoraks'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Abdomen</label>
                                    <select name="abdomen" class="form-control">
                                        <option value="Normal" <?= (isset($pemeriksaan['abdomen']) && $pemeriksaan['abdomen'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                        <option value="Abnormal" <?= (isset($pemeriksaan['abdomen']) && $pemeriksaan['abdomen'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                        <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['abdomen']) && $pemeriksaan['abdomen'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Genital</label>
                                    <select name="genital" class="form-control">
                                        <option value="Normal" <?= (isset($pemeriksaan['genital']) && $pemeriksaan['genital'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                        <option value="Abnormal" <?= (isset($pemeriksaan['genital']) && $pemeriksaan['genital'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                        <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['genital']) && $pemeriksaan['genital'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Ekstremitas</label>
                                    <select name="ekstremitas" class="form-control">
                                        <option value="Normal" <?= (isset($pemeriksaan['ekstremitas']) && $pemeriksaan['ekstremitas'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                        <option value="Abnormal" <?= (isset($pemeriksaan['ekstremitas']) && $pemeriksaan['ekstremitas'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                        <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['ekstremitas']) && $pemeriksaan['ekstremitas'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Kulit</label>
                                    <select name="kulit" class="form-control">
                                        <option value="Normal" <?= (isset($pemeriksaan['kulit']) && $pemeriksaan['kulit'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                                        <option value="Abnormal" <?= (isset($pemeriksaan['kulit']) && $pemeriksaan['kulit'] == 'Abnormal') ? 'selected' : '' ?>>Abnormal</option>
                                        <option value="Tidak Diperiksa" <?= (isset($pemeriksaan['kulit']) && $pemeriksaan['kulit'] == 'Tidak Diperiksa') ? 'selected' : '' ?>>Tidak Diperiksa</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Keterangan Pemeriksaan Fisik</label>
                            <textarea name="ket_fisik" class="form-control" rows="3"><?= isset($pemeriksaan['ket_fisik']) ? $pemeriksaan['ket_fisik'] : '' ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pemeriksaan Penunjang -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Pemeriksaan Penunjang</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Ultrasonografi</label>
                            <textarea name="ultra" class="form-control" rows="3"><?= isset($pemeriksaan['ultra']) ? $pemeriksaan['ultra'] : '' ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Laboratorium</label>
                            <textarea name="lab" class="form-control" rows="3"><?= isset($pemeriksaan['lab']) ? $pemeriksaan['lab'] : '' ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Diagnosis & Tatalaksana -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Diagnosis & Tatalaksana</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Diagnosis</label>
                            <textarea name="diagnosis" class="form-control" rows="3" required><?= isset($pemeriksaan['diagnosis']) ? $pemeriksaan['diagnosis'] : '' ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Tatalaksana</label>
                            <textarea name="tata" class="form-control" rows="3" required><?= isset($pemeriksaan['tata']) ? $pemeriksaan['tata'] : '' ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>