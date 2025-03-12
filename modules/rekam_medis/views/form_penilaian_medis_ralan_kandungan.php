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
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <textarea name="ultra" id="ultrasonografi" class="form-control" rows="8"></textarea>
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
                                            <textarea name="lab" class="form-control" rows="2"></textarea>
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
                                            <textarea name="ket_fisik" class="form-control" rows="2">saat ini dalam batas normal</textarea>
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
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <textarea name="diagnosis" id="diagnosis" class="form-control" rows="2"></textarea>
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
                                                    <textarea name="tata" id="tatalaksana" class="form-control" rows="4"></textarea>
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
                            <input type="hidden" name="action" value="form_penilaian_medis_ralan_kandungan">
                            <input type="hidden" name="no_rawat" value="<?= $data['no_rawat'] ?>">
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
                            $conn = new mysqli('auth-db1151.hstgr.io', 'u609399718_adminpraktek', 'Obgin@12345', 'u609399718_praktekobgin');

                            if ($conn->connect_error) {
                                die("Koneksi gagal: " . $conn->connect_error);
                            }

                            // Query untuk mengambil data template
                            $where = "";
                            if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
                                $kategori = $conn->real_escape_string($_GET['kategori']);
                                $where = "WHERE kategori_tx = '$kategori' AND status = 'active'";
                            } else {
                                $where = "WHERE status = 'active'";
                            }

                            $sql = "SELECT * FROM template_tatalaksana $where ORDER BY kategori_tx ASC, nama_template_tx ASC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $no = 1;
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_template_tx']) . "</td>";
                                    echo "<td><div style='max-height: 100px; overflow-y: auto;'>" . nl2br(htmlspecialchars($row['isi_template_tx'])) . "</div></td>";
                                    echo "<td>" . htmlspecialchars($row['kategori_tx']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tags'] ?? '-') . "</td>";
                                    echo "<td>
                                            <button type='button' class='btn btn-sm btn-primary mb-1 w-100' onclick='gunakanTemplate(" . json_encode($row['isi_template_tx']) . ")'>
                                                <i class='fas fa-copy'></i> Gunakan
                                            </button>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>Tidak ada data template</td></tr>";
                            }

                            $conn->close();
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
                            <input type="hidden" name="action" value="form_penilaian_medis_ralan_kandungan">
                            <input type="hidden" name="no_rawat" value="<?= $data['no_rawat'] ?>">
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
                            $no_rkm_medis = $data['no_rkm_medis'];

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