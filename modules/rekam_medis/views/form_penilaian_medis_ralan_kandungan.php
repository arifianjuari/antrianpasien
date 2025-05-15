<?php
// Pastikan tidak ada akses langsung ke file ini
if (!defined('BASE_PATH')) {
    die('No direct script access allowed');
}

// Pastikan session sudah dimulai
if (!isset($_SESSION)) {
    session_start();
}

// Set default source_page jika belum ada
if (!isset($_SESSION['source_page'])) {
    $_SESSION['source_page'] = 'form_penilaian_medis_ralan_kandungan';
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

// Query untuk data terakhir
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

// Ambil data status obstetri
$sql_status_obstetri = "SELECT * FROM status_obstetri WHERE no_rkm_medis = ? ORDER BY updated_at DESC";
$stmt_status_obstetri = $conn->prepare($sql_status_obstetri);
$stmt_status_obstetri->bind_param("s", $no_rkm_medis);
$stmt_status_obstetri->execute();
$result_status_obstetri = $stmt_status_obstetri->get_result();
$statusObstetri = [];
while ($row = $result_status_obstetri->fetch_assoc()) {
    $statusObstetri[] = $row;
}
$stmt_status_obstetri->close();

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

    /* Style untuk tab */
    .tab-pane {
        transition: all 0.3s ease-in-out;
        overflow: hidden;
        font-size: 0.8rem;
        padding: 15px;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 0.25rem 0.25rem;
    }

    .tab-pane:not(.active),
    .tab-pane:not(.show) {
        display: none;
    }

    .nav-tabs {
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 0;
    }

    .nav-tabs .nav-link {
        display: flex;
        align-items: center;
        font-size: 0.85rem;
        cursor: pointer;
        padding: 0.75rem 1rem;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        margin-right: 0.25rem;
        border-radius: 0.25rem 0.25rem 0 0;
    }

    .nav-tabs .nav-link.active,
    .nav-tabs .nav-link:not(.collapsed) {
        background-color: #fff;
        border-bottom-color: #fff;
    }

    .nav-tabs .nav-link i {
        margin-left: 5px;
        transition: transform 0.3s;
    }

    .nav-tabs .nav-link.collapsed i {
        transform: rotate(-90deg);
    }

    .nav-tabs .nav-link:not(.collapsed) i {
        transform: rotate(0deg);
    }

    /* Style untuk tabel status obstetri */
    .table-responsive {
        margin-top: 1rem;
    }

    .table-sm {
        font-size: 0.85rem;
    }

    .btn-add {
        background-color: #28a745;
        color: white;
    }

    .btn-add:hover {
        background-color: #218838;
        color: white;
    }

    /* Fix tampilan tabel status obstetri */
    #skrining .table th,
    #skrining .table td {
        padding: 0.5rem;
        vertical-align: middle;
    }

    /* Loading spinner style */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    /* Debug box style */
    .alert-info {
        font-size: 0.8rem;
    }

    .alert-info h6 {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    /* Fix tampilan tabel pada mode mobile */
    @media (max-width: 768px) {
        #skrining .table {
            font-size: 0.75rem;
        }

        #skrining .table th,
        #skrining .table td {
            padding: 0.3rem;
        }

        #skrining .btn-sm {
            padding: 0.15rem 0.3rem;
            font-size: 0.6rem;
        }
    }
</style>

<!-- Load Status Obstetri Helper -->
<script src="<?= BASE_URL ?>/assets/js/status_obstetri_helper.js"></script>

<!-- Initialize BASE_URL variable for JavaScript -->
<script>
    // Make BASE_URL available to JavaScript
    var BASE_URL = '<?= BASE_URL ?>';
    console.log('BASE_URL initialized as:', BASE_URL);
</script>

<!-- Modal untuk menampilkan gambar edukasi -->
<div class="modal fade" id="gambarEdukasiModal" tabindex="-1" aria-labelledby="gambarEdukasiModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gambarEdukasiModalLabel">Gambar Edukasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="gambarEdukasiContent">
                <!-- Gambar akan dimuat di sini -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Penilaian Medis Rawat Jalan Kandungan</h3>
                </div>
                <div class="card-body">

                    <!-- Tab Identitas dan Status Obstetri -->
                    <ul class="nav nav-tabs mb-0" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="identitas-tab" data-toggle="collapse" href="#identitas" role="tab">
                                Identitas <i class="fas fa-chevron-down"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link collapsed" id="skrining-tab" data-toggle="collapse" href="#skrining" role="tab">
                                Status Obstetri <i class="fas fa-chevron-down"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link collapsed" id="riwayat-kehamilan-tab" data-toggle="collapse" href="#riwayat-kehamilan" role="tab">
                                Riwayat Kehamilan <i class="fas fa-chevron-down"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link collapsed" id="status-ginekologi-tab" data-toggle="collapse" href="#status-ginekologi" role="tab">
                                Status Ginekologi <i class="fas fa-chevron-down"></i>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade collapse show" id="identitas" role="tabpanel">
                            <div class="row">
                                <!-- Kolom Kiri -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="card-title mb-0" style="font-size: 0.9rem;">Data Pribadi</h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-sm table-hover" style="font-size: 0.85rem;">
                                                <tr>
                                                    <th class="text-muted px-3">Nama Pasien</th>
                                                    <td class="px-3"><?= $data['nm_pasien'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">Jenis Kelamin</th>
                                                    <td class="px-3"><?= $data['jk'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">Tanggal Lahir</th>
                                                    <td class="px-3"><?= date('d-m-Y', strtotime($data['tgl_lahir'])) ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">Umur</th>
                                                    <td class="px-3"><?= $data['umur'] ?? '-' ?> tahun</td>
                                                </tr>

                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="card-title mb-0" style="font-size: 0.9rem;">Informasi Tambahan</h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-sm table-hover" style="font-size: 0.85rem;">
                                                <tr>
                                                    <th width="140" class="text-muted px-3">Alamat</th>
                                                    <td class="px-3"><?= $data['alamat'] ?? '-' ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">No. Telepon</th>
                                                    <td class="px-3"><?= $data['no_tlp'] ?? '-' ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">Pekerjaan</th>
                                                    <td class="px-3"><?= $data['pekerjaan'] ?? '-' ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">Status Nikah</th>
                                                    <td class="px-3"><?= $data['stts_nikah'] ?? '-' ?></td>
                                                </tr>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Status Obstetri -->
                        <div class="tab-pane fade" id="skrining" role="tabpanel">
                            <div class="mb-3 d-flex justify-content-between">
                                <h6 class="font-weight-bold">Status Obstetri</h6>
                                <a href="index.php?module=rekam_medis&action=tambah_status_obstetri&no_rkm_medis=<?= $data['no_rkm_medis'] ?>&source=form_penilaian_medis_ralan_kandungan" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Data
                                </a>
                            </div>
                            <div id="statusObstetriContent">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>G-P-A</th>
                                                <th>HPHT</th>
                                                <th>TP</th>
                                                <th>TP Penyesuaian</th>
                                                <th>Faktor Risiko</th>
                                                <th width="100">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="statusObstetriTableBody">

                                        </tbody>
                                    </table>
                                </div>


                            </div>
                        </div>

                        <!-- Tab Riwayat Kehamilan -->
                        <div class="tab-pane fade" id="riwayat-kehamilan" role="tabpanel">
                            <div class="mb-3 d-flex justify-content-between">
                                <h6 class="font-weight-bold">Riwayat Kehamilan</h6>
                                <a href="index.php?module=rekam_medis&action=tambah_riwayat_kehamilan&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>&source=form_edit_pemeriksaan" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Data
                                </a>
                            </div>
                            <div id="riwayatKehamilanContent">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Status</th>
                                                <th>Jenis</th>
                                                <th>Tempat</th>
                                                <th>Penolong</th>
                                                <th>Tahun</th>
                                                <th>Jenis Kelamin</th>
                                                <th>BB</th>
                                                <th>Kondisi</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="riwayatKehamilanTableBody">
                                            <!-- Data riwayat kehamilan akan dimuat melalui AJAX -->
                                            <tr>
                                                <td colspan="10" class="text-center">Memuat data riwayat kehamilan...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Status Ginekologi -->
                        <div class="tab-pane fade" id="status-ginekologi" role="tabpanel">
                            <div class="mb-3 d-flex justify-content-between">
                                <h6 class="font-weight-bold">Status Ginekologi</h6>
                                <a href="index.php?module=rekam_medis&action=tambah_status_ginekologi&no_rkm_medis=<?= $data['no_rkm_medis'] ?>&source=form_penilaian_medis_ralan_kandungan&no_rawat=<?= $data['no_rawat'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Data
                                </a>
                            </div>
                            <div id="statusGinekologiContent">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Parturien</th>
                                                <th>Abortus</th>
                                                <th>Hari Pertama Haid Terakhir</th>
                                                <th>Kontrasepsi Terakhir</th>
                                                <th>Lama Menikah (Tahun)</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="statusGinekologiTableBody">
                                            <!-- Data status ginekologi akan dimuat melalui AJAX -->
                                            <tr>
                                                <td colspan="7" class="text-center">Memuat data status ginekologi...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                                    <label>Riwayat Sekarang</label>
                                                    <textarea name="rps" class="form-control form-control-sm" rows="5"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-2">
                                                    <label>Riwayat Penyakit Dahulu</label>
                                                    <textarea name="rpd" class="form-control form-control-sm" rows="4"></textarea>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Alergi</label>
                                                    <textarea name="alergi" class="form-control form-control-sm" rows="2"></textarea>
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
                                                <div class="mb-3">
                                                    <label>BB (kg)</label>
                                                    <input type="number" name="bb" class="form-control form-control-sm" value="<?= htmlspecialchars($bb_terakhir) ?>" step="0.01" min="0" max="500" placeholder="Gunakan titik untuk desimal" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                                                    <small class="text-muted">Gunakan titik (.) untuk desimal, bukan koma</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label>TB (cm)</label>
                                                    <input type="number" name="tb" class="form-control form-control-sm" value="<?= htmlspecialchars($tb_terakhir) ?>" step="0.1" min="0" max="300" placeholder="Gunakan titik untuk desimal" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
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
                                                    <textarea name="diagnosis" id="diagnosis" class="form-control" rows="4"><?= htmlspecialchars($diagnosis_terakhir) ?></textarea>
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
                            $conn = new mysqli('auth-db1151.hstgr.io', 'u609399718_adminpraktek', 'Obgin@12345', 'u609399718_praktekobgin');

                            if ($conn->connect_error) {
                                die("Koneksi gagal: " . $conn->connect_error);
                            }

                            // Query untuk mendapatkan semua template
                            $sql = "SELECT * FROM template_usg WHERE status = 'active' ORDER BY kategori_usg ASC, nama_template_usg ASC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $no = 1;
                                while ($row = $result->fetch_assoc()) {
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
                            $no_rkm_medis = $data['no_rkm_medis'];

                            // Koneksi ke database
                            $conn = new mysqli('auth-db1151.hstgr.io', 'u609399718_adminpraktek', 'Obgin@12345', 'u609399718_praktekobgin');

                            if ($conn->connect_error) {
                                die("Koneksi gagal: " . $conn->connect_error);
                            }

                            // Query untuk mendapatkan riwayat diagnosis
                            $sql = "SELECT 
                                    pmrk.tanggal, 
                                    pmrk.diagnosis 
                                FROM penilaian_medis_ralan_kandungan pmrk
                                JOIN reg_periksa rp ON pmrk.no_rawat = rp.no_rawat
                                WHERE rp.no_rkm_medis = ? 
                                AND pmrk.diagnosis IS NOT NULL 
                                AND pmrk.diagnosis != ''
                                ORDER BY pmrk.tanggal DESC";

                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("s", $no_rkm_medis);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                $no = 1;
                                while ($row = $result->fetch_assoc()) {
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
                            $sql = "SELECT id_edukasi, judul, isi_edukasi, kategori, tag, link_gambar, status_aktif FROM edukasi WHERE status_aktif = 1 ORDER BY kategori ASC, judul ASC";
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
                                    echo "<td>
                                        <button type='button' class='btn btn-sm btn-primary mb-1 w-100' onclick='gunakanTemplateEdukasi(" . json_encode($row['isi_edukasi']) . ")'>
                                            <i class='fas fa-check'></i> Gunakan
                                        </button>";
                                    // Debugging to check if link_gambar exists and has values
                                    echo "<button type='button' class='btn btn-sm btn-info w-100 mt-1' onclick='lihatGambarEdukasi(\"" . (isset($row['link_gambar']) ? htmlspecialchars($row['link_gambar']) : "https://via.placeholder.com/400x300?text=No+Image") . "\", \"" . htmlspecialchars($row['judul']) . "\")'>
                                        <i class='fas fa-image'></i> Lihat Gambar
                                    </button>";
                                    echo "</td>";
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
    // Tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Identitas tab
        const identitasTab = document.getElementById('identitas-tab');
        const identitasContent = document.getElementById('identitas');

        // Status Obstetri tab
        const skriningTab = document.getElementById('skrining-tab');
        const skriningContent = document.getElementById('skrining');

        // Riwayat Kehamilan tab
        const riwayatKehamilanTab = document.getElementById('riwayat-kehamilan-tab');
        const riwayatKehamilanContent = document.getElementById('riwayat-kehamilan');

        // Status Ginekologi tab
        const statusGinekologiTab = document.getElementById('status-ginekologi-tab');
        const statusGinekologiContent = document.getElementById('status-ginekologi');

        // Setup default state
        identitasContent.style.display = 'block';
        identitasContent.classList.add('show');
        skriningContent.style.display = 'none';
        riwayatKehamilanContent.style.display = 'none';
        statusGinekologiContent.style.display = 'none';

        // Pastikan tab akif memiliki class active
        identitasTab.classList.add('active');
        identitasTab.classList.remove('collapsed');
        skriningTab.classList.add('collapsed');
        skriningTab.classList.remove('active');
        riwayatKehamilanTab.classList.add('collapsed');
        riwayatKehamilanTab.classList.remove('active');
        statusGinekologiTab.classList.add('collapsed');
        statusGinekologiTab.classList.remove('active');

        // Log untuk debugging
        debugStatusObstetri('Tab system initialized');
        debugStatusObstetri('identitasTab: ' + (identitasTab ? 'found' : 'not found'));
        debugStatusObstetri('identitasContent: ' + (identitasContent ? 'found' : 'not found'));
        debugStatusObstetri('skriningTab: ' + (skriningTab ? 'found' : 'not found'));
        debugStatusObstetri('skriningContent: ' + (skriningContent ? 'found' : 'not found'));

        // Initialize icon state
        identitasTab.querySelector('i').style.transform = 'rotate(0deg)';
        skriningTab.querySelector('i').style.transform = 'rotate(-90deg)';
        riwayatKehamilanTab.querySelector('i').style.transform = 'rotate(-90deg)';
        statusGinekologiTab.querySelector('i').style.transform = 'rotate(-90deg)';

        // Fungsi untuk menutup semua tab kecuali yang aktif
        function closeAllTabsExcept(activeTabContent) {
            const allTabContents = [identitasContent, skriningContent, riwayatKehamilanContent, statusGinekologiContent];
            const allTabs = [identitasTab, skriningTab, riwayatKehamilanTab, statusGinekologiTab];

            allTabContents.forEach(content => {
                if (content !== activeTabContent) {
                    content.style.display = 'none';
                    content.classList.remove('show');
                }
            });

            allTabs.forEach(tab => {
                if (tab.getAttribute('href') !== '#' + activeTabContent.id) {
                    tab.classList.add('collapsed');
                    tab.classList.remove('active');
                    tab.querySelector('i').style.transform = 'rotate(-90deg)';
                }
            });
        }

        // Add click handlers
        identitasTab.addEventListener('click', function(e) {
            e.preventDefault();
            debugStatusObstetri('Identitas tab clicked');

            if (identitasContent.style.display === 'block') {
                identitasContent.style.display = 'none';
                identitasContent.classList.remove('show');
                this.classList.add('collapsed');
                this.classList.remove('active');
                this.querySelector('i').style.transform = 'rotate(-90deg)';
            } else {
                identitasContent.style.display = 'block';
                identitasContent.classList.add('show');
                closeAllTabsExcept(identitasContent);

                this.classList.remove('collapsed');
                this.classList.add('active');
                this.querySelector('i').style.transform = 'rotate(0deg)';
            }
        });

        skriningTab.addEventListener('click', function(e) {
            e.preventDefault();
            debugStatusObstetri('Status Obstetri tab clicked');

            if (skriningContent.style.display === 'block') {
                skriningContent.style.display = 'none';
                skriningContent.classList.remove('show');
                this.classList.add('collapsed');
                this.classList.remove('active');
                this.querySelector('i').style.transform = 'rotate(-90deg)';
            } else {
                skriningContent.style.display = 'block';
                skriningContent.classList.add('show');
                closeAllTabsExcept(skriningContent);

                this.classList.remove('collapsed');
                this.classList.add('active');
                this.querySelector('i').style.transform = 'rotate(0deg)';

                // Penting: Panggil refresh data ketika tab dibuka
                refreshStatusObstetriData();
                debugStatusObstetri('Calling refreshStatusObstetriData when tab is opened');
            }
        });

        // Handler untuk tab Riwayat Kehamilan
        riwayatKehamilanTab.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Riwayat Kehamilan tab clicked');

            if (riwayatKehamilanContent.style.display === 'block') {
                riwayatKehamilanContent.style.display = 'none';
                riwayatKehamilanContent.classList.remove('show');
                this.classList.add('collapsed');
                this.classList.remove('active');
                this.querySelector('i').style.transform = 'rotate(-90deg)';
            } else {
                riwayatKehamilanContent.style.display = 'block';
                riwayatKehamilanContent.classList.add('show');
                closeAllTabsExcept(riwayatKehamilanContent);

                this.classList.remove('collapsed');
                this.classList.add('active');
                this.querySelector('i').style.transform = 'rotate(0deg)';

                // Muat data riwayat kehamilan saat tab dibuka
                refreshRiwayatKehamilanData();
                console.log('Calling refreshRiwayatKehamilanData when tab is opened');
            }
        });

        // Handler untuk tab Status Ginekologi
        statusGinekologiTab.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Status Ginekologi tab clicked');

            if (statusGinekologiContent.style.display === 'block') {
                statusGinekologiContent.style.display = 'none';
                statusGinekologiContent.classList.remove('show');
                this.classList.add('collapsed');
                this.classList.remove('active');
                this.querySelector('i').style.transform = 'rotate(-90deg)';
            } else {
                statusGinekologiContent.style.display = 'block';
                statusGinekologiContent.classList.add('show');
                closeAllTabsExcept(statusGinekologiContent);

                this.classList.remove('collapsed');
                this.classList.add('active');
                this.querySelector('i').style.transform = 'rotate(0deg)';

                // Muat data status ginekologi saat tab dibuka
                refreshStatusGinekologiData();
                console.log('Calling refreshStatusGinekologiData when tab is opened');
            }
        });
    });

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

            var hasVisibleRows = false; // Flag untuk mengecek apakah ada baris yang terlihat

            rows.forEach(function(row) {
                var rowJudul = row.cells[1].textContent.toLowerCase();
                var rowIsi = row.cells[2].textContent.toLowerCase();
                var rowTags = row.cells[4].textContent.toLowerCase();

                var matchesKategori = kategori === '' || row.getAttribute('data-kategori') === kategori;
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

    // Fungsi untuk debug tampilan data
    function debugStatusObstetri(message) {
        console.log('[DEBUG] ' + message);
        // Komentar atau hapus bagian penambahan pesan ke alert-info
        // const debugInfoDiv = document.querySelector('.alert-info');
        // if (debugInfoDiv) {
        //     const debugMsg = document.createElement('div');
        //     debugMsg.className = 'mt-1 text-danger';
        //     debugMsg.innerHTML = '<strong>[DEBUG]</strong> ' + message;
        //     debugInfoDiv.appendChild(debugMsg);
        // }
    }

    // Fungsi untuk refresh data status obstetri melalui AJAX
    function refreshStatusObstetriData() {
        const noRkmMedis = '<?= $data['no_rkm_medis'] ?>';
        const statusObstetriContent = document.getElementById('statusObstetriContent');
        const refreshButton = document.getElementById('refreshStatusObstetri');

        debugStatusObstetri('Refreshing Status Obstetri data for: ' + noRkmMedis);

        // Tampilkan loading state
        if (refreshButton) {
            refreshButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            refreshButton.disabled = true;
        }

        // Buat element untuk loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        loadingOverlay.style.position = 'absolute';
        loadingOverlay.style.top = '0';
        loadingOverlay.style.left = '0';
        loadingOverlay.style.width = '100%';
        loadingOverlay.style.height = '100%';
        loadingOverlay.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
        loadingOverlay.style.display = 'flex';
        loadingOverlay.style.justifyContent = 'center';
        loadingOverlay.style.alignItems = 'center';
        loadingOverlay.style.zIndex = '1000';

        // Tambahkan loading overlay ke content
        statusObstetriContent.style.position = 'relative';
        statusObstetriContent.appendChild(loadingOverlay);

        debugStatusObstetri('Loading status ditampilkan');

        // Log URL yang akan diakses
        const ajaxUrl = 'index.php?module=rekam_medis&action=get_status_obstetri_ajax&no_rkm_medis=' + encodeURIComponent(noRkmMedis);
        debugStatusObstetri('AJAX URL: ' + ajaxUrl);

        // Buat objek AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open('GET', ajaxUrl, true);

        xhr.onload = function() {
            debugStatusObstetri('AJAX onload triggered with status: ' + this.status);
            if (this.status === 200) {
                try {
                    // Tampilkan respons mentah untuk debugging
                    const rawResponse = this.responseText;
                    debugStatusObstetri('Response received (RAW):');

                    // Coba deteksi jika ini adalah HTML bukan JSON
                    if (rawResponse.trim().startsWith('<')) {
                        debugStatusObstetri('ERROR: Respons berisi HTML, bukan JSON. Endpoint mungkin tidak benar.');

                        // Tampilkan pesan kesalahan tentang endpoint
                        const errorAlert = document.createElement('div');
                        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                        errorAlert.innerHTML = `
                            <strong>Error!</strong> Endpoint AJAX mengembalikan HTML, bukan JSON. Ini mungkin karena:
                            <ul>
                                <li>File get_status_obstetri_ajax.php tidak ditemukan atau bermasalah</li>
                                <li>Session habis dan halaman dialihkan ke login</li>
                                <li>Ada error PHP pada endpoint</li>
                            </ul>
                            <p>Silakan periksa endpoint AJAX: <code>index.php?module=rekam_medis&action=get_status_obstetri_ajax</code></p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;

                        statusObstetriContent.insertBefore(errorAlert, statusObstetriContent.firstChild);
                        throw new Error('Respons berisi HTML, bukan JSON');
                    }

                    // Kurangi output debug - hanya log ke console, jangan tampilkan raw response lengkap
                    debugStatusObstetri('Response received and being processed');
                    const response = JSON.parse(rawResponse);
                    debugStatusObstetri('JSON parsed successfully, status: ' + response.status);

                    if (response.status === 'success') {
                        // Update tabel dengan data baru
                        const tableBody = document.getElementById('statusObstetriTableBody');
                        if (tableBody) {
                            debugStatusObstetri('Data count: ' + response.data.length);
                            if (response.data.length > 0) {
                                let tableHtml = '';

                                response.data.forEach(function(so) {
                                    const formattedDate = new Date(so.updated_at).toLocaleDateString('id-ID', {
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric'
                                    });

                                    const hphtDate = so.tanggal_hpht ? new Date(so.tanggal_hpht).toLocaleDateString('id-ID', {
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric'
                                    }) : '-';

                                    const tpDate = so.tanggal_tp ? new Date(so.tanggal_tp).toLocaleDateString('id-ID', {
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric'
                                    }) : '-';

                                    const tpPenyesuaianDate = so.tanggal_tp_penyesuaian ? new Date(so.tanggal_tp_penyesuaian).toLocaleDateString('id-ID', {
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric'
                                    }) : '-';

                                    // Bangun faktor risiko
                                    let faktorRisiko = [];
                                    if (so.faktor_risiko_umum) {
                                        faktorRisiko.push('Umum: ' + so.faktor_risiko_umum.replace(/,/g, ', '));
                                    }
                                    if (so.faktor_risiko_obstetri) {
                                        faktorRisiko.push('Obstetri: ' + so.faktor_risiko_obstetri.replace(/,/g, ', '));
                                    }
                                    if (so.faktor_risiko_preeklampsia) {
                                        faktorRisiko.push('Preeklampsia: ' + so.faktor_risiko_preeklampsia.replace(/,/g, ', '));
                                    }

                                    const faktorRisikoHtml = faktorRisiko.length > 0 ? faktorRisiko.join('<br>') : '-';

                                    tableHtml += `
                                        <tr>
                                            <td>${formattedDate}</td>
                                            <td>${so.gravida}-${so.paritas}-${so.abortus}</td>
                                            <td>${hphtDate}</td>
                                            <td>${tpDate}</td>
                                            <td>${tpPenyesuaianDate}</td>
                                            <td>${faktorRisikoHtml}</td>
                                            <td>
                                                <a href="index.php?module=rekam_medis&action=edit_status_obstetri&id=${so.id_status_obstetri}&source=<?= $_SESSION['source_page'] ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="index.php?module=rekam_medis&action=hapus_status_obstetri&id=${so.id_status_obstetri}&source=<?= $_SESSION['source_page'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    `;
                                });

                                tableBody.innerHTML = tableHtml;
                            } else {
                                tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada data status obstetri</td></tr>';
                            }
                            debugStatusObstetri('Table body updated with new data');
                        } else {
                            debugStatusObstetri('ERROR: Table body element not found');
                        }
                    } else {
                        debugStatusObstetri('Error response: ' + response.message);
                        alert('Error: ' + response.message);
                    }
                } catch (error) {
                    debugStatusObstetri('Error parsing JSON: ' + error.message);
                    console.error('Error parsing JSON:', error);
                }
            } else {
                debugStatusObstetri('HTTP error: ' + this.status);
                console.error('HTTP Error:', this.status);
            }

            // Reset loading state
            if (refreshButton) {
                refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Data';
                refreshButton.disabled = false;
            }

            // Hapus loading overlay
            if (statusObstetriContent.contains(loadingOverlay)) {
                statusObstetriContent.removeChild(loadingOverlay);
            }
        };

        xhr.onerror = function() {
            debugStatusObstetri('Request failed');
            console.error('Request Failed');

            // Reset loading state
            if (refreshButton) {
                refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Data';
                refreshButton.disabled = false;
            }

            // Hapus loading overlay
            if (statusObstetriContent.contains(loadingOverlay)) {
                statusObstetriContent.removeChild(loadingOverlay);
            }
        };

        xhr.send();
    }

    // Fungsi untuk refresh data Riwayat Kehamilan
    function refreshRiwayatKehamilanData() {
        const noRkmMedis = '<?= $data['no_rkm_medis'] ?>';
        const riwayatKehamilanContent = document.getElementById('riwayatKehamilanContent');

        // Buat element untuk loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';

        // Tambahkan loading overlay ke content
        riwayatKehamilanContent.style.position = 'relative';
        riwayatKehamilanContent.appendChild(loadingOverlay);

        console.log('Refreshing Riwayat Kehamilan data for: ' + noRkmMedis);

        // AJAX request untuk riwayat kehamilan
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'index.php?module=rekam_medis&action=get_riwayat_kehamilan_ajax&no_rkm_medis=' + encodeURIComponent(noRkmMedis), true);

        xhr.onload = function() {
            if (this.status === 200) {
                try {
                    const response = JSON.parse(this.responseText);
                    console.log('Riwayat kehamilan data received:', response);

                    const tableBody = document.getElementById('riwayatKehamilanTableBody');
                    if (tableBody) {
                        if (response.status === 'success' && response.data && response.data.length > 0) {
                            let tableHtml = '';

                            response.data.forEach(function(rk) {
                                tableHtml += `
                                    <tr>
                                        <td>${rk.no_urut_kehamilan}</td>
                                        <td>${rk.status_kehamilan || '-'}</td>
                                        <td>${rk.jenis_persalinan || '-'}</td>
                                        <td>${rk.tempat_persalinan || '-'}</td>
                                        <td>${rk.penolong_persalinan || '-'}</td>
                                        <td>${rk.tahun_persalinan || '-'}</td>
                                        <td>${rk.jenis_kelamin_anak || '-'}</td>
                                        <td>${rk.berat_badan_lahir || '-'}</td>
                                        <td>${rk.kondisi_lahir || '-'}</td>
                                        <td>
                                            <a href="index.php?module=rekam_medis&action=edit_riwayat_kehamilan&id=${rk.id_riwayat_kehamilan}&source=<?= $_SESSION['source_page'] ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?module=rekam_medis&action=hapus_riwayat_kehamilan&id=${rk.id_riwayat_kehamilan}&source=<?= $_SESSION['source_page'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                `;
                            });

                            tableBody.innerHTML = tableHtml;
                        } else {
                            tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Tidak ada data riwayat kehamilan</td></tr>';
                        }
                    }
                } catch (error) {
                    console.error('Error parsing JSON:', error);
                    document.getElementById('riwayatKehamilanTableBody').innerHTML =
                        '<tr><td colspan="10" class="text-center text-danger">Error: Gagal memuat data riwayat kehamilan</td></tr>';
                }
            } else {
                console.error('HTTP Error:', this.status);
                document.getElementById('riwayatKehamilanTableBody').innerHTML =
                    '<tr><td colspan="10" class="text-center text-danger">Error: Gagal memuat data riwayat kehamilan</td></tr>';
            }

            // Hapus loading overlay
            if (riwayatKehamilanContent.contains(loadingOverlay)) {
                riwayatKehamilanContent.removeChild(loadingOverlay);
            }
        };

        xhr.onerror = function() {
            console.error('Request Failed');
            document.getElementById('riwayatKehamilanTableBody').innerHTML =
                '<tr><td colspan="10" class="text-center text-danger">Error: Gagal terhubung ke server</td></tr>';

            // Hapus loading overlay
            if (riwayatKehamilanContent.contains(loadingOverlay)) {
                riwayatKehamilanContent.removeChild(loadingOverlay);
            }
        };

        xhr.send();
    }

    // Fungsi untuk refresh data Status Ginekologi
    function refreshStatusGinekologiData() {
        const noRkmMedis = '<?= $data['no_rkm_medis'] ?>';
        const statusGinekologiContent = document.getElementById('statusGinekologiContent');

        // Buat element untuk loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';

        // Tambahkan loading overlay ke content
        statusGinekologiContent.style.position = 'relative';
        statusGinekologiContent.appendChild(loadingOverlay);

        console.log('Refreshing Status Ginekologi data for: ' + noRkmMedis);

        // AJAX request untuk status ginekologi
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'index.php?module=rekam_medis&action=get_status_ginekologi_ajax&no_rkm_medis=' + encodeURIComponent(noRkmMedis), true);

        xhr.onload = function() {
            if (this.status === 200) {
                try {
                    const response = JSON.parse(this.responseText);
                    console.log('Status ginekologi data received:', response);

                    const tableBody = document.getElementById('statusGinekologiTableBody');
                    if (tableBody) {
                        if (response.status === 'success' && response.data && response.data.length > 0) {
                            let tableHtml = '';

                            response.data.forEach(function(sg) {
                                const tanggalCreated = new Date(sg.created_at).toLocaleDateString('id-ID', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric'
                                });

                                const hphtDate = sg.Hari_pertama_haid_terakhir ? new Date(sg.Hari_pertama_haid_terakhir).toLocaleDateString('id-ID', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric'
                                }) : '-';

                                tableHtml += `
                                    <tr>
                                        <td>${tanggalCreated}</td>
                                        <td>${sg.Parturien || '-'}</td>
                                        <td>${sg.Abortus || '-'}</td>
                                        <td>${hphtDate}</td>
                                        <td>${sg.Kontrasepsi_terakhir || '-'}</td>
                                        <td>${sg.lama_menikah_th || '-'}</td>
                                        <td>
                                            <a href="index.php?module=rekam_medis&action=edit_status_ginekologi&id=${sg.id_status_ginekologi}&source=<?= $_SESSION['source_page'] ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?module=rekam_medis&action=hapus_status_ginekologi&id=${sg.id_status_ginekologi}&source=<?= $_SESSION['source_page'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                `;
                            });

                            tableBody.innerHTML = tableHtml;
                        } else {
                            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada data status ginekologi</td></tr>';
                        }
                    }
                } catch (error) {
                    console.error('Error parsing JSON:', error);
                    document.getElementById('statusGinekologiTableBody').innerHTML =
                        '<tr><td colspan="7" class="text-center text-danger">Error: Gagal memuat data status ginekologi</td></tr>';
                }
            } else {
                console.error('HTTP Error:', this.status);
                document.getElementById('statusGinekologiTableBody').innerHTML =
                    '<tr><td colspan="7" class="text-center text-danger">Error: Gagal memuat data status ginekologi</td></tr>';
            }

            // Hapus loading overlay
            if (statusGinekologiContent.contains(loadingOverlay)) {
                statusGinekologiContent.removeChild(loadingOverlay);
            }
        };

        xhr.onerror = function() {
            console.error('Request Failed');
            document.getElementById('statusGinekologiTableBody').innerHTML =
                '<tr><td colspan="7" class="text-center text-danger">Error: Gagal terhubung ke server</td></tr>';

            // Hapus loading overlay
            if (statusGinekologiContent.contains(loadingOverlay)) {
                statusGinekologiContent.removeChild(loadingOverlay);
            }
        };

        xhr.send();
    }

    // Fungsi untuk melihat gambar edukasi
    function lihatGambarEdukasi(url, judul) {
        // Set judul modal
        document.getElementById('gambarEdukasiModalLabel').textContent = 'Gambar: ' + judul;

        // Log untuk debugging
        console.log('Original image URL:', url);

        // Set base path untuk gambar
        const basePath = BASE_URL ? BASE_URL + '/uploads/edukasi/' : 'https://srv1151-files.hstgr.io/37b1269c3c524999/files/public_html/uploads/edukasi/';

        // Periksa dan format URL gambar
        if (url && url.trim() !== '') {
            // Jika URL tidak dimulai dengan http/https dan bukan placeholder
            if (!url.startsWith('http') && !url.startsWith('https://') && url !== "https://via.placeholder.com/400x300?text=No+Image") {
                url = basePath + url;
            }
        } else {
            // Jika URL kosong, gunakan placeholder
            url = 'https://via.placeholder.com/400x300?text=Gambar+Tidak+Tersedia';
        }

        console.log('Final image URL:', url);

        // Set gambar ke dalam modal
        const imgHtml = `
            <div class="text-center">
                <img src="${url}" 
                     class="img-fluid" 
                     alt="Gambar Edukasi" 
                     onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300?text=Gambar+Tidak+Ditemukan'; console.log('Failed to load image: ${url}');">
            </div>`;

        document.getElementById('gambarEdukasiContent').innerHTML = imgHtml;

        // Tampilkan modal
        var modal = new bootstrap.Modal(document.getElementById('gambarEdukasiModal'));
        modal.show();
    }

    // Memuat Status Obstetri Pertama Kali
    document.addEventListener('DOMContentLoaded', () => {
        debugStatusObstetri('DOMContentLoaded fired');

        // --- Pendekatan #1: Event click collapse/tab ---
        const skriningTab = document.getElementById('skrining-tab');

        if (skriningTab) {
            debugStatusObstetri('Tab Status Obstetri ditemukan, menambahkan click listener');
            skriningTab.addEventListener('click', function(e) {
                debugStatusObstetri('Tab Status Obstetri diklik');

                // Periksa apakah tab akan terbuka - collapsed class akan dihapus
                // Karena ini kompleks dengan timing Bootstrap, kita akan coba pendekatan lain

                // Set timer untuk loading data setelah tab dibuka (200ms delay)
                setTimeout(() => {
                    const skriningPane = document.getElementById('skrining');

                    if (skriningPane && skriningPane.style.display === 'block') {
                        debugStatusObstetri('Tab Status Obstetri terdeteksi terbuka, memanggil refresh data');
                        refreshStatusObstetriData();
                    } else {
                        debugStatusObstetri('Tab Status Obstetri terdeteksi belum terbuka');
                    }
                }, 200);
            });
        } else {
            debugStatusObstetri('ERROR: Tab Status Obstetri tidak ditemukan');
        }

        // --- Tambahkan event listener untuk tab Riwayat Kehamilan ---
        const riwayatKehamilanTab = document.getElementById('riwayat-kehamilan-tab');

        if (riwayatKehamilanTab) {
            console.log('Tab Riwayat Kehamilan ditemukan, menambahkan click listener');
            riwayatKehamilanTab.addEventListener('click', function(e) {
                console.log('Tab Riwayat Kehamilan diklik');

                // Set timer untuk loading data setelah tab dibuka
                setTimeout(() => {
                    const riwayatKehamilanPane = document.getElementById('riwayat-kehamilan');

                    if (riwayatKehamilanPane && riwayatKehamilanPane.style.display === 'block') {
                        console.log('Tab Riwayat Kehamilan terdeteksi terbuka, memanggil refresh data');
                        refreshRiwayatKehamilanData();
                    } else {
                        console.log('Tab Riwayat Kehamilan terdeteksi belum terbuka');
                    }
                }, 200);
            });
        } else {
            console.error('ERROR: Tab Riwayat Kehamilan tidak ditemukan');
        }

        // --- Tambahkan event listener untuk tab Status Ginekologi ---
        const statusGinekologiTab = document.getElementById('status-ginekologi-tab');

        if (statusGinekologiTab) {
            console.log('Tab Status Ginekologi ditemukan, menambahkan click listener');
            statusGinekologiTab.addEventListener('click', function(e) {
                console.log('Tab Status Ginekologi diklik');

                // Set timer untuk loading data setelah tab dibuka
                setTimeout(() => {
                    const statusGinekologiPane = document.getElementById('status-ginekologi');

                    if (statusGinekologiPane && statusGinekologiPane.style.display === 'block') {
                        console.log('Tab Status Ginekologi terdeteksi terbuka, memanggil refresh data');
                        refreshStatusGinekologiData();
                    } else {
                        console.log('Tab Status Ginekologi terdeteksi belum terbuka');
                    }
                }, 200);
            });
        } else {
            console.error('ERROR: Tab Status Ginekologi tidak ditemukan');
        }

        // --- Pendekatan #2: Panggil segera jika tab sudah aktif saat load ---
        const skriningPane = document.getElementById('skrining');
        if (skriningPane && window.getComputedStyle(skriningPane).display === 'block') {
            debugStatusObstetri('Tab Status Obstetri terdeteksi sudah aktif saat load, memanggil refresh data');
            // Sedikit delay agar DOM siap
            setTimeout(refreshStatusObstetriData, 300);
        }

        // Periksa juga tab riwayat kehamilan
        const riwayatKehamilanPane = document.getElementById('riwayat-kehamilan');
        if (riwayatKehamilanPane && window.getComputedStyle(riwayatKehamilanPane).display === 'block') {
            console.log('Tab Riwayat Kehamilan terdeteksi sudah aktif saat load, memanggil refresh data');
            setTimeout(refreshRiwayatKehamilanData, 300);
        }

        // Periksa juga tab status ginekologi
        const statusGinekologiPane = document.getElementById('status-ginekologi');
        if (statusGinekologiPane && window.getComputedStyle(statusGinekologiPane).display === 'block') {
            console.log('Tab Status Ginekologi terdeteksi sudah aktif saat load, memanggil refresh data');
            setTimeout(refreshStatusGinekologiData, 300);
        }

        // --- Pendekatan #3: Backup - coba load saat tombol di tab Status Obstetri pertama kali terlihat ---
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    debugStatusObstetri('Tab content Status Obstetri terlihat, memanggil refresh data (via IntersectionObserver)');
                    refreshStatusObstetriData();
                    observer.disconnect(); // Hentikan observer setelah satu kali
                }
            });
        });

        if (skriningPane) {
            observer.observe(skriningPane);
        }

        // Buat observer untuk tab riwayat kehamilan
        const observerRiwayatKehamilan = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    console.log('Tab content Riwayat Kehamilan terlihat, memanggil refresh data (via IntersectionObserver)');
                    refreshRiwayatKehamilanData();
                    observerRiwayatKehamilan.disconnect();
                }
            });
        });

        if (riwayatKehamilanPane) {
            observerRiwayatKehamilan.observe(riwayatKehamilanPane);
        }

        // Buat observer untuk tab status ginekologi
        const observerStatusGinekologi = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    console.log('Tab content Status Ginekologi terlihat, memanggil refresh data (via IntersectionObserver)');
                    refreshStatusGinekologiData();
                    observerStatusGinekologi.disconnect();
                }
            });
        });

        if (statusGinekologiPane) {
            observerStatusGinekologi.observe(statusGinekologiPane);
        }
    });
</script>