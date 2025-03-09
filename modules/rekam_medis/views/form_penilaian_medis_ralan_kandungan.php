<?php
// Pastikan tidak ada akses langsung ke file ini
if (!defined('BASE_PATH')) {
    die('No direct script access allowed');
}
?>

<style>
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
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Penilaian Medis Rawat Jalan Kandungan</h3>
                </div>
                <div class="card-body">
                    <form action="index.php?module=rekam_medis&action=simpan_penilaian_medis_ralan_kandungan" method="POST">
                        <input type="hidden" name="no_rawat" value="<?= $data['no_rawat'] ?>">
                        <input type="hidden" name="tanggal" value="<?= date('Y-m-d H:i:s') ?>">
                        <input type="hidden" name="anamnesis" value="Autoanamnesis">
                        <input type="hidden" name="hubungan" value="-">
                        <input type="hidden" name="keadaan" value="Sehat">
                        <input type="hidden" name="kesadaran" value="Compos Mentis">
                        <input type="hidden" name="kepala" value="Normal">
                        <input type="hidden" name="mata" value="Normal">
                        <input type="hidden" name="gigi" value="Normal">
                        <input type="hidden" name="tht" value="Normal">
                        <input type="hidden" name="thoraks" value="Normal">
                        <input type="hidden" name="abdomen" value="Normal">
                        <input type="hidden" name="genital" value="Normal">
                        <input type="hidden" name="ekstremitas" value="Normal">
                        <input type="hidden" name="kulit" value="Normal">

                        <div class="row">
                            <!-- Kolom 1 -->
                            <div class="col-md-4">
                                <!-- Data Pasien -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Pasien</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <th width="150">No. Rawat</th>
                                                <td><?= $data['no_rawat'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nama Pasien</th>
                                                <td><?= $data['nm_pasien'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>No. RM</th>
                                                <td><?= $data['no_rkm_medis'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal Lahir</th>
                                                <td><?= date('d-m-Y', strtotime($data['tgl_lahir'])) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Jenis Kelamin</th>
                                                <td><?= $data['jk'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal Periksa</th>
                                                <td><?= date('d-m-Y', strtotime($data['tgl_registrasi'])) ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <!-- Anamnesis -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Anamnesis</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label>Keluhan Utama</label>
                                            <textarea name="keluhan_utama" class="form-control" rows="2" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label>Riwayat Penyakit Sekarang</label>
                                            <textarea name="rps" class="form-control" rows="2"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label>Riwayat Penyakit Dahulu</label>
                                            <textarea name="rpd" class="form-control" rows="2"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label>Alergi</label>
                                            <input type="text" name="alergi" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom 2 -->
                            <div class="col-md-4">
                                <!-- Pemeriksaan Fisik -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Pemeriksaan Fisik</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label>GCS</label>
                                            <input type="text" name="gcs" class="form-control" required value="456">
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label>TD (mmHg)</label>
                                                <input type="text" name="td" class="form-control" required value="120/80">
                                            </div>
                                            <div class="col-6">
                                                <label>Nadi (x/menit)</label>
                                                <input type="text" name="nadi" class="form-control" required value="90">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label>RR (x/menit)</label>
                                                <input type="text" name="rr" class="form-control" required value="16">
                                            </div>
                                            <div class="col-6">
                                                <label>Suhu (°C)</label>
                                                <input type="text" name="suhu" class="form-control" required value="36.4">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label>SpO2 (%)</label>
                                                <input type="text" name="spo" class="form-control" value="99">
                                            </div>
                                            <div class="col-6">
                                                <label>BB (kg)</label>
                                                <input type="text" name="bb" class="form-control">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label>TB (cm)</label>
                                            <input type="text" name="tb" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <!-- Pemeriksaan Penunjang -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Pemeriksaan Penunjang</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label>Ultrasonografi</label>
                                            <textarea name="ultra" class="form-control" rows="2" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label>Laboratorium</label>
                                            <textarea name="lab" class="form-control" rows="2" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom 3 -->
                            <div class="col-md-4">
                                <!-- Pemeriksaan Organ -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Pemeriksaan Organ</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="mb-2">
                                                    <label>Kepala</label>
                                                    <select name="kepala" class="form-select" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Mata</label>
                                                    <select name="mata" class="form-select" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Gigi</label>
                                                    <select name="gigi" class="form-select" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>THT</label>
                                                    <select name="tht" class="form-select" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Thoraks</label>
                                                    <select name="thoraks" class="form-select" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-2">
                                                    <label>Abdomen</label>
                                                    <select name="abdomen" class="form-select" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Genital</label>
                                                    <select name="genital" class="form-select" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Ekstremitas</label>
                                                    <select name="ekstremitas" class="form-select" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Kulit</label>
                                                    <select name="kulit" class="form-select" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label>Keterangan Pemeriksaan Fisik</label>
                                            <textarea name="ket_fisik" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Diagnosis & Tatalaksana -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Diagnosis & Tatalaksana</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label>Diagnosis</label>
                                            <textarea name="diagnosis" class="form-control" rows="2" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label>Tatalaksana</label>
                                            <textarea name="tata" class="form-control" rows="2" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label>Tanggal Kontrol</label>
                                            <input type="date" name="tanggal_kontrol" class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <label>Atensi</label>
                                            <select name="atensi" class="form-select">
                                                <option value="0">Tidak</option>
                                                <option value="1">Ya</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="history.back()">
                                <i class="fas fa-times"></i> Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>