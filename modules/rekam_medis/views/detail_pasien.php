<?php
// Tambahkan debugging
error_log("File detail_pasien.php diakses");

// Pastikan variabel yang diperlukan tersedia
if (!isset($pasien)) {
    error_log("Error: Variabel pasien tidak tersedia di detail_pasien.php");
    echo "Error: Data pasien tidak tersedia.";
    exit;
}

error_log("Data pasien: " . json_encode($pasien));
?>
<!DOCTYPE html>
<html>

<head>
    <!-- Load jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Load Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Load SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <style>
        /* Fix untuk sidebar collapse - pastikan konten mengisi seluruh lebar */
        body .main-content {
            transition: margin-left 0.3s ease !important;
        }

        body .sidebar.minimized~.main-content {
            margin-left: 60px !important;
        }

        /* Gaya untuk tab panes dan konten lainnya */
        .tab-pane {
            transition: all 0.3s ease-in-out;
            overflow: hidden;
            font-size: 0.8rem;
            /* Mengurangi ukuran font secara global */
        }

        .tab-pane:not(.active),
        .tab-pane:not(.show) {
            display: none;
        }

        .alert {
            margin-bottom: 1rem;
        }

        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }

        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }

        /* Tambahan style untuk toggle switch Berikutnya Gratis */
        .toggle-gratis:checked {
            background-color: #198754;
            border-color: #198754;
        }

        .toggle-gratis {
            cursor: pointer;
            width: 2rem;
            height: 1rem;
        }

        /* Style khusus untuk tombol toggle di header */
        .header-toggle {
            margin-top: 2px;
        }

        .header-toggle .form-check-input {
            height: 15px;
            width: 30px;
        }

        .header-toggle .toggle-spinner {
            width: 12px;
            height: 12px;
        }

        /* Toast notification */
        .toast {
            z-index: 9999;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }

        .toggle-spinner {
            top: 50% !important;
            transform: translate(-50%, -50%) !important;
        }

        /* Buat efek pulse pada toggle saat terjadi perubahan */
        @keyframes toggle-pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .toggle-success {
            animation: toggle-pulse 0.5s;
        }

        /* Style untuk posisi tombol di header */
        .header-buttons {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 100;
        }

        /* Style untuk status gratis text */
        .gratis-status {
            font-size: 0.7rem;
            background-color: #198754;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            white-space: nowrap;
            display: none;
            margin-right: 5px;
            transition: all 0.3s ease;
        }

        .gratis-status.active {
            display: inline-block;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Menyembunyikan tombol voucher lama */
        .voucher-button {
            display: none !important;
        }

        /* Responsive fixes */
        @media (max-width: 767px) {
            .card-header {
                padding-bottom: 45px !important;
                /* Tambah padding untuk ruang tombol */
                position: relative;
            }

            .header-buttons {
                position: absolute;
                right: 10px;
                top: auto;
                bottom: 10px;
                transform: none;
                width: auto;
                justify-content: flex-end;
                gap: 5px;
            }

            /* Pastikan tombol voucher tetap lingkaran */
            .voucher-button-mobile {
                flex-shrink: 0;
                /* Mencegah tombol menyusut */
                width: 25px !important;
                height: 25px !important;
                padding: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                border-radius: 50% !important;
            }

            .voucher-button-mobile i {
                font-size: 10px !important;
            }

            /* Sesuaikan ukuran teks status agar tidak terlalu besar */
            .gratis-status {
                font-size: 0.65rem;
                padding: 1.5px 5px;
            }

            /* Pastikan tombol-tombol sejajar */
            .header-toggle,
            .header-buttons a {
                margin-bottom: 0 !important;
            }

            .header-toggle .form-check-input {
                margin-top: 0;
            }
        }
    </style>

    <style>
        .tab-pane:not(.show) {
            display: none;
            height: 0;
            padding: 0;
            margin: 0;
        }

        .tab-pane.show {
            display: block;
        }

        .nav-tabs .nav-link {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            /* Mengurangi ukuran font untuk navigasi */
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

        /* Style untuk tabel resizable columns */
        .table-resizable {
            table-layout: fixed;
            width: 100%;
        }

        .table-resizable th {
            position: relative;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table-resizable th .resizer {
            position: absolute;
            top: 0;
            right: 0;
            width: 8px;
            height: 100%;
            background-color: transparent;
            cursor: col-resize;
            z-index: 10;
        }

        .table-resizable th .resizer:hover,
        .table-resizable th .resizing {
            border-right: 2px solid #0d6efd;
        }

        /* Style untuk riwayat pemeriksaan */
        .riwayat-item {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
            overflow: hidden;
        }

        .riwayat-header {
            background-color: #f8f9fa;
            padding: 0.75rem 1rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s;
        }

        .riwayat-header:hover {
            background-color: #e9ecef;
        }

        .riwayat-content {
            padding: 1rem;
            display: none;
            border-top: 1px solid #dee2e6;
        }

        .riwayat-toggle {
            transition: transform 0.2s;
        }

        .riwayat-toggle.active {
            transform: rotate(180deg);
        }

        /* Tambahan style untuk menangani collapse */
        .collapse:not(.show) {
            display: none;
        }

        .collapsing {
            height: 0;
            overflow: hidden;
            transition: height 0.35s ease;
        }

        #myTabContent {
            margin-bottom: 0;
        }

        .tab-content>.tab-pane {
            margin-bottom: 0;
        }

        .riwayat-section {
            margin-top: 1rem;
        }

        .table {
            font-size: 0.8rem;
            /* Mengurangi ukuran font untuk tabel */
        }

        .card-title {
            font-size: 0.85rem !important;
            /* Mengurangi ukuran font untuk judul card */
        }

        /* Tambahan style untuk tombol download */
        .download-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .download-buttons .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .download-buttons .btn i {
            margin-right: 6px;
        }

        .card-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-tools {
            display: flex;
            justify-content: flex-end;
            width: 100%;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @media (max-width: 992px) {
            .card-header-actions {
                flex-direction: column;
                align-items: flex-start;
            }

            .download-buttons {
                margin-top: 10px;
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .download-buttons {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
                width: 100%;
            }

            .download-buttons .btn {
                padding: 6px 8px;
                font-size: 0.8rem;
                width: 100%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                white-space: normal;
                text-align: center;
                min-height: 40px;
            }

            .download-buttons .btn i {
                margin-right: 4px;
                font-size: 0.9rem;
            }

            .download-buttons .btn span {
                display: inline-block;
                line-height: 1.2;
            }

            /* Tampilan super compact untuk layar sangat kecil */
            @media (max-width: 400px) {
                .download-buttons {
                    grid-template-columns: 1fr;
                }
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .card-tools {
                margin-top: 10px;
                width: 100%;
            }
        }

        /* Tambahkan CSS untuk tab Download */
        .download-section .card {
            border: 1px solid rgba(0, 0, 0, .125);
            transition: all 0.3s ease;
        }

        .download-section .card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .download-section .btn {
            text-align: left;
            padding: 12px 15px;
            font-size: 0.9rem;
        }

        .download-section .btn i {
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .download-section .col-md-6 {
                padding: 0 5px;
            }

            .download-section .btn {
                padding: 10px;
                font-size: 0.85rem;
            }
        }

        /* Update CSS untuk layout header */
        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, .125);
            position: relative;
        }

        .voucher-button {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 35px;
            height: 35px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .card-header .d-flex {
            padding-right: 45px;
            /* Memberikan ruang untuk tombol voucher */
        }

        @media (max-width: 768px) {
            .card-header .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
                width: 100%;
            }

            .card-header .card-title {
                margin-top: 5px;
            }

            .voucher-button {
                top: 10px;
                right: 10px;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Debug info -->
                <?php if (isset($_GET['debug'])): ?>
                    <div class="alert alert-info">
                        <h5>Debug Information:</h5>
                        <pre>
                <?php
                    echo "Riwayat Pemeriksaan:\n";
                    print_r($riwayatPemeriksaan);
                ?>
                </pre>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header p-2">
                        <div class="d-flex align-items-center gap-2">
                            <!-- Tombol Kembali di kiri -->
                            <a href="index.php?module=rekam_medis&action=manajemen_antrian" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>

                            <h5 class="card-title mb-0 d-none d-md-inline-block">Detail Rekam Medis Pasien</h5>
                        </div>

                        <!-- Tombol Voucher, Berikutnya Gratis, dan Selesai di kanan -->
                        <div class="header-buttons">
                            <?php if (isset($_GET['id_pendaftaran']) && isset($_GET['source']) && $_GET['source'] === 'antrian'): ?>
                                <button type="button" class="btn btn-success btn-sm" onclick="tandaiSelesai('<?= $_GET['id_pendaftaran'] ?>')">
                                    <i class="fas fa-flag me-1"></i>Tandai Selesai
                                </button>
                            <?php endif; ?>
                            <span class="gratis-status <?= !empty($pasien['berikutnya_gratis']) ? 'active' : '' ?>">
                                <i class="fas fa-check-circle me-1"></i>Digratiskan untuk Kunjungan Berikutnya
                            </span>
                            <div class="form-check form-switch d-flex justify-content-center position-relative header-toggle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Kunjungan berikutnya gratis">
                                <input class="form-check-input toggle-gratis" type="checkbox" role="switch"
                                    data-no-rm="<?= $pasien['no_rkm_medis'] ?>"
                                    <?= !empty($pasien['berikutnya_gratis']) ? 'checked' : '' ?>>
                                <div class="toggle-spinner position-absolute top-0 start-50 translate-middle-x d-none">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                            <a href="../admin_praktek/voucher.php" class="btn btn-dark btn-sm rounded-circle voucher-button-mobile" style="width: 25px; height: 25px; padding: 0; display: flex; align-items: center; justify-content: center;" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Buat Voucher">
                                <i class="fas fa-tags text-white" style="font-size: 10px;"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['success'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                <?php unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i> <?= $_SESSION['error'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Debug info -->
                        <?php
                        error_log("=== Debug Riwayat Pemeriksaan ===");
                        error_log("Timestamp: " . date('Y-m-d H:i:s'));
                        if (isset($riwayatPemeriksaan)) {
                            error_log("Jumlah data: " . count($riwayatPemeriksaan));
                            if (count($riwayatPemeriksaan) > 0) {
                                error_log("Data pertama: " . json_encode($riwayatPemeriksaan[0]));
                            }
                        } else {
                            error_log("Variable riwayatPemeriksaan tidak terset");
                        }
                        error_log("================================");
                        ?>

                        <!-- Informasi waktu akses untuk memastikan data terbaru -->
                        <div class="small text-muted mb-3">
                            Data diakses pada: <?= date('Y-m-d H:i:s') ?>
                        </div>

                        <ul class="nav nav-tabs" id="myTab" role="tablist">
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
                            <li class="nav-item">
                                <a class="nav-link collapsed" id="surat-tab" data-toggle="collapse" href="#surat" role="tab">
                                    Surat <i class="fas fa-chevron-down"></i>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link collapsed" id="download-tab" data-toggle="collapse" href="#download" role="tab">
                                    Download <i class="fas fa-chevron-down"></i>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <!-- Tab Identitas -->
                            <div class="tab-pane fade collapse" id="identitas" role="tabpanel">
                                <div class="mb-3 d-flex justify-content-end">
                                    <a href="index.php?module=rekam_medis&action=editPasien&id=<?= $pasien['no_rkm_medis'] ?>&source=<?= $_SESSION['source_page'] ?>" class="btn btn-warning btn-sm me-2">
                                        <i class="fas fa-edit"></i> Edit Data Pasien
                                    </a>
                                    <?php if (!empty($pasien['no_tlp'])): ?>
                                        <a href="https://wa.me/62<?= preg_replace('/[^0-9]/', '', $pasien['no_tlp']) ?>" target="_blank" class="btn btn-success btn-sm">
                                            <i class="fab fa-whatsapp"></i> Kirim WhatsApp
                                        </a>
                                    <?php endif; ?>
                                </div>

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
                                                        <th width="140" class="text-muted px-3">No.RM</th>
                                                        <td class="px-3"><?= $pasien['no_rkm_medis'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted px-3">Nama Pasien</th>
                                                        <td class="px-3"><?= $pasien['nm_pasien'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted px-3">No. KTP</th>
                                                        <td class="px-3"><?= $pasien['no_ktp'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted px-3">Jenis Kelamin</th>
                                                        <td class="px-3"><?= $pasien['jk'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted px-3">Tanggal Lahir</th>
                                                        <td class="px-3"><?= date('d-m-Y', strtotime($pasien['tgl_lahir'])) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted px-3">Umur</th>
                                                        <td class="px-3"><?= $pasien['umur'] ?> tahun</td>
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
                                                        <td class="px-3"><?= $pasien['alamat'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted px-3">Kecamatan</th>
                                                        <td class="px-3">
                                                            <?php
                                                            $nama_kecamatan = '-';
                                                            foreach ($kecamatan as $kec) {
                                                                if ($kec['kd_kec'] == $pasien['kd_kec']) {
                                                                    $nama_kecamatan = $kec['nm_kec'];
                                                                    break;
                                                                }
                                                            }
                                                            echo $nama_kecamatan;
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted px-3">No. Telepon</th>
                                                        <td class="px-3"><?= $pasien['no_tlp'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted px-3">Pekerjaan</th>
                                                        <td class="px-3"><?= $pasien['pekerjaan'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted px-3">Status Nikah</th>
                                                        <td class="px-3"><?= $pasien['stts_nikah'] ?? '-' ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted px-3">Catatan Pasien</th>
                                                        <td class="px-3"><?= $pasien['catatan_pasien'] ?? '-' ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Status Obstetri -->
                            <div class="tab-pane fade" id="skrining" role="tabpanel">
                                <div class="mb-3">
                                    <?php if (!isset($statusObstetri) || count($statusObstetri) === 0): ?>
                                        <a href="index.php?module=rekam_medis&action=tambah_status_obstetri&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>&source=<?= $_SESSION['source_page'] ?>" class="btn btn-add btn-sm">
                                            <i class="fas fa-plus"></i> Tambah Status Obstetri
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped">
                                        <thead>
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
                                        <tbody>
                                            <?php if (isset($statusObstetri) && count($statusObstetri) > 0): ?>
                                                <?php foreach ($statusObstetri as $so): ?>
                                                    <tr>
                                                        <td><?= date('d-m-Y', strtotime($so['updated_at'])) ?></td>
                                                        <td><?= $so['gravida'] . '-' . $so['paritas'] . '-' . $so['abortus'] ?></td>
                                                        <td><?= !empty($so['tanggal_hpht']) ? date('d-m-Y', strtotime($so['tanggal_hpht'])) : '-' ?></td>
                                                        <td><?= !empty($so['tanggal_tp']) ? date('d-m-Y', strtotime($so['tanggal_tp'])) : '-' ?></td>
                                                        <td><?= !empty($so['tanggal_tp_penyesuaian']) ? date('d-m-Y', strtotime($so['tanggal_tp_penyesuaian'])) : '-' ?></td>
                                                        <td>
                                                            <?php
                                                            $faktor_risiko = [];
                                                            if (!empty($so['faktor_risiko_umum'])) {
                                                                $faktor_risiko[] = 'Umum: ' . str_replace(',', ', ', $so['faktor_risiko_umum']);
                                                            }
                                                            if (!empty($so['faktor_risiko_obstetri'])) {
                                                                $faktor_risiko[] = 'Obstetri: ' . str_replace(',', ', ', $so['faktor_risiko_obstetri']);
                                                            }
                                                            if (!empty($so['faktor_risiko_preeklampsia'])) {
                                                                $faktor_risiko[] = 'Preeklampsia: ' . str_replace(',', ', ', $so['faktor_risiko_preeklampsia']);
                                                            }
                                                            echo !empty($faktor_risiko) ? implode('<br>', $faktor_risiko) : '-';
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <a href="index.php?module=rekam_medis&action=edit_status_obstetri&id=<?= $so['id_status_obstetri'] ?>&source=<?= $_SESSION['source_page'] ?>" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="index.php?module=rekam_medis&action=hapus_status_obstetri&id=<?= $so['id_status_obstetri'] ?>&source=<?= $_SESSION['source_page'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">Tidak ada data status obstetri</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab Riwayat Kehamilan -->
                            <div class="tab-pane fade" id="riwayat-kehamilan" role="tabpanel">
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="index.php?module=rekam_medis&action=tambah_riwayat_kehamilan&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>&source=<?= $_SESSION['source_page'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Tambah Riwayat
                                    </a>
                                </div>
                                <div id="riwayatKehamilanContent" class="position-relative">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-striped table-resizable">
                                            <thead>
                                                <tr>
                                                    <th>No<div class="resizer"></div>
                                                    </th>
                                                    <th>Status<div class="resizer"></div>
                                                    </th>
                                                    <th>Jenis<div class="resizer"></div>
                                                    </th>
                                                    <th>Tempat<div class="resizer"></div>
                                                    </th>
                                                    <th>Penolong<div class="resizer"></div>
                                                    </th>
                                                    <th>Tahun<div class="resizer"></div>
                                                    </th>
                                                    <th>Jenis Kelamin<div class="resizer"></div>
                                                    </th>
                                                    <th>BB<div class="resizer"></div>
                                                    </th>
                                                    <th>Kondisi<div class="resizer"></div>
                                                    </th>
                                                    <th>Aksi<div class="resizer"></div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="riwayatKehamilanTableBody">
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
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="index.php?module=rekam_medis&action=tambah_status_ginekologi&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>&source=<?= $_SESSION['source_page'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Tambah Status Ginekologi
                                    </a>
                                </div>
                                <div id="statusGinekologiContent" class="position-relative">
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
                                                <tr>
                                                    <td colspan="7" class="text-center">Memuat data status ginekologi...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Surat -->
                            <div class="tab-pane fade" id="surat" role="tabpanel">
                                <div class="d-flex justify-content-end mb-3">
                                    <button id="tambahSurat" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Tambah Surat
                                    </button>
                                </div>
                                <div id="suratContent" class="position-relative">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Nomor Surat</th>
                                                    <th>Tanggal</th>
                                                    <th>Jenis Surat</th>
                                                    <th>Diagnosa</th>
                                                    <th>Dokter Pemeriksa</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="suratTableBody">
                                                <tr>
                                                    <td colspan="6" class="text-center">Memuat data surat...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Download -->
                            <div class="tab-pane fade" id="download" role="tabpanel">
                                <div class="p-3">
                                    <div class="row download-section">
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <div class="card-header bg-light">
                                                    <h6 class="card-title mb-0" style="font-size: 0.9rem;">Dokumen Rekam Medis</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="d-grid gap-2">
                                                        <a href="index.php?module=rekam_medis&action=generate_pdf&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-download" target="_blank">
                                                            <i class="fas fa-file-pdf me-2"></i> Resume Rekam Medis
                                                        </a>
                                                        <a href="index.php?module=rekam_medis&action=generate_edukasi_pdf&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-download" target="_blank">
                                                            <i class="fas fa-file-pdf me-2"></i> Edukasi Pasien
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <div class="card-header bg-light">
                                                    <h6 class="card-title mb-0" style="font-size: 0.9rem;">Dokumen Kebidanan</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="d-grid gap-2">
                                                        <a href="index.php?module=rekam_medis&action=generate_status_obstetri_pdf&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-download" target="_blank">
                                                            <i class="fas fa-file-pdf me-2"></i> Status Obstetri
                                                        </a>
                                                        <a href="index.php?module=rekam_medis&action=generate_status_ginekologi_pdf&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-download" target="_blank">
                                                            <i class="fas fa-file-pdf me-2"></i> Status Ginekologi
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Riwayat Kunjungan & Pemeriksaan -->
                        <div class="riwayat-section mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Riwayat Kunjungan & Pemeriksaan</h5>
                                <a href="index.php?module=rekam_medis&action=tambah_pemeriksaan&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>&source=<?= $_SESSION['source_page'] ?>" class="btn btn-add btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Kunjungan
                                </a>
                            </div>

                            <?php
                            // Debug: Tampilkan informasi riwayat pemeriksaan
                            if (isset($riwayatPemeriksaan)) {
                                echo "<!-- Debug: Jumlah riwayat pemeriksaan: " . count($riwayatPemeriksaan) . " -->";
                                if (count($riwayatPemeriksaan) > 0) {
                                    echo "<!-- Debug: Sample data: " . json_encode($riwayatPemeriksaan[0]) . " -->";
                                }
                            } else {
                                echo "<!-- Debug: Variabel riwayatPemeriksaan tidak terdefinisi -->";
                            }
                            ?>

                            <?php if (isset($riwayatPemeriksaan) && count($riwayatPemeriksaan) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped table-resizable">
                                        <thead>
                                            <tr>
                                                <th>Waktu Pemeriksaan<div class="resizer"></div>
                                                </th>
                                                <th>Keluhan Utama<div class="resizer"></div>
                                                </th>
                                                <th>Diagnosis<div class="resizer"></div>
                                                </th>
                                                <th>Tatalaksana<div class="resizer"></div>
                                                </th>
                                                <th>Resep<div class="resizer"></div>
                                                </th>
                                                <th width="120">Aksi<div class="resizer"></div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($riwayatPemeriksaan as $rp): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= date('d-m-Y', strtotime($rp['tgl_registrasi'])) ?> <?= $rp['jam_reg'] ?></strong>
                                                        <?php if (!empty($rp['nm_dokter'])): ?>
                                                            <br><small>Dr. <?= $rp['nm_dokter'] ?></small>
                                                        <?php endif; ?>
                                                        <div class="small text-muted"><?= $rp['no_rawat'] ?></div>
                                                    </td>
                                                    <td><?= $rp['keluhan_utama'] ?: '-' ?></td>
                                                    <td><?= $rp['diagnosis'] ?: '-' ?></td>
                                                    <td><?= $rp['tata'] ?: '-' ?></td>
                                                    <td><?= $rp['resep'] ?: '-' ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalDetail<?= str_replace('/', '', $rp['no_rawat']) ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <a href="index.php?module=rekam_medis&action=edit_kunjungan&no_rawat=<?= $rp['no_rawat'] ?>&source=<?= $_SESSION['source_page'] ?>" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="index.php?module=rekam_medis&action=hapus_kunjungan&no_rawat=<?= $rp['no_rawat'] ?>&source=<?= $_SESSION['source_page'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus kunjungan ini?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>

                                                        <!-- Modal Detail Pemeriksaan -->
                                                        <div class="modal fade" id="modalDetail<?= str_replace('/', '', $rp['no_rawat']) ?>" tabindex="-1" aria-labelledby="modalDetailLabel<?= str_replace('/', '', $rp['no_rawat']) ?>" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="modalDetailLabel<?= str_replace('/', '', $rp['no_rawat']) ?>">Detail Pemeriksaan</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                                <table class="table table-sm">
                                                                                    <tr>
                                                                                        <th width="150">No. Rawat</th>
                                                                                        <td><?= $rp['no_rawat'] ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Tanggal/Jam</th>
                                                                                        <td><?= date('d-m-Y', strtotime($rp['tgl_registrasi'])) ?> <?= $rp['jam_reg'] ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Dokter</th>
                                                                                        <td><?= !empty($rp['nm_dokter']) ? 'Dr. ' . $rp['nm_dokter'] : '-' ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Status Bayar</th>
                                                                                        <td><?= $rp['status_bayar'] ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Rincian</th>
                                                                                        <td>
                                                                                            <?php
                                                                                            if (!empty($rp['rincian'])) {
                                                                                                echo nl2br(htmlspecialchars($rp['rincian']));
                                                                                            } else {
                                                                                                echo '-';
                                                                                            }
                                                                                            ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <table class="table table-sm">
                                                                                    <tr>
                                                                                        <th width="150">Keluhan Utama</th>
                                                                                        <td><?= $rp['keluhan_utama'] ?: '-' ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Riwayat Penyakit Sekarang</th>
                                                                                        <td><?= $rp['rps'] ?: '-' ?></td>
                                                                                    </tr>
                                                                                </table>
                                                                            </div>
                                                                        </div>

                                                                        <h6 class="mb-2 mt-3">Hasil Pemeriksaan<?= $rp['tgl_pemeriksaan'] ? ': ' . date('d-m-Y H:i:s', strtotime($rp['tgl_pemeriksaan'])) : '' ?></h6>
                                                                        <div class="row">
                                                                            <div class="col-md-4">
                                                                                <table class="table table-sm">
                                                                                    <tr>
                                                                                        <th width="150">BB/TB</th>
                                                                                        <td><?= ($rp['bb'] || $rp['tb']) ? ($rp['bb'] ?: '-') . ' kg / ' . ($rp['tb'] ?: '-') . ' cm' : '-' ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>BMI</th>
                                                                                        <td><?= $rp['bmi'] ? $rp['bmi'] . ' kg/m² (' . $rp['interpretasi_bmi'] . ')' : '-' ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Tekanan Darah</th>
                                                                                        <td><?= $rp['td'] ? $rp['td'] . ' mmHg' : '-' ?></td>
                                                                                    </tr>
                                                                                </table>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <table class="table table-sm">
                                                                                    <tr>
                                                                                        <th width="150">Ultrasonografi</th>
                                                                                        <td><?= $rp['ultra'] ?: '-' ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Keterangan Fisik</th>
                                                                                        <td><?= $rp['ket_fisik'] ?: '-' ?></td>
                                                                                    </tr>
                                                                                </table>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <table class="table table-sm">
                                                                                    <tr>
                                                                                        <th width="150">Laboratorium</th>
                                                                                        <td><?= $rp['lab'] ?: '-' ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Diagnosis</th>
                                                                                        <td><?= $rp['diagnosis'] ?: '-' ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Tatalaksana</th>
                                                                                        <td><?= $rp['tata'] ?: '-' ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Resep</th>
                                                                                        <td><?= $rp['resep'] ?: '-' ?></td>
                                                                                    </tr>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                                        <?php if (empty($rp['keluhan_utama'])): ?>
                                                                            <a href="index.php?module=rekam_medis&action=form_penilaian_medis_ralan_kandungan&no_rawat=<?= $rp['no_rawat'] ?>&source=<?= $_SESSION['source_page'] ?>" class="btn btn-primary">
                                                                                <i class="fas fa-plus"></i> Tambah Pemeriksaan
                                                                            </a>
                                                                        <?php else: ?>
                                                                            <a href="index.php?module=rekam_medis&action=edit_pemeriksaan&id=<?= $rp['no_rawat'] ?>&source=<?= $_SESSION['source_page'] ?>" class="btn btn-warning">
                                                                                <i class="fas fa-edit"></i> Edit Pemeriksaan
                                                                            </a>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    Belum ada riwayat kunjungan.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Status Ginekologi -->
    <div class="modal fade" id="modalGinekologi" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Status Ginekologi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formGinekologi" method="post" onsubmit="return false;">
                    <div class="modal-body">
                        <input type="hidden" name="no_rkm_medis" value="<?= $pasien['no_rkm_medis'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Parturien</label>
                            <input type="number" name="parturien" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Abortus</label>
                            <input type="number" name="abortus" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hari Pertama Haid Terakhir</label>
                            <input type="date" name="hpht" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kontrasepsi Terakhir</label>
                            <input type="text" name="kontrasepsi" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lama Menikah (Tahun)</label>
                            <input type="number" name="lama_menikah" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Surat -->
    <div class="modal fade" id="modalSurat" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSurat" method="post" onsubmit="return false;">
                    <div class="modal-body">
                        <input type="hidden" name="no_rkm_medis" value="<?= $pasien['no_rkm_medis'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Tanggal Surat</label>
                            <input type="date" name="tanggal_surat" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Surat</label>
                            <select name="jenis_surat" class="form-select" id="jenisSurat" required>
                                <option value="">-- Pilih Jenis Surat --</option>
                                <option value="skd">Surat Keterangan Dokter</option>
                                <option value="sakit">Surat Sakit</option>
                                <option value="rujukan">Surat Rujukan</option>
                            </select>
                        </div>

                        <!-- Field khusus untuk surat sakit -->
                        <div id="fieldSuratSakit" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Mulai Sakit</label>
                                <input type="date" name="mulai_sakit" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Selesai Sakit</label>
                                <input type="date" name="selesai_sakit" class="form-control">
                            </div>
                        </div>

                        <!-- Field khusus untuk surat dokter umum -->
                        <div id="fieldSuratDokter" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Keperluan</label>
                                <input type="text" name="keperluan" class="form-control" placeholder="Keperluan dibuatnya surat">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Diagnosa</label>
                            <input type="text" name="diagnosa" class="form-control" placeholder="Diagnosa pasien (opsional)">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan tambahan di surat"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dokter Pemeriksa</label>
                            <input type="text" name="dokter_pemeriksa" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk collapse/expand tab
        const tabToggles = document.querySelectorAll('[data-toggle="collapse"]');
        const tabPanes = document.querySelectorAll('.tab-pane');

        // Set initial state
        document.querySelector('#identitas').classList.add('show');
        document.querySelector('#identitas-tab').classList.remove('collapsed');

        tabToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                const targetPane = document.querySelector(targetId);

                // Close all other tabs
                tabPanes.forEach(pane => {
                    if (pane !== targetPane) {
                        pane.classList.remove('show');
                    }
                });

                tabToggles.forEach(t => {
                    if (t !== toggle) {
                        t.classList.add('collapsed');
                    }
                });

                // Toggle current tab
                this.classList.toggle('collapsed');
                targetPane.classList.toggle('show');
            });
        });

        // Script untuk memuat ulang halaman jika ada parameter refresh
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('refresh')) {
            const loadingMessage = document.createElement('div');
            loadingMessage.className = 'alert alert-info alert-dismissible fade show';
            loadingMessage.innerHTML = '<i class="fas fa-sync-alt me-2"></i> Memuat data terbaru...';
            document.querySelector('.card-body').prepend(loadingMessage);

            setTimeout(function() {
                const newUrl = window.location.href.replace(/[&?]refresh=\d+/, '');
                window.location.href = newUrl;
            }, 2000);
        }

        // Tambahkan parameter refresh ke tombol edit
        const editButton = document.querySelector('a[href*="action=editPasien"]');
        if (editButton) {
            editButton.addEventListener('click', function(e) {
                if (!this.href.includes('t=')) {
                    this.href += '&t=' + new Date().getTime();
                }
            });
        }

        // Fungsi untuk mengubah status
        document.querySelectorAll('.btn-status').forEach(button => {
            button.addEventListener('click', function() {
                const noRawat = this.getAttribute('data-no-rawat');
                const currentStatus = this.getAttribute('data-status');
                const newStatus = currentStatus === 'Sudah' ? 'Belum' : 'Sudah';

                fetch(`index.php?module=rekam_medis&action=update_status&no_rawat=${noRawat}`, {
                        method: 'GET'
                    })
                    .then(response => response.text())
                    .then(() => {
                        // Update tampilan tombol
                        this.setAttribute('data-status', newStatus);
                        this.textContent = newStatus;
                        this.classList.remove('btn-success', 'btn-danger');
                        this.classList.add(newStatus === 'Sudah' ? 'btn-success' : 'btn-danger');

                        // Tampilkan notifikasi
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show';
                        alert.innerHTML = `
                    Status berhasil diubah menjadi ${newStatus}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                        document.querySelector('.card-body').insertBefore(alert, document.querySelector('.card-body').firstChild);

                        // Hilangkan notifikasi setelah 3 detik
                        setTimeout(() => {
                            alert.remove();
                        }, 3000);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengubah status');
                    });
            });
        });

        $(document).ready(function() {
            // Handler untuk tombol Tambah Status Ginekologi
            $(document).on('click', '#tambahStatusGinekologi', function(e) {
                e.preventDefault();
                $('#modalGinekologi').modal('show');
            });

            // Handler untuk submit form
            $('#formGinekologi').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: 'index.php?module=rekam_medis&action=tambahStatusGinekologi',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    beforeSend: function() {
                        $('.modal-footer button').prop('disabled', true);
                        $('.modal-footer button[type="submit"]').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            $('#modalGinekologi').modal('hide');
                            $('#formGinekologi')[0].reset();

                            // Tampilkan alert sukses
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data status ginekologi berhasil disimpan',
                                allowOutsideClick: false
                            }).then((result) => {
                                // Refresh halaman dengan mempertahankan tab yang aktif
                                window.location.href = 'index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>#status-ginekologi';
                                location.reload();
                            });
                        } else {
                            // Tampilkan alert error
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Terjadi kesalahan saat menyimpan data'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        // Tampilkan alert error
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan pada server: ' + error
                        });
                    },
                    complete: function() {
                        $('.modal-footer button').prop('disabled', false);
                        $('.modal-footer button[type="submit"]').html('Simpan');
                    }
                });
            });
        });

        // Fungsi untuk memeriksa status sidebar dan menyesuaikan tampilan
        function checkSidebarState() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');

            if (sidebar && mainContent) {
                // Tambahkan CSS inline untuk memastikan main-content menyesuaikan dengan benar
                if (sidebar.classList.contains('minimized')) {
                    // Sidebar diminimalkan, sesuaikan margin-left main-content
                    mainContent.style.marginLeft = '60px';
                } else {
                    // Sidebar normal, kembalikan margin-left default
                    if (window.innerWidth <= 991.98) {
                        // Tampilan mobile
                        mainContent.style.marginLeft = '0';
                    } else {
                        // Tampilan desktop
                        mainContent.style.marginLeft = '280px';
                    }
                }
            }
        }

        // Periksa status sidebar saat halaman dimuat
        checkSidebarState();

        // Tambahkan event listener untuk tombol toggle sidebar
        const toggleButtons = document.querySelectorAll('#toggleSidebar, #toggleMobileSidebar');
        toggleButtons.forEach(button => {
            if (button) {
                button.addEventListener('click', function() {
                    // Beri waktu untuk CSS transition
                    setTimeout(checkSidebarState, 300);
                });
            }
        });

        // Tambahkan event listener untuk window resize
        window.addEventListener('resize', checkSidebarState);

        // Tambahkan MutationObserver untuk memantau perubahan pada sidebar
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class') {
                        // Sidebar class berubah, periksa statusnya
                        checkSidebarState();
                    }
                });
            });

            // Mulai observasi pada sidebar untuk perubahan atribut
            observer.observe(sidebar, {
                attributes: true
            });
        }

        // Handler untuk toggle berikutnya_gratis
        document.querySelectorAll('.toggle-gratis').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const noRm = this.getAttribute('data-no-rm');
                const isChecked = this.checked ? 1 : 0;
                const checkboxElement = this; // Simpan referensi ke checkbox
                const spinnerElement = this.parentNode.querySelector('.toggle-spinner');
                const statusTextElement = checkboxElement.closest('.header-buttons').querySelector('.gratis-status');

                // Update tampilan status text
                if (isChecked) {
                    statusTextElement.classList.add('active');
                } else {
                    statusTextElement.classList.remove('active');
                }

                // Tampilkan loading state
                checkboxElement.disabled = true;
                spinnerElement.classList.remove('d-none');

                // Tambahkan log untuk debugging
                console.log(`Mengirim request: no_rkm_medis=${noRm}, berikutnya_gratis=${isChecked}`);

                // Kirim data ke server
                fetch('index.php?module=rekam_medis&action=toggleBerikutnyaGratis', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `no_rkm_medis=${noRm}&berikutnya_gratis=${isChecked}`
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.status === 'success') {
                            // Tampilkan notifikasi kecil
                            const toast = document.createElement('div');
                            toast.classList.add('toast', 'position-fixed', 'bottom-0', 'end-0', 'm-3');
                            toast.setAttribute('role', 'alert');
                            toast.setAttribute('aria-live', 'assertive');
                            toast.setAttribute('aria-atomic', 'true');
                            toast.innerHTML = `
                            <div class="toast-header ${isChecked ? 'bg-success' : 'bg-secondary'} text-white">
                                <strong class="me-auto">Status Pasien</strong>
                                <small>baru saja</small>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <div class="toast-body">
                                Status "Berikutnya Gratis" untuk pasien 
                                <strong>${noRm}</strong> telah diubah menjadi 
                                <span class="badge ${isChecked ? 'bg-success' : 'bg-secondary'}">${isChecked ? 'AKTIF' : 'TIDAK AKTIF'}</span>
                            </div>
                        `;
                            document.body.appendChild(toast);

                            // Inisialisasi dan tampilkan toast
                            const bsToast = new bootstrap.Toast(toast);
                            bsToast.show();

                            // Tambahkan efek pulse pada toggle
                            checkboxElement.classList.add('toggle-success');
                            setTimeout(() => {
                                checkboxElement.classList.remove('toggle-success');
                            }, 500);

                            // Hapus toast setelah ditutup
                            toast.addEventListener('hidden.bs.toast', function() {
                                toast.remove();
                            });
                        } else {
                            // Kembalikan checkbox dan status text ke status sebelumnya jika gagal
                            checkboxElement.checked = !isChecked;
                            if (!isChecked) {
                                statusTextElement.classList.add('active');
                            } else {
                                statusTextElement.classList.remove('active');
                            }
                            alert('Gagal mengubah status: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Kembalikan checkbox dan status text ke status sebelumnya jika gagal
                        checkboxElement.checked = !isChecked;
                        if (!isChecked) {
                            statusTextElement.classList.add('active');
                        } else {
                            statusTextElement.classList.remove('active');
                        }
                        alert('Terjadi kesalahan, silakan coba lagi');
                    })
                    .finally(() => {
                        // Kembalikan state normal
                        checkboxElement.disabled = false;
                        spinnerElement.classList.add('d-none');
                    });
            });
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Fungsi untuk memuat data Riwayat Kehamilan menggunakan AJAX
        function loadRiwayatKehamilanData() {
            const noRkmMedis = '<?= $pasien['no_rkm_medis'] ?>';
            const riwayatKehamilanTableBody = document.getElementById('riwayatKehamilanTableBody');

            // Tampilkan loading
            riwayatKehamilanTableBody.innerHTML = '<tr><td colspan="10" class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data riwayat kehamilan...</td></tr>';

            console.log('Memuat data riwayat kehamilan untuk: ' + noRkmMedis);

            // Buat AJAX request
            fetch('index.php?module=rekam_medis&action=get_riwayat_kehamilan_ajax&no_rkm_medis=' + encodeURIComponent(noRkmMedis))
                .then(response => response.json())
                .then(data => {
                    console.log('Data riwayat kehamilan berhasil dimuat:', data);

                    if (data.status === 'success' && data.data && data.data.length > 0) {
                        let tableHtml = '';

                        data.data.forEach(function(rk) {
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

                        riwayatKehamilanTableBody.innerHTML = tableHtml;
                    } else {
                        riwayatKehamilanTableBody.innerHTML = '<tr><td colspan="10" class="text-center">Tidak ada data riwayat kehamilan</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error saat memuat data riwayat kehamilan:', error);
                    riwayatKehamilanTableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error: Gagal memuat data riwayat kehamilan</td></tr>';
                });
        }

        // Fungsi untuk memuat data Status Ginekologi menggunakan AJAX
        function loadStatusGinekologiData() {
            const noRkmMedis = '<?= $pasien['no_rkm_medis'] ?>';
            const statusGinekologiTableBody = document.getElementById('statusGinekologiTableBody');

            // Tampilkan loading
            statusGinekologiTableBody.innerHTML = '<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data status ginekologi...</td></tr>';

            console.log('Memuat data status ginekologi untuk: ' + noRkmMedis);

            // Buat AJAX request
            fetch('index.php?module=rekam_medis&action=get_status_ginekologi_ajax&no_rkm_medis=' + encodeURIComponent(noRkmMedis))
                .then(response => response.json())
                .then(data => {
                    console.log('Data status ginekologi berhasil dimuat:', data);

                    if (data.status === 'success' && data.data && data.data.length > 0) {
                        let tableHtml = '';

                        data.data.forEach(function(sg) {
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

                        statusGinekologiTableBody.innerHTML = tableHtml;
                    } else {
                        statusGinekologiTableBody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada data status ginekologi</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error saat memuat data status ginekologi:', error);
                    statusGinekologiTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error: Gagal memuat data status ginekologi</td></tr>';
                });
        }

        // Fungsi untuk memuat data Surat menggunakan AJAX
        function loadSuratData() {
            const noRkmMedis = '<?= $pasien['no_rkm_medis'] ?>';
            const suratTableBody = document.getElementById('suratTableBody');

            // Tampilkan loading
            suratTableBody.innerHTML = '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data surat...</td></tr>';

            console.log('Memuat data surat untuk: ' + noRkmMedis);

            // Buat AJAX request
            fetch('index.php?module=rekam_medis&action=get_surat_ajax&no_rkm_medis=' + encodeURIComponent(noRkmMedis))
                .then(response => response.json())
                .then(data => {
                    console.log('Data surat berhasil dimuat:', data);

                    if (data.status === 'success' && data.data && data.data.length > 0) {
                        let tableHtml = '';

                        data.data.forEach(function(surat) {
                            const tanggalSurat = new Date(surat.tanggal_surat).toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric'
                            });

                            // Format jenis surat untuk tampilan
                            let jenisSuratText = '';
                            switch(surat.jenis_surat) {
                                case 'skd':
                                    jenisSuratText = 'Surat Keterangan Dokter';
                                    break;
                                case 'sakit':
                                    jenisSuratText = 'Surat Sakit';
                                    break;
                                case 'rujukan':
                                    jenisSuratText = 'Surat Rujukan';
                                    break;
                                default:
                                    jenisSuratText = surat.jenis_surat;
                            }

                            tableHtml += `
                                <tr>
                                    <td>${surat.nomor_surat || '-'}</td>
                                    <td>${tanggalSurat}</td>
                                    <td>${jenisSuratText}</td>
                                    <td>${surat.diagnosa || '-'}</td>
                                    <td>${surat.dokter_pemeriksa || '-'}</td>
                                    <td>
                                        <a href="index.php?module=rekam_medis&action=edit_surat&id=${surat.id_surat}&source=<?= $_SESSION['source_page'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?module=rekam_medis&action=hapus_surat&id=${surat.id_surat}&source=<?= $_SESSION['source_page'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus surat ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <a href="index.php?module=rekam_medis&action=cetak_surat&id=${surat.id_surat}" class="btn btn-primary btn-sm" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                        });

                        suratTableBody.innerHTML = tableHtml;
                    } else {
                        suratTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Tidak ada data surat</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error saat memuat data surat:', error);
                    suratTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error: Gagal memuat data surat</td></tr>';
                });
        }

        // Muat data saat tab dibuka
        document.addEventListener('DOMContentLoaded', function() {
            // Modify tab click handlers to load data
            document.getElementById('status-ginekologi-tab').addEventListener('click', function() {
                loadStatusGinekologiData();
            });

            document.getElementById('riwayat-kehamilan-tab').addEventListener('click', function() {
                loadRiwayatKehamilanData();
            });

            document.getElementById('surat-tab').addEventListener('click', function() {
                loadSuratData();
            });

            // Handler untuk tombol Tambah Surat
            document.getElementById('tambahSurat').addEventListener('click', function() {
                $('#modalSurat').modal('show');
            });

            // Handler untuk perubahan jenis surat
            document.getElementById('jenisSurat').addEventListener('change', function() {
                const jenisSurat = this.value;
                const fieldSuratSakit = document.getElementById('fieldSuratSakit');
                const fieldSuratDokter = document.getElementById('fieldSuratDokter');

                // Sembunyikan semua field khusus
                fieldSuratSakit.style.display = 'none';
                fieldSuratDokter.style.display = 'none';

                // Tampilkan field sesuai jenis surat
                if (jenisSurat === 'sakit') {
                    fieldSuratSakit.style.display = 'block';
                } else if (jenisSurat === 'skd') {
                    fieldSuratDokter.style.display = 'block';
                }
            });

            // Handler untuk submit form surat
            $('#formSurat').on('submit', function(e) {
                e.preventDefault();

                // Debug: Log the form data before submitting
                console.log('Form data:', $(this).serialize());
                
                $.ajax({
                    url: 'index.php?module=rekam_medis&action=tambahSurat',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    beforeSend: function() {
                        console.log('Submitting form to:', 'index.php?module=rekam_medis&action=tambahSurat');
                        $('.modal-footer button').prop('disabled', true);
                        $('.modal-footer button[type="submit"]').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
                    },
                    success: function(response) {
                        console.log('Success response:', response);
                        if (response.status == 'success') {
                            $('#modalSurat').modal('hide');
                            $('#formSurat')[0].reset();

                            // Tampilkan alert sukses
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data surat berhasil disimpan',
                                allowOutsideClick: false
                            }).then((result) => {
                                // Refresh halaman dengan mempertahankan tab yang aktif
                                window.location.href = 'index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $pasien["no_rkm_medis"] ?>#surat';
                                location.reload();
                            });
                        } else {
                            // Tampilkan alert error
                            console.error('Error in response:', response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Terjadi kesalahan saat menyimpan data'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            statusCode: xhr.status,
                            statusText: xhr.statusText
                        });
                        
                        // Try to parse response if available
                        let errorMessage = 'Terjadi kesalahan saat menyimpan data';
                        try {
                            if (xhr.responseText) {
                                const errorResponse = JSON.parse(xhr.responseText);
                                if (errorResponse.message) {
                                    errorMessage = errorResponse.message;
                                }
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errorMessage
                        });
                    },
                    complete: function() {
                        console.log('Request completed');
                        $('.modal-footer button').prop('disabled', false);
                        $('.modal-footer button[type="submit"]').html('Simpan');
                    }
                });
            });

            // Jika ada hash di URL (contoh: #riwayat-kehamilan), buka tab yang sesuai dan muat datanya
            if (window.location.hash) {
                const targetTab = document.querySelector(`a[href="${window.location.hash}"]`);
                if (targetTab) {
                    // Klik tab secara programatis untuk membukanya
                    targetTab.click();

                    // Muat data untuk tab yang ditargetkan
                    if (window.location.hash === '#riwayat-kehamilan') {
                        setTimeout(loadRiwayatKehamilanData, 300); // Delay sebentar agar tab selesai dibuka
                    } else if (window.location.hash === '#status-ginekologi') {
                        setTimeout(loadStatusGinekologiData, 300); // Delay sebentar agar tab selesai dibuka
                    } else if (window.location.hash === '#surat') {
                        setTimeout(loadSuratData, 300); // Delay sebentar agar tab selesai dibuka
                    }
                }
            }

            // Inisialisasi kolom tabel resizable
            initResizableTable();
        });

        // Fungsi untuk membuat semua tabel menjadi resizable
        function makeAllTablesResizable() {
            // Pilih tabel yang belum memiliki class table-resizable
            const regularTables = document.querySelectorAll('.table:not(.table-resizable)');

            regularTables.forEach(table => {
                // Tambahkan class
                table.classList.add('table-resizable');

                // Tambahkan div.resizer ke setiap header tabel
                const headers = table.querySelectorAll('th');
                headers.forEach(th => {
                    // Periksa apakah sudah memiliki resizer
                    if (!th.querySelector('.resizer')) {
                        const resizer = document.createElement('div');
                        resizer.className = 'resizer';
                        th.appendChild(resizer);
                    }
                });
            });
        }

        // Fungsi untuk membuat kolom tabel dapat diubah ukurannya
        function initResizableTable() {
            const tables = document.querySelectorAll('.table-resizable');

            // Juga buat tabel lainnya menjadi resizable
            makeAllTablesResizable();

            tables.forEach(table => {
                const resizers = table.querySelectorAll('th .resizer');

                let startX, startWidth, currentTh, index;

                // Untuk setiap resizer di tabel
                resizers.forEach((resizer, i) => {
                    resizer.addEventListener('mousedown', function(e) {
                        e.preventDefault();

                        // Simpan referensi ke th saat ini dan indeksnya
                        currentTh = this.parentElement;
                        index = i;

                        // Catat posisi awal dan lebar awal
                        startX = e.pageX;
                        startWidth = currentTh.offsetWidth;

                        // Tambahkan class untuk styling saat mengubah ukuran
                        resizer.classList.add('resizing');

                        // Tambahkan event untuk gerakan mouse
                        document.addEventListener('mousemove', resize);
                        document.addEventListener('mouseup', stopResize);
                    });
                });

                // Fungsi untuk mengubah ukuran kolom
                function resize(e) {
                    if (!currentTh) return;

                    // Hitung lebar baru berdasarkan gerakan mouse
                    const diffX = e.pageX - startX;
                    const newWidth = Math.max(50, startWidth + diffX); // Minimal 50px lebar

                    // Ubah lebar kolom
                    currentTh.style.width = newWidth + 'px';

                    // Perbarui lebar kolom pada semua baris tabel
                    const allRows = table.querySelectorAll('tr');
                    allRows.forEach(row => {
                        const cell = row.cells[index];
                        if (cell) {
                            cell.style.width = newWidth + 'px';
                        }
                    });
                }

                // Fungsi untuk berhenti mengubah ukuran
                function stopResize() {
                    if (!currentTh) return;

                    // Hapus class untuk styling
                    const resizer = currentTh.querySelector('.resizer');
                    if (resizer) {
                        resizer.classList.remove('resizing');
                    }

                    // Bersihkan event listener
                    document.removeEventListener('mousemove', resize);
                    document.removeEventListener('mouseup', stopResize);

                    // Reset variabel
                    currentTh = null;
                }
            });
        }
    </script>
    <!-- Script untuk menangani tombol Tandai Selesai -->
    <script>
        function tandaiSelesai(idPendaftaran) {
            if (confirm('Apakah Anda yakin ingin menandai pendaftaran ini sebagai selesai?')) {
                const formData = new FormData();
                formData.append('id_pendaftaran', idPendaftaran);
                formData.append('status', 'Selesai');

                // Gunakan XMLHttpRequest untuk menangkap detail error lebih baik
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '/antrian pasien/modules/rekam_medis/controllers/update_status.php', true);
                
                // Menangani response
                xhr.onload = function() {
                    console.log('Status: ' + xhr.status);
                    console.log('Response Text: ' + xhr.responseText);
                    
                    if (xhr.status === 200) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            console.log('Parsed data:', data);
                            
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Status pendaftaran berhasil diubah menjadi Selesai',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    // Redirect ke halaman manajemen antrian
                                    window.location.href = '<?= BASE_URL ?>/index.php?module=rekam_medis&action=manajemen_antrian';
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: 'Gagal mengubah status pendaftaran: ' + data.message
                                });
                            }
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Terjadi kesalahan saat memproses respons dari server'
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Server merespons dengan status: ' + xhr.status
                        });
                    }
                };
                
                // Menangani error
                xhr.onerror = function() {
                    console.error('Request error');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat menghubungi server'
                    });
                };
                
                // Mengirim request
                xhr.send(formData);
            }
        }
    </script>
</body>

</html>