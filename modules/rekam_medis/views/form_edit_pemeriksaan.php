<?php
// Pastikan tidak ada akses langsung ke file ini
if (!defined('BASE_PATH')) {
    // Definisikan BASE_PATH jika belum ada untuk kompatibilitas
    define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/antrian pasien');
    
    // Log untuk debugging
    error_log("FORM EDIT: BASE_PATH not defined, setting to: " . BASE_PATH);
}

// Enable error reporting untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Tambahkan log error untuk debug
error_log("Form Edit Pemeriksaan: Loading started at " . date('Y-m-d H:i:s'));

// Pastikan session sudah dimulai
if (!isset($_SESSION)) {
    session_start();
}

// Set default source_page jika belum ada
if (!isset($_SESSION['source_page'])) {
    $_SESSION['source_page'] = 'form_edit_pemeriksaan';
}

try {
    // Deteksi apakah ini remote host atau localhost
    $is_remote = !($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1');
    error_log("Form Edit Pemeriksaan: Running on " . ($_SERVER['HTTP_HOST'] ?? 'unknown host') . ", remote: " . ($is_remote ? 'yes' : 'no'));

    // Buat koneksi langsung ke database praktek obgin menggunakan mysqli
    error_log("Form Edit Pemeriksaan: Attempting database connection to auth-db1151.hstgr.io, u609399718_praktekobgin");
    $conn = new mysqli('auth-db1151.hstgr.io', 'u609399718_adminpraktek', 'Obgin@12345', 'u609399718_praktekobgin');

    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }

    error_log("Form Edit Pemeriksaan: Database connection successful");
} catch (Exception $e) {
    error_log("Form Edit Pemeriksaan ERROR: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Error koneksi database: " . $e->getMessage() . "</div>";
}

// Fungsi untuk mendapatkan koneksi database
function getConnection() {
    global $conn;
    return $conn;
}

// Cek apakah ada data pemeriksaan
if (!isset($pemeriksaan) || !$pemeriksaan) {
    $_SESSION['error'] = 'Data pemeriksaan tidak ditemukan';
    $redirect_url = isset($_SERVER['HTTP_HOST']) ? 
        ($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/antrian pasien/index.php?module=rekam_medis&action=data_pasien') :
        'index.php?module=rekam_medis&action=data_pasien';
    header('Location: ' . $redirect_url);
    exit;
}
?>

<script>
// Menonaktifkan Service Worker untuk halaman ini
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(function(registrations) {
        for(let registration of registrations) {
            registration.unregister();
            console.log('Service Worker dinonaktifkan untuk halaman Edit Pemeriksaan');
        }
    });
    
    // Menghindari caching
    if (window.caches) {
        caches.keys().then(function(cacheNames) {
            cacheNames.forEach(function(cacheName) {
                caches.delete(cacheName);
                console.log('Cache dihapus:', cacheName);
            });
        });
    }
}
</script>

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

    .tab-pane:not(.active) {
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

    .nav-tabs .nav-link.active {
        background-color: #fff;
        border-bottom-color: #fff;
    }

    .nav-tabs .nav-link i {
        margin-left: 5px;
        transition: transform 0.3s;
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

    /* Loading spinner style */
    .loading-overlay {
        position: fixed;
        /* Changed from absolute to fixed */
        top: 0;
        left: 0;
        width: 100vw;
        /* Changed from 100% to 100vw */
        height: 100vh;
        /* Changed from 100% to 100vh */
        background-color: rgba(255, 255, 255, 0.8);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        /* Increased z-index */
    }

    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-radius: 50%;
        border-top: 5px solid #3498db;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .modal-backdrop {
        opacity: 0.5;
        z-index: 1040;
    }

    .modal {
        z-index: 1050;
    }

    #globalLoadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading-content {
        text-align: center;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
    }
</style>

<div id="globalLoadingOverlay" class="loading-overlay">
    <div class="loading-spinner"></div>
</div>

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

            <!-- Tab Navigasi -->
            <ul class="nav nav-tabs mb-0" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="identitas-tab" data-bs-toggle="tab" href="#identitas" role="tab" aria-controls="identitas" aria-selected="true">
                        <i class="fas fa-user-circle mr-1"></i> Data Pasien <i class="fas fa-chevron-down ml-1"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="skrining-tab" data-bs-toggle="tab" href="#skrining" role="tab" aria-controls="skrining" aria-selected="false">
                        <i class="fas fa-clipboard-check mr-1"></i> Status Obstetri <i class="fas fa-chevron-down ml-1"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="riwayat-kehamilan-tab" data-bs-toggle="tab" href="#riwayat-kehamilan" role="tab" aria-controls="riwayat-kehamilan" aria-selected="false">
                        <i class="fas fa-baby mr-1"></i> Riwayat Kehamilan <i class="fas fa-chevron-down ml-1"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="status-ginekologi-tab" data-bs-toggle="tab" href="#status-ginekologi" role="tab" aria-controls="status-ginekologi" aria-selected="false">
                        <i class="fas fa-venus mr-1"></i> Status Ginekologi <i class="fas fa-chevron-down ml-1"></i>
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="myTabContent">
                <!-- Tab Data Pasien -->
                <div class="tab-pane fade show active" id="identitas" role="tabpanel" aria-labelledby="identitas-tab">
                    <div class="py-3">
                        <div class="row">
                            <!-- Kolom Kiri - Data Pasien -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0" style="font-size: 0.9rem;">Informasi Pasien</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-hover" style="font-size: 0.85rem;">
                                            <tr>
                                                <th width="140" class="text-muted px-3">No. Rekam Medis</th>
                                                <td class="px-3"><?= $pasien['no_rkm_medis'] ?></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted px-3">Nama Pasien</th>
                                                <td class="px-3"><?= $pasien['nm_pasien'] ?></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted px-3">Tanggal Lahir</th>
                                                <td class="px-3"><?= date('d-m-Y', strtotime($pasien['tgl_lahir'])) ?></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted px-3">Tanggal Pemeriksaan</th>
                                                <td class="px-3"><?= date('d-m-Y H:i', strtotime($pemeriksaan['tanggal'])) ?></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted px-3">No. Rawat</th>
                                                <td class="px-3"><?= $pemeriksaan['no_rawat'] ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan - Data Tambahan -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0" style="font-size: 0.9rem;">Informasi Tambahan</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-hover" style="font-size: 0.85rem;">
                                            <tr>
                                                <th width="140" class="text-muted px-3">Alamat</th>
                                                <td class="px-3"><?= $pasien['alamat'] ?? '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted px-3">No. Telepon</th>
                                                <td class="px-3"><?= $pasien['no_tlp'] ?? '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted px-3">Pekerjaan</th>
                                                <td class="px-3"><?= $pasien['pekerjaan'] ?? '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted px-3">Status Nikah</th>
                                                <td class="px-3"><?= $pasien['stts_nikah'] ?? '-' ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Status Obstetri -->
                <div class="tab-pane fade" id="skrining" role="tabpanel" aria-labelledby="skrining-tab">
                    <div class="mb-3 d-flex justify-content-between">
                        <h6 class="font-weight-bold">Status Obstetri</h6>
                        <a href="index.php?module=rekam_medis&action=tambah_status_obstetri&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>&source=form_edit_pemeriksaan" class="btn btn-primary btn-sm">
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
                                    <tr>
                                        <td colspan="7" class="text-center">Memuat data status obstetri...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab Riwayat Kehamilan -->
                <div class="tab-pane fade" id="riwayat-kehamilan" role="tabpanel" aria-labelledby="riwayat-kehamilan-tab">
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
                <div class="tab-pane fade" id="status-ginekologi" role="tabpanel" aria-labelledby="status-ginekologi-tab">
                    <div class="mb-3 d-flex justify-content-between">
                        <h6 class="font-weight-bold">Status Ginekologi</h6>
                        <a href="index.php?module=rekam_medis&action=tambah_status_ginekologi&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>&source=form_edit_pemeriksaan" class="btn btn-primary btn-sm">
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
                                            <label>Riwayat Sekarang</label>
                                            <textarea name="rps" class="form-control form-control-sm" rows="4"><?= isset($pemeriksaan['rps']) ? $pemeriksaan['rps'] : '' ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <label>Riwayat Penyakit Dahulu</label>
                                            <textarea name="rpd" class="form-control form-control-sm" rows="2"><?= isset($pemeriksaan['rpd']) ? $pemeriksaan['rpd'] : '' ?></textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label>Alergi</label>
                                            <textarea name="alergi" class="form-control form-control-sm" rows="2"><?= isset($pemeriksaan['alergi']) ? $pemeriksaan['alergi'] : '' ?></textarea>
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

                        <!-- Tombol Pilih Gambar Edukasi -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Gambar Edukasi</h6>
                            </div>
                            <div class="card-body">
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPilihGambarEdukasi">
                                    <i class="fas fa-images"></i> Pilih Gambar Edukasi
                                </button>
                            </div>
                        </div>

                        <!-- Gambar Edukasi Terpilih -->
                        <div class="card mb-3" id="cardGambarEdukasi" style="display: none;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Gambar Edukasi Terpilih</h6>
                                <button type="button" class="btn btn-sm btn-danger" onclick="hapusGambarTerpilih()">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                            <div class="card-body">
                                <input type="hidden" name="gambar_edukasi" id="gambarEdukasiInput">
                                <input type="hidden" name="judul_gambar_edukasi" id="judulGambarEdukasiInput">
                                <div class="text-center">
                                    <a href="#" onclick="bukaGambarDiTabBaru(); return false;" style="cursor: pointer;" title="Klik untuk membuka gambar di tab baru">
                                        <img id="gambarEdukasiTerpilih" src="" class="img-fluid" style="max-height: 300px;" alt="Gambar Edukasi">
                                    </a>
                                    <p class="mt-2" id="judulGambarEdukasiTerpilih"></p>
                                    <small class="text-muted">(Klik gambar untuk membuka di tab baru)</small>
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
                                            <textarea name="diagnosis" id="diagnosis" class="form-control" rows="4"><?= isset($pemeriksaan['diagnosis']) ? $pemeriksaan['diagnosis'] : '' ?></textarea>
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
                                            <textarea name="tata" id="tatalaksana" class="form-control" rows="4"><?= isset($pemeriksaan['tata']) ? $pemeriksaan['tata'] : '' ?></textarea>
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
                                            <textarea name="edukasi" id="edukasi" class="form-control" rows="5"><?= isset($pemeriksaan['edukasi']) ? $pemeriksaan['edukasi'] : '' ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border">
                                                <div class="card-body p-2">
                                                    <button type="button" class="btn btn-sm btn-info w-100" data-bs-toggle="modal" data-bs-target="#modalDaftarEdukasi">
                                                        <i class="fas fa-list"></i> Lihat Template
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card border mt-2">
                                                <div class="card-body p-2">
                                                    <a href="javascript:void(0)" onclick="printEdukasi()" class="btn btn-sm btn-success w-100">
                                                        <i class="fas fa-print"></i> Cetak Edukasi
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Resume</label>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <textarea name="resume" id="resume" class="form-control" rows="17"><?= isset($pemeriksaan['resume']) ? $pemeriksaan['resume'] : '' ?></textarea>
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
                            // Use PDO connection from config.php
                            $conn = getConnection(); // Get the PDO connection

                            // Query untuk mendapatkan semua template
                            $sql = "SELECT * FROM template_tatalaksana WHERE status = 'active' ORDER BY kategori_tx ASC, nama_template_tx ASC";
                            $stmt = $conn->query($sql);

                            if ($stmt && $stmt->rowCount() > 0) {
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
                            // Use PDO connection from config.php
                            $conn = getConnection(); // Get the PDO connection

                            // Query untuk mendapatkan semua template
                            $sql = "SELECT * FROM template_usg WHERE status = 'active' ORDER BY kategori_usg ASC, nama_template_usg ASC";
                            $stmt = $conn->query($sql);

                            if ($stmt && $stmt->rowCount() > 0) {
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
                            // Use PDO connection from config.php
                            $conn = getConnection(); // Get the PDO connection

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

                            if ($stmt && $stmt->rowCount() > 0) {
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
                        <input type="text" id="search_generik" class="form-control" placeholder="Cari...">
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-primary btn-sm" onclick="tambahkanObatTerpilih()">Tambahkan Obat Terpilih</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
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
                                <th width="15%">Nama Obat</th>
                                <th width="10%">Bentuk Sediaan</th>
                                <th width="10%">Dosis</th>
                                <th width="10%">Harga</th>
                                <th width="15%">Farmasi</th>
                                <th width="15%">Catatan</th>
                                <th width="10%">ED</th>
                                <th width="10%">Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Koneksi ke database
                            // Use PDO connection from config.php
                            $conn = getConnection(); // Get the PDO connection

                            // Query untuk mendapatkan semua data formularium, sorted by ED date (non-empty first), then by ED ascending, then by name
                            $sql = "SELECT * FROM formularium WHERE status_aktif = 1 ORDER BY (ed IS NULL OR ed = '') ASC, ed ASC, nama_obat ASC";
                            $stmt = $conn->query($sql);

                            if ($stmt && $stmt->rowCount() > 0) {
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $bentuk_dosis = $row['bentuk_sediaan'] . ' ' . $row['dosis'];
                                    echo "<tr class='obat-row' data-kategori='" . htmlspecialchars($row['kategori']) . "'>";
                                    echo "<td><input type='checkbox' class='form-check-input obat-checkbox' data-nama='" . htmlspecialchars($row['nama_obat']) . "' data-bentuk-sediaan='" . htmlspecialchars($row['bentuk_sediaan']) . "' data-dosis='" . htmlspecialchars($row['dosis']) . "' data-catatan='" . htmlspecialchars($row['catatan_obat']) . "'></td>";
                                    echo "<td>" . htmlspecialchars($row['nama_obat']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['bentuk_sediaan']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['dosis']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['harga']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['farmasi']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['catatan_obat']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['ed']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['kategori']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>Tidak ada data obat</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Modal footer dihapus karena tombol sudah dipindahkan ke atas -->

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
                            // Use PDO connection from config.php
                            $conn = getConnection(); // Get the PDO connection

                            // Query untuk mendapatkan semua template edukasi
                            $sql = "SELECT * FROM edukasi WHERE status_aktif = 1 ORDER BY kategori ASC, judul ASC";
                            $stmt = $conn->query($sql);

                            if ($stmt && $stmt->rowCount() > 0) {
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
                            // Use PDO connection from config.php
                            $conn = getConnection(); // Get the PDO connection

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

<!-- Modal Pilih Gambar Edukasi -->
<div class="modal fade" id="modalPilihGambarEdukasi" tabindex="-1" aria-labelledby="modalPilihGambarEdukasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPilihGambarEdukasiLabel">Pilih Gambar Edukasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filter Kategori dan Pencarian -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="filter_kategori_gambar" class="form-select me-2">
                            <option value="">Semua Kategori</option>
                            <option value="fetomaternal">Fetomaternal</option>
                            <option value="ginekologi umum">Ginekologi Umum</option>
                            <option value="onkogin">Onkogin</option>
                            <option value="fertilitas">Fertilitas</option>
                            <option value="uroginekologi">Uroginekologi</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="search_gambar" class="form-control" placeholder="Cari judul gambar...">
                    </div>
                </div>

                <!-- Grid Gambar -->
                <div class="row" id="gridGambarEdukasi">
                    <?php
                    // Koneksi ke database
                    // Use PDO connection from config.php
                            $conn = getConnection(); // Get the PDO connection

                    // Query untuk mendapatkan semua gambar edukasi
                    $sql = "SELECT * FROM edukasi WHERE status_aktif = 1 AND link_gambar IS NOT NULL ORDER BY kategori ASC, judul ASC";
                    $stmt = $conn->query($sql);

                    if ($stmt && $stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<div class="col-md-4 mb-3 gambar-item" data-kategori="' . htmlspecialchars($row['kategori']) . '" data-judul="' . htmlspecialchars($row['judul']) . '">';
                            echo '<div class="card h-100">';
                            echo '<img src="uploads/edukasi/' . htmlspecialchars($row['link_gambar']) . '" class="card-img-top" alt="' . htmlspecialchars($row['judul']) . '" style="height: 200px; object-fit: contain;">';
                            echo '<div class="card-body">';
                            echo '<h6 class="card-title">' . htmlspecialchars($row['judul']) . '</h6>';
                            echo '<p class="card-text small">' . htmlspecialchars($row['kategori']) . '</p>';
                            echo '<button type="button" class="btn btn-primary btn-sm w-100" onclick="pilihGambar(\'' . htmlspecialchars($row['link_gambar']) . '\', \'' . htmlspecialchars($row['judul']) . '\')"><i class="fas fa-check"></i> Pilih</button>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="col-12 text-center">Tidak ada gambar edukasi tersedia</div>';
                    }
                    ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Pastikan jQuery dan Bootstrap JS dimuat -->
<script>
    // Periksa jika jQuery belum dimuat
    if (typeof jQuery === 'undefined') {
        console.error('jQuery tidak ditemukan. Memuat dari CDN...');
        const jqueryScript = document.createElement('script');
        jqueryScript.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        jqueryScript.integrity = 'sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=';
        jqueryScript.crossOrigin = 'anonymous';
        document.head.appendChild(jqueryScript);
    }

    // Periksa jika Bootstrap JS belum dimuat
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JS tidak ditemukan. Memuat dari CDN...');
        const bootstrapScript = document.createElement('script');
        bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js';
        bootstrapScript.integrity = 'sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4';
        bootstrapScript.crossOrigin = 'anonymous';
        bootstrapScript.onload = function() {
            console.log('Bootstrap JS berhasil dimuat. Menginisialisasi tab...');
            // Inisialisasi tab secara langsung setelah Bootstrap dimuat
            initializeTabs();
        };
        document.head.appendChild(bootstrapScript);
    }

    // Fungsi untuk menginisialisasi tab
    function initializeTabs() {
        if (typeof bootstrap !== 'undefined') {
            // Inisialisasi semua tab
            const tabElements = document.querySelectorAll('#myTab a');
            tabElements.forEach(tabElement => {
                try {
                    // Coba instansiasi Tab
                    const tabInstance = new bootstrap.Tab(tabElement);
                    console.log('Tab berhasil dibuat untuk:', tabElement.id);

                    // Tambahkan event listener
                    tabElement.addEventListener('click', function(e) {
                        e.preventDefault();
                        tabInstance.show();
                    });
                } catch (error) {
                    console.error('Gagal menginisialisasi tab untuk element', tabElement.id, error);
                }
            });

            // Setup event listener untuk tab yang ditampilkan
            const myTabs = document.getElementById('myTab');
            if (myTabs) {
                myTabs.addEventListener('shown.bs.tab', function(event) {
                    const activeTab = event.target;
                    const tabId = activeTab.getAttribute('href').substring(1);
                    console.log('Tab aktif:', tabId);

                    // Load data berdasarkan tab yang aktif
                    if (tabId === 'skrining') {
                        refreshStatusObstetriData();
                    } else if (tabId === 'riwayat-kehamilan') {
                        refreshRiwayatKehamilanData();
                    } else if (tabId === 'status-ginekologi') {
                        refreshStatusGinekologiData();
                    }
                });
            }

            console.log('Semua tab berhasil diinisialisasi');
        } else {
            console.error('Bootstrap JS belum tersedia. Tab tidak dapat diinisialisasi');
        }
    }

    // Inisialisasi tab jika bootstrap sudah tersedia
    if (typeof bootstrap !== 'undefined') {
        document.addEventListener('DOMContentLoaded', initializeTabs);
    }
</script>

<?php 
// Debug marker akhir file
echo "<!-- END FORM EDIT -->\n";
error_log("Form Edit Pemeriksaan: File execution completed");
?>

<!-- Script utama aplikasi -->
<script>
    const loadingManager = {
        overlay: null,
        timeoutId: null,

        init() {
            this.overlay = document.getElementById('globalLoadingOverlay');
        },

        show() {
            if (this.overlay) {
                clearTimeout(this.timeoutId);
                this.overlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        },

        hide() {
            if (this.overlay) {
                // Tambah delay kecil untuk memastikan transisi modal selesai
                this.timeoutId = setTimeout(() => {
                    this.overlay.style.display = 'none';
                    document.body.style.overflow = '';
                }, 300);
            }
        }
    };

    // Inisialisasi saat dokumen dimuat
    document.addEventListener('DOMContentLoaded', () => {
        loadingManager.init();

        // Event listener untuk modal
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('show.bs.modal', () => {
                loadingManager.hide(); // Pastikan loading hilang saat modal muncul
            });

            modal.addEventListener('hidden.bs.modal', () => {
                loadingManager.hide(); // Pastikan loading hilang saat modal tertutup
            });
        });
    });

    function gunakanTemplate(isi) {
        try {
            loadingManager.show();
            const currentValue = document.getElementById('tatalaksana').value;
            if (currentValue && currentValue.trim() !== '') {
                document.getElementById('tatalaksana').value = currentValue + '\n\n' + isi;
            } else {
                document.getElementById('tatalaksana').value = isi;
            }
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalDaftarTemplate'));
            if (modal) {
                modal.hide();
            }
        } finally {
            loadingManager.hide();
        }
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
        try {
            loadingManager.show();
            const currentValue = document.getElementById('ultrasonografi').value;
            if (currentValue && currentValue.trim() !== '') {
                document.getElementById('ultrasonografi').value = currentValue + '\n\n' + isi;
            } else {
                document.getElementById('ultrasonografi').value = isi;
            }
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalDaftarTemplateUsg'));
            if (modal) {
                modal.hide();
            }
        } finally {
            loadingManager.hide();
        }
    }

    function gunakanDiagnosis(isi) {
        document.getElementById('diagnosis').value = isi;
        $('#modalRiwayatDiagnosis').modal('hide');
    }

    function gunakanTemplateEdukasi(isi) {
        try {
            loadingManager.show();
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalDaftarEdukasi'));
            if (modal) {
                modal.hide();
            }
        } finally {
            loadingManager.hide();
        }
    }

    function gunakanTemplateResume(isi) {
        try {
            loadingManager.show();
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalDaftarTemplateResume'));
            if (modal) {
                modal.hide();
            }
        } finally {
            loadingManager.hide();
        }
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
                var bentukSediaan = checkbox.getAttribute('data-bentuk-sediaan');
                var dosis = checkbox.getAttribute('data-dosis');
                // Menghilangkan pengambilan data-catatan

                // Format: [nama_obat]     No.
                //          [dosis]
                var textObat = namaObat + '     No.X';
                textObat += '\n         ' + dosis;

                // Menghilangkan penambahan catatan ke teks obat

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

        // Tutup modal menggunakan Bootstrap 5 API
        const modalElement = document.getElementById('modalDaftarTemplateResep');
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) {
            modalInstance.hide();
        } else {
            // Fallback ke jQuery jika instance tidak ditemukan
            $('#modalDaftarTemplateResep').modal('hide');
        }
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
            $conn = getConnection();
            $sql = "SELECT s.*
                    FROM reg_periksa r 
                    JOIN status_obstetri s ON r.no_rkm_medis = s.no_rkm_medis
                    WHERE r.no_rawat = :no_rawat";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':no_rawat', $no_rawat, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt && $stmt->rowCount() > 0) {
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
            // Get PDO connection
            $conn = getConnection();
            
            // Query langsung ke tabel status_ginekologi dengan no_rkm_medis
            // Perhatikan bahwa nama kolom menggunakan kapital di awal (seperti dalam model StatusGinekologi.php)
            $sql = "SELECT * FROM status_ginekologi WHERE no_rkm_medis = :no_rkm_medis ORDER BY created_at DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':no_rkm_medis', $no_rkm_medis, PDO::PARAM_STR);
            $stmt->execute();

            // Debug output
            error_log("Query status_ginekologi untuk no_rkm_medis: " . $no_rkm_medis);

            if ($stmt && $stmt->rowCount() > 0) {
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

    function printEdukasi() {
        // Ambil isi dari textarea edukasi
        const isiEdukasi = document.getElementById('edukasi').value.trim();

        // Validasi isi edukasi
        if (!isiEdukasi) {
            alert('Mohon isi data edukasi terlebih dahulu sebelum mencetak');
            return;
        }

        const noRawat = '<?= $pemeriksaan['no_rawat'] ?>';
        const namaPasien = '<?= $pasien['nm_pasien'] ?>';
        const noRm = '<?= $pasien['no_rkm_medis'] ?>';

        // Redirect ke halaman print dengan parameter
        const url = 'modules/rekam_medis/print_edukasi.php?isi=' + encodeURIComponent(isiEdukasi) +
            '&no_rawat=' + encodeURIComponent(noRawat) +
            '&nama=' + encodeURIComponent(namaPasien) +
            '&no_rm=' + encodeURIComponent(noRm);

        // Buka halaman print di tab baru
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

    // Fungsi untuk debugging Status Obstetri
    function debugStatusObstetri(message) {
        console.log('DEBUG StatusObstetri: ' + message);
    }

    // Fungsi untuk refresh data status obstetri
    function refreshStatusObstetriData() {
        const noRkmMedis = '<?= $pasien['no_rkm_medis'] ?>';
        const statusObstetriContent = document.getElementById('statusObstetriContent');

        if (!statusObstetriContent) {
            console.error('Element statusObstetriContent tidak ditemukan');
            return;
        }

        debugStatusObstetri('Refreshing Status Obstetri data for: ' + noRkmMedis);

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
                                                <a href="index.php?module=rekam_medis&action=edit_status_obstetri&id=${so.id_status_obstetri}&source=form_edit_pemeriksaan" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="index.php?module=rekam_medis&action=hapus_status_obstetri&id=${so.id_status_obstetri}&source=form_edit_pemeriksaan" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
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
                    document.getElementById('statusObstetriTableBody').innerHTML =
                        '<tr><td colspan="7" class="text-center text-danger">Error: Gagal memuat data status obstetri</td></tr>';
                }
            } else {
                debugStatusObstetri('HTTP error: ' + this.status);
                console.error('HTTP Error:', this.status);
                document.getElementById('statusObstetriTableBody').innerHTML =
                    '<tr><td colspan="7" class="text-center text-danger">Error: Gagal memuat data status obstetri</td></tr>';
            }

            // Hapus loading overlay
            if (statusObstetriContent.contains(loadingOverlay)) {
                statusObstetriContent.removeChild(loadingOverlay);
            }
        };

        xhr.onerror = function() {
            debugStatusObstetri('Request failed');
            console.error('Request Failed');
            document.getElementById('statusObstetriTableBody').innerHTML =
                '<tr><td colspan="7" class="text-center text-danger">Error: Gagal terhubung ke server</td></tr>';

            // Hapus loading overlay
            if (statusObstetriContent.contains(loadingOverlay)) {
                statusObstetriContent.removeChild(loadingOverlay);
            }
        };

        xhr.send();
    }

    // Fungsi untuk refresh data riwayat kehamilan
    function refreshRiwayatKehamilanData() {
        const noRkmMedis = '<?= $pasien['no_rkm_medis'] ?>';
        const riwayatKehamilanContent = document.getElementById('riwayatKehamilanContent');

        if (!riwayatKehamilanContent) {
            console.error('Element riwayatKehamilanContent tidak ditemukan');
            return;
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
        riwayatKehamilanContent.style.position = 'relative';
        riwayatKehamilanContent.appendChild(loadingOverlay);

        console.log('Refreshing Riwayat Kehamilan data for: ' + noRkmMedis);

        // AJAX request untuk riwayat kehamilan
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'index.php?module=rekam_medis&action=get_riwayat_kehamilan_ajax&no_rkm_medis=' + encodeURIComponent(noRkmMedis), true);

        xhr.onload = function() {
            if (this.status === 200) {
                try {
                    const rawResponse = this.responseText;

                    // Coba deteksi jika ini adalah HTML bukan JSON
                    if (rawResponse.trim().startsWith('<')) {
                        console.error('ERROR: Respons berisi HTML, bukan JSON. Endpoint mungkin tidak benar.');

                        // Tampilkan pesan kesalahan tentang endpoint
                        const errorAlert = document.createElement('div');
                        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                        errorAlert.innerHTML = `
                            <strong>Error!</strong> Endpoint AJAX mengembalikan HTML, bukan JSON. Ini mungkin karena:
                            <ul>
                                <li>File get_riwayat_kehamilan_ajax.php tidak ditemukan atau bermasalah</li>
                                <li>Session habis dan halaman dialihkan ke login</li>
                                <li>Ada error PHP pada endpoint</li>
                            </ul>
                            <p>Silakan periksa endpoint AJAX: <code>index.php?module=rekam_medis&action=get_riwayat_kehamilan_ajax</code></p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;

                        riwayatKehamilanContent.insertBefore(errorAlert, riwayatKehamilanContent.firstChild);
                        throw new Error('Respons berisi HTML, bukan JSON');
                    }

                    const response = JSON.parse(rawResponse);
                    console.log('Riwayat kehamilan data received:', response);

                    const tableBody = document.getElementById('riwayatKehamilanTableBody');
                    if (tableBody) {
                        if (response.status === 'success' && response.data && response.data.length > 0) {
                            let tableHtml = '';

                            response.data.forEach(function(rk) {
                                tableHtml += `
                                    <tr>
                                        <td>${rk.no_urut_kehamilan || '-'}</td>
                                        <td>${rk.status_kehamilan || '-'}</td>
                                        <td>${rk.jenis_persalinan || '-'}</td>
                                        <td>${rk.tempat_persalinan || '-'}</td>
                                        <td>${rk.penolong_persalinan || '-'}</td>
                                        <td>${rk.tahun_persalinan || '-'}</td>
                                        <td>${rk.jenis_kelamin_anak || '-'}</td>
                                        <td>${rk.berat_badan_lahir || '-'}</td>
                                        <td>${rk.kondisi_lahir || '-'}</td>
                                        <td>
                                            <a href="index.php?module=rekam_medis&action=edit_riwayat_kehamilan&id=${rk.id_riwayat_kehamilan}&source=form_edit_pemeriksaan" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?module=rekam_medis&action=hapus_riwayat_kehamilan&id=${rk.id_riwayat_kehamilan}&source=form_edit_pemeriksaan" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
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
                    } else {
                        console.error('ERROR: Table body element riwayatKehamilanTableBody not found');
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

    // Fungsi untuk refresh data status ginekologi
    function refreshStatusGinekologiData() {
        const noRkmMedis = '<?= $pasien['no_rkm_medis'] ?>';
        const statusGinekologiContent = document.getElementById('statusGinekologiContent');

        if (!statusGinekologiContent) {
            console.error('Element statusGinekologiContent tidak ditemukan');
            return;
        }

        // Buat loading overlay
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
                                            <a href="index.php?module=rekam_medis&action=edit_status_ginekologi&id=${sg.id_status_ginekologi}&source=<?= $_SESSION['source_page'] ?? 'form_edit_pemeriksaan' ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?module=rekam_medis&action=hapus_status_ginekologi&id=${sg.id_status_ginekologi}&source=<?= $_SESSION['source_page'] ?? 'form_edit_pemeriksaan' ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
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

    // Memuat data saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        // Inisialisasi tab melalui fungsi yang telah didefinisikan di script sebelumnya
        if (typeof initializeTabs === 'function') {
            initializeTabs();
        }

        // Buat IntersectionObserver untuk setiap konten tab sebagai fallback
        const setupObserver = (elementId, callbackFn) => {
            const element = document.getElementById(elementId);
            if (element) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            console.log(`Tab ${elementId} terlihat, memanggil callback via IntersectionObserver`);
                            callbackFn();
                            observer.disconnect();
                        }
                    });
                });
                observer.observe(element);
            }
        };

        // Set up observers untuk setiap tab content
        setupObserver('skrining', refreshStatusObstetriData);
        setupObserver('riwayat-kehamilan', refreshRiwayatKehamilanData);
        setupObserver('status-ginekologi', refreshStatusGinekologiData);
    });

    // Tambahkan di bagian awal script, setelah definisi loadingManager
    const modalCleanup = {
        cleanup() {
            // Hapus semua overlay yang mungkin tertinggal
            const overlays = document.querySelectorAll('.loading-overlay');
            overlays.forEach(overlay => overlay.remove());

            // Reset scroll
            document.body.style.overflow = '';

            // Hapus semua modal backdrop yang mungkin tertinggal
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());

            // Tutup semua modal yang masih terbuka
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
                modal.classList.remove('show');
                modal.style.display = 'none';
            });

            // Reset loading manager
            if (loadingManager && loadingManager.overlay) {
                loadingManager.hide();
            }
        }
    };

    // Tambahkan event listener untuk tombol close modal
    document.querySelectorAll('.modal .btn-close, .modal .close').forEach(button => {
        button.addEventListener('click', () => {
            setTimeout(modalCleanup.cleanup, 300);
        });
    });

    // Tambahkan event listener untuk klik di luar modal
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            setTimeout(modalCleanup.cleanup, 300);
        }
    });

    // Override fungsi hide loading untuk selalu membersihkan modal
    const originalHide = loadingManager.hide;
    loadingManager.hide = function() {
        originalHide.call(this);
        modalCleanup.cleanup();
    };

    // Tambahkan di bagian script, setelah DOMContentLoaded event listener yang ada
    document.addEventListener('keydown', function(e) {
        // Jika tombol Escape ditekan
        if (e.key === 'Escape') {
            loadingManager.hide();

            // Cari semua modal yang terbuka
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });
        }
    });

    // Tambahkan event handler untuk semua modal
    const allModals = document.querySelectorAll('.modal');
    allModals.forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            // Pastikan loading overlay hilang ketika modal ditutup
            loadingManager.hide();
            document.body.style.overflow = '';
        });

        // Tambahkan error handler untuk modal
        modal.addEventListener('show.bs.modal', function(event) {
            try {
                // Reset modal state
                const modalBody = this.querySelector('.modal-body');
                if (modalBody) {
                    const loadingOverlays = modalBody.querySelectorAll('.loading-overlay');
                    loadingOverlays.forEach(overlay => overlay.remove());
                }
            } catch (error) {
                console.error('Error saat membuka modal:', error);
                loadingManager.hide();
            }
        });
    });

    // Perbaiki fungsi gunakanTemplate untuk menangani error dengan lebih baik
    function handleTemplateError(error, modalId) {
        console.error('Error saat menggunakan template:', error);
        loadingManager.hide();

        // Tutup modal jika masih terbuka
        const modal = document.getElementById(modalId);
        if (modal) {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }

        // Reset scroll
        document.body.style.overflow = '';

        // Tampilkan pesan error ke user
        alert('Terjadi kesalahan saat menggunakan template. Silakan coba lagi.');
    }

    // Update semua fungsi template untuk menggunakan error handler
    function gunakanTemplate(isi) {
        try {
            loadingManager.show();
            const currentValue = document.getElementById('tatalaksana').value;
            if (currentValue && currentValue.trim() !== '') {
                document.getElementById('tatalaksana').value = currentValue + '\n\n' + isi;
            } else {
                document.getElementById('tatalaksana').value = isi;
            }
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalDaftarTemplate'));
            if (modal) {
                modal.hide();
            }
        } catch (error) {
            handleTemplateError(error, 'modalDaftarTemplate');
        } finally {
            setTimeout(() => {
                loadingManager.hide();
                document.body.style.overflow = '';
            }, 300);
        }
    }

    // Fungsi untuk filter gambar
    function filterGambar() {
        var kategori = document.getElementById('filter_kategori_gambar').value;
        var searchText = document.getElementById('search_gambar').value.toLowerCase();
        var items = document.querySelectorAll('.gambar-item');
        var hasVisibleItems = false;

        items.forEach(function(item) {
            var itemKategori = item.getAttribute('data-kategori');
            var itemJudul = item.getAttribute('data-judul').toLowerCase();

            var matchesKategori = kategori === '' || itemKategori === kategori;
            var matchesSearch = searchText === '' || itemJudul.includes(searchText);

            if (matchesKategori && matchesSearch) {
                item.style.display = '';
                hasVisibleItems = true;
            } else {
                item.style.display = 'none';
            }
        });

        // Tampilkan pesan jika tidak ada item yang sesuai
        var noDataMessage = document.querySelector('.no-data-message');
        if (!hasVisibleItems) {
            if (!noDataMessage) {
                noDataMessage = document.createElement('div');
                noDataMessage.className = 'col-12 text-center no-data-message';
                noDataMessage.innerHTML = 'Tidak ada gambar yang sesuai dengan kriteria pencarian';
                document.getElementById('gridGambarEdukasi').appendChild(noDataMessage);
            }
            noDataMessage.style.display = '';
        } else if (noDataMessage) {
            noDataMessage.style.display = 'none';
        }
    }

    // Event listener untuk filter
    document.getElementById('filter_kategori_gambar').addEventListener('change', filterGambar);
    document.getElementById('search_gambar').addEventListener('input', filterGambar);

    // Event listener untuk filter formularium (resep)
    document.getElementById('search_generik').addEventListener('input', filterFormularium);
    document.getElementById('filter_kategori_obat').addEventListener('change', filterFormularium);

    function filterFormularium() {
        var kategori = document.getElementById('filter_kategori_obat').value.toLowerCase();
        var searchText = document.getElementById('search_generik').value.toLowerCase();
        var rows = document.querySelectorAll('#tabelFormularium tbody tr.obat-row');
        var hasVisible = false;
        rows.forEach(function(row) {
            var rowKategori = row.getAttribute('data-kategori').toLowerCase();
            // gabungkan teks dari kolom untuk pencarian
            var text = Array.from(row.cells).slice(1, 6).map(function(cell) {
                return cell.textContent.toLowerCase();
            }).join(' ');
            var matchesKategori = kategori === '' || rowKategori === kategori;
            var matchesSearch = searchText === '' || text.includes(searchText);
            if (matchesKategori && matchesSearch) {
                row.style.display = '';
                hasVisible = true;
            } else {
                row.style.display = 'none';
            }
        });
        var tbody = document.querySelector('#tabelFormularium tbody');
        var noData = document.querySelector('#tabelFormularium tbody tr.no-data-row');
        if (!hasVisible) {
            if (!noData) {
                var tr = document.createElement('tr');
                tr.className = 'no-data-row';
                tr.innerHTML = '<td colspan="7" class="text-center">Tidak ada data obat yang sesuai dengan kriteria pencarian</td>';
                tbody.appendChild(tr);
            } else {
                noData.style.display = '';
            }
        } else if (noData) {
            noData.style.display = 'none';
        }
    }

    // Fungsi untuk memilih gambar
    function pilihGambar(namaFile, judul) {
        // Simpan data gambar ke input hidden
        document.getElementById('gambarEdukasiInput').value = namaFile;
        document.getElementById('judulGambarEdukasiInput').value = judul;

        // Tampilkan gambar dengan path yang benar
        const gambarPath = 'uploads/edukasi/' + namaFile;
        document.getElementById('gambarEdukasiTerpilih').src = gambarPath;
        document.getElementById('judulGambarEdukasiTerpilih').textContent = judul;

        // Tampilkan card gambar
        document.getElementById('cardGambarEdukasi').style.display = 'block';

        // Tutup modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalPilihGambarEdukasi'));
        if (modal) {
            modal.hide();
        }
    }

    // Fungsi untuk menghapus gambar terpilih
    function hapusGambarTerpilih() {
        // Reset input hidden
        document.getElementById('gambarEdukasiInput').value = '';
        document.getElementById('judulGambarEdukasiInput').value = '';

        // Reset gambar
        document.getElementById('gambarEdukasiTerpilih').src = '';
        document.getElementById('judulGambarEdukasiTerpilih').textContent = '';

        // Sembunyikan card
        document.getElementById('cardGambarEdukasi').style.display = 'none';
    }

    function bukaGambarDiTabBaru() {
        const gambarSrc = document.getElementById('gambarEdukasiTerpilih').src;
        if (gambarSrc) {
            window.open(gambarSrc, '_blank');
        }
    }
</script>