<?php
// Pastikan tidak ada akses langsung ke file ini
if (!defined('BASE_PATH')) {
    die('No direct script access allowed');
}

// Ambil data TB dan BB terakhir
$tb_terakhir = '';
$bb_terakhir = '';
$diagnosis_terakhir = '';
$tatalaksana_terakhir = '';
$resep_terakhir = '';
$no_rkm_medis = $data['no_rkm_medis'];

$conn = new mysqli('auth-db1151.hstgr.io', 'u609399718_adminpraktek', 'Obgin@12345', 'u609399718_praktekobgin');

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$sql = "SELECT tb, bb, diagnosis, tata, resep 
        FROM penilaian_medis_ralan_kandungan pmrk
        JOIN reg_periksa rp ON pmrk.no_rawat = rp.no_rawat
        WHERE rp.no_rkm_medis = ? 
        AND (pmrk.tb IS NOT NULL OR pmrk.bb IS NOT NULL OR pmrk.diagnosis IS NOT NULL 
             OR pmrk.tata IS NOT NULL OR pmrk.resep IS NOT NULL)
        AND (pmrk.tb != '' OR pmrk.bb != '' OR pmrk.diagnosis != '' 
             OR pmrk.tata != '' OR pmrk.resep != '')
        ORDER BY pmrk.tanggal DESC 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $no_rkm_medis);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $tb_terakhir = $row['tb'];
    $bb_terakhir = $row['bb'];
    $diagnosis_terakhir = $row['diagnosis'];
    $tatalaksana_terakhir = $row['tata'];
    $resep_terakhir = $row['resep'];
}

$stmt->close();
$conn->close();
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
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="mb-2">
                                                    <label>Keluhan Utama</label>
                                                    <textarea name="keluhan_utama" class="form-control form-control-sm" rows="2" required></textarea>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Riwayat Penyakit Sekarang</label>
                                                    <textarea name="rps" class="form-control form-control-sm" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-2">
                                                    <label>Riwayat Penyakit Dahulu</label>
                                                    <textarea name="rpd" class="form-control form-control-sm" rows="2"></textarea>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Alergi</label>
                                                    <input type="text" name="alergi" class="form-control form-control-sm">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pemeriksaan Fisik -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Pemeriksaan Fisik</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <div class="mb-2">
                                                    <label>GCS</label>
                                                    <input type="text" name="gcs" class="form-control form-control-sm" required value="456">
                                                </div>
                                                <div class="mb-2">
                                                    <label>TD (mmHg)</label>
                                                    <input type="text" name="td" class="form-control form-control-sm" required value="120/80">
                                                </div>
                                                <div class="mb-2">
                                                    <label>Nadi (x/menit)</label>
                                                    <input type="text" name="nadi" class="form-control form-control-sm" required value="90">
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="mb-2">
                                                    <label>RR (x/menit)</label>
                                                    <input type="text" name="rr" class="form-control form-control-sm" required value="16">
                                                </div>
                                                <div class="mb-2">
                                                    <label>Suhu (°C)</label>
                                                    <input type="text" name="suhu" class="form-control form-control-sm" required value="36.4">
                                                </div>
                                                <div class="mb-2">
                                                    <label>SpO2 (%)</label>
                                                    <input type="text" name="spo" class="form-control form-control-sm" value="99">
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="mb-2">
                                                    <label>BB (kg)</label>
                                                    <input type="text" name="bb" class="form-control form-control-sm" value="<?= htmlspecialchars($bb_terakhir) ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label>TB (cm)</label>
                                                    <input type="text" name="tb" class="form-control form-control-sm" value="<?= htmlspecialchars($tb_terakhir) ?>">
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
                                        <h5 class="card-title mb-0">Pemeriksaan Organ</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <div class="mb-2">
                                                    <label>Kepala</label>
                                                    <select name="kepala" class="form-select form-select-sm" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Mata</label>
                                                    <select name="mata" class="form-select form-select-sm" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Gigi</label>
                                                    <select name="gigi" class="form-select form-select-sm" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="mb-2">
                                                    <label>THT</label>
                                                    <select name="tht" class="form-select form-select-sm" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Thoraks</label>
                                                    <select name="thoraks" class="form-select form-select-sm" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Abdomen</label>
                                                    <select name="abdomen" class="form-select form-select-sm" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="mb-2">
                                                    <label>Genital</label>
                                                    <select name="genital" class="form-select form-select-sm" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Ekstremitas</label>
                                                    <select name="ekstremitas" class="form-select form-select-sm" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Kulit</label>
                                                    <select name="kulit" class="form-select form-select-sm" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Abnormal">Abnormal</option>
                                                        <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label>Keterangan Pemeriksaan Fisik</label>
                                            <textarea name="ket_fisik" class="form-control form-control-sm" rows="1"></textarea>
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
                                                    <textarea name="ultra" id="ultrasonografi" class="form-control" rows="10"></textarea>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="card border">
                                                        <div class="card-header py-1 bg-light">
                                                            <h6 class="mb-0 small">Template USG</h6>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <button type="button" class="btn btn-sm btn-info w-100" data-bs-toggle="modal" data-bs-target="#modalDaftarTemplateUsg">
                                                                <i class="fas fa-list"></i> Lihat Template USG
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
                                                    <textarea name="diagnosis" id="diagnosis" class="form-control" rows="2"><?= htmlspecialchars($diagnosis_terakhir) ?></textarea>
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
                                                    <textarea name="tata" id="tatalaksana" class="form-control" rows="4"><?= htmlspecialchars($tatalaksana_terakhir) ?></textarea>
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
                                            <label>Edukasi</label>
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <textarea name="edukasi" id="edukasi" class="form-control" rows="4"></textarea>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="card border">
                                                        <div class="card-header py-1 bg-light">
                                                            <h6 class="mb-0 small">Template Edukasi</h6>
                                                        </div>
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
                                            <label>Resep</label>
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <textarea name="resep" id="resep" class="form-control" rows="4"><?= htmlspecialchars($resep_terakhir) ?></textarea>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="card border">
                                                        <div class="card-header py-1 bg-light">
                                                            <h6 class="mb-0 small">Formularium</h6>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <button type="button" class="btn btn-sm btn-info w-100" data-bs-toggle="modal" data-bs-target="#modalDaftarTemplateResep">
                                                                <i class="fas fa-list"></i> Lihat Daftar
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
                            $conn = new mysqli('auth-db1151.hstgr.io', 'u609399718_adminpraktek', 'Obgin@12345', 'u609399718_praktekobgin');

                            if ($conn->connect_error) {
                                die("Koneksi gagal: " . $conn->connect_error);
                            }

                            // Query untuk mengambil semua data template
                            $sql = "SELECT * FROM template_tatalaksana WHERE status = 'active' ORDER BY kategori_tx ASC, nama_template_tx ASC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $no = 1;
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr class='template-row' data-kategori='" . htmlspecialchars($row['kategori_tx']) . "'>";
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
                        <select id="filter_kategori_usg" class="form-select me-2">
                            <option value="">Semua Kategori</option>
                            <option value="obstetri" <?= isset($_GET['kategori_usg']) && $_GET['kategori_usg'] == 'obstetri' ? 'selected' : '' ?>>Obstetri</option>
                            <option value="ginekologi" <?= isset($_GET['kategori_usg']) && $_GET['kategori_usg'] == 'ginekologi' ? 'selected' : '' ?>>Ginekologi</option>
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
                                    echo "<tr class='template-row' data-kategori='" . $row['kategori_usg'] . "'>";
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
                            $conn = new mysqli('auth-db1151.hstgr.io', 'u609399718_adminpraktek', 'Obgin@12345', 'u609399718_praktekobgin');

                            if ($conn->connect_error) {
                                die("Koneksi gagal: " . $conn->connect_error);
                            }

                            // Query untuk mendapatkan semua data formularium
                            $sql = "SELECT * FROM formularium WHERE status_aktif = 1 ORDER BY nama_obat ASC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
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

                            $conn->close();
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
                <!-- Filter Kategori -->
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
                            $conn = new mysqli('auth-db1151.hstgr.io', 'u609399718_adminpraktek', 'Obgin@12345', 'u609399718_praktekobgin');

                            if ($conn->connect_error) {
                                die("Koneksi gagal: " . $conn->connect_error);
                            }

                            // Query untuk mendapatkan semua template edukasi
                            $sql = "SELECT * FROM edukasi WHERE status_aktif = 1 ORDER BY kategori ASC, judul ASC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $no = 1;
                                while ($row = $result->fetch_assoc()) {
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

                var textObat = namaObat + ' - ' + bentukDosis;

                if (catatan) {
                    textObat += '\nCatatan: ' + catatan;
                }
                obatTerpilih.push(textObat);
            }
        }

        if (obatTerpilih.length > 0) {
            var currentValue = resepField.value;
            var newValue = obatTerpilih.join('\n');

            if (currentValue && currentValue.trim() !== '') {
                resepField.value = currentValue + '\n' + newValue;
            } else {
                resepField.value = newValue;
            }
        }

        $('#modalDaftarTemplateResep').modal('hide');
    }

    // Filter untuk template USG
    document.addEventListener('DOMContentLoaded', function() {
        // Tambahkan event listener untuk filter kategori USG
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
            var noDataRow = document.querySelector('#tabelTemplateUsg tbody tr:not(.template-row)');
            if (noDataRow) {
                noDataRow.style.display = visibleRows.length === 0 ? '' : 'none';
            } else if (visibleRows.length === 0) {
                var tbody = document.querySelector('#tabelTemplateUsg tbody');
                var tr = document.createElement('tr');
                tr.className = 'no-data-row';
                tr.innerHTML = '<td colspan="6" class="text-center">Tidak ada template tersedia</td>';
                tbody.appendChild(tr);
            } else {
                var noDataRows = document.querySelectorAll('#tabelTemplateUsg tbody tr.no-data-row');
                noDataRows.forEach(function(row) {
                    row.style.display = 'none';
                });
            }
        });

        // Tambahkan event listener untuk filter kategori obat dan pencarian nama generik
        function filterTable() {
            var kategori = document.getElementById('filter_kategori_obat').value;
            var searchTerm = document.getElementById('search_generik').value.toLowerCase();
            var rows = document.querySelectorAll('#tabelFormularium tbody tr.obat-row');

            rows.forEach(function(row) {
                var rowKategori = row.getAttribute('data-kategori');
                var namaGenerik = row.cells[2].textContent.toLowerCase(); // Kolom nama generik
                var showByKategori = kategori === '' || rowKategori === kategori;
                var showBySearch = searchTerm === '' || namaGenerik.includes(searchTerm);

                if (showByKategori && showBySearch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // Tampilkan pesan jika tidak ada data
            var visibleRows = document.querySelectorAll('#tabelFormularium tbody tr.obat-row:not([style*="display: none"])');
            if (visibleRows.length === 0) {
                var tbody = document.querySelector('#tabelFormularium tbody');
                var noDataRow = document.querySelector('#tabelFormularium tbody tr.no-data-row');

                if (!noDataRow) {
                    var tr = document.createElement('tr');
                    tr.className = 'no-data-row';
                    tr.innerHTML = '<td colspan="7" class="text-center">Tidak ada data obat yang sesuai dengan kriteria pencarian</td>';
                    tbody.appendChild(tr);
                } else {
                    noDataRow.style.display = '';
                }
            } else {
                var noDataRows = document.querySelectorAll('#tabelFormularium tbody tr.no-data-row');
                noDataRows.forEach(function(row) {
                    row.style.display = 'none';
                });
            }

            // Uncheck "Pilih Semua" checkbox saat filter berubah
            document.getElementById('checkAll').checked = false;
        }

        // Event listener untuk filter kategori
        document.getElementById('filter_kategori_obat').addEventListener('change', filterTable);

        // Event listener untuk pencarian nama generik
        document.getElementById('search_generik').addEventListener('input', filterTable);

        // Inisialisasi DataTables untuk tabel formularium
        $(document).ready(function() {
            var table = $('#tabelFormularium').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                pageLength: 10,
                order: [
                    [1, 'asc']
                ]
            });

            // Hapus event handler lama untuk filter kategori
            $('select[name="kategori_obat"]').off('change');
        });

        // Tambahkan event listener untuk filter kategori tatalaksana
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
    });
</script>