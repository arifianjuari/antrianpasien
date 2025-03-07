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

// Tambahkan style di bagian atas file
?>
<style>
    .tab-pane {
        transition: all 0.3s ease-in-out;
        overflow: hidden;
        font-size: 0.8rem;
        /* Mengurangi ukuran font secara global */
    }

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
                <div class="card-header">
                    <h3 class="card-title">Detail Rekam Medis Pasien</h3>
                    <div class="card-tools">
                        <a href="index.php?module=rekam_medis&action=generate_pdf&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf"></i> Download Resume PDF
                        </a>
                        <a href="index.php?module=rekam_medis" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
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
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        <!-- Tab Identitas -->
                        <div class="tab-pane fade collapse" id="identitas" role="tabpanel">
                            <div class="mb-3 d-flex justify-content-end">
                                <a href="index.php?module=rekam_medis&action=editPasien&id=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit Data Pasien
                                </a>
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
                                                    <th width="140" class="text-muted px-3">No. Rekam Medis</th>
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
                                                    <th class="text-muted px-3">Tempat Lahir</th>
                                                    <td class="px-3"><?= $pasien['tmp_lahir'] ?></td>
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
                                                    <th class="text-muted px-3">Kabupaten</th>
                                                    <td class="px-3">
                                                        <?php
                                                        $nama_kabupaten = '-';
                                                        foreach ($kabupaten as $kab) {
                                                            if ($kab['kd_kab'] == $pasien['kd_kab']) {
                                                                $nama_kabupaten = $kab['nm_kab'];
                                                                break;
                                                            }
                                                        }
                                                        echo $nama_kabupaten;
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">No. Telepon</th>
                                                    <td class="px-3"><?= $pasien['no_tlp'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">Pendidikan</th>
                                                    <td class="px-3"><?= $pasien['pnd'] ?? '-' ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">Pekerjaan</th>
                                                    <td class="px-3"><?= $pasien['pekerjaan'] ?? '-' ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">Status Nikah</th>
                                                    <td class="px-3"><?= $pasien['stts_nikah'] ?? '-' ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">Agama</th>
                                                    <td class="px-3"><?= $pasien['agama'] ?? '-' ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">Golongan Darah</th>
                                                    <td class="px-3"><?= $pasien['gol_darah'] ?? '-' ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Informasi Registrasi -->
                                    <div class="card mt-3">
                                        <div class="card-header bg-light">
                                            <h6 class="card-title mb-0" style="font-size: 0.9rem;">Informasi Registrasi</h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-sm table-hover" style="font-size: 0.85rem;">
                                                <tr>
                                                    <th width="140" class="text-muted px-3">Cara Bayar</th>
                                                    <td class="px-3">
                                                        <?php
                                                        $cara_bayar = '';
                                                        switch ($pasien['kd_pj']) {
                                                            case 'UMU':
                                                                $cara_bayar = 'Umum';
                                                                break;
                                                            case 'BPJ':
                                                                $cara_bayar = 'BPJS';
                                                                break;
                                                            case 'ASR':
                                                                $cara_bayar = 'Asuransi';
                                                                break;
                                                            case 'KOR':
                                                                $cara_bayar = 'Korporasi';
                                                                break;
                                                            default:
                                                                $cara_bayar = $pasien['kd_pj'];
                                                        }
                                                        echo $cara_bayar;
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted px-3">Tgl Pendaftaran</th>
                                                    <td class="px-3"><?= date('d-m-Y', strtotime($pasien['tgl_daftar'])) ?></td>
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
                                    <a href="index.php?module=rekam_medis&action=tambah_status_obstetri&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-primary btn-sm">
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
                                                        <a href="index.php?module=rekam_medis&action=edit_status_obstetri&id=<?= $so['id_status_obstetri'] ?>" class="btn btn-warning btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="index.php?module=rekam_medis&action=hapus_status_obstetri&id=<?= $so['id_status_obstetri'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
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
                                <a href="index.php?module=rekam_medis&action=tambah_riwayat_kehamilan&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Riwayat
                                </a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Status</th>
                                            <th>Jenis</th>
                                            <th>Tempat</th>
                                            <th>Penolong</th>
                                            <th>Tanggal</th>
                                            <th>Jenis Kelamin</th>
                                            <th>BB</th>
                                            <th>Kondisi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (isset($riwayatKehamilan) && count($riwayatKehamilan) > 0): ?>
                                            <?php foreach ($riwayatKehamilan as $rk): ?>
                                                <tr>
                                                    <td><?= $rk['no_urut_kehamilan'] ?></td>
                                                    <td><?= $rk['status_kehamilan'] ?></td>
                                                    <td><?= $rk['jenis_persalinan'] ?? '-' ?></td>
                                                    <td><?= $rk['tempat_persalinan'] ?? '-' ?></td>
                                                    <td><?= $rk['penolong_persalinan'] ?? '-' ?></td>
                                                    <td><?= !empty($rk['tanggal_persalinan']) ? date('d-m-Y', strtotime($rk['tanggal_persalinan'])) : '-' ?></td>
                                                    <td><?= $rk['jenis_kelamin_anak'] ?? '-' ?></td>
                                                    <td><?= $rk['berat_badan_lahir'] ?? '-' ?></td>
                                                    <td><?= $rk['kondisi_lahir'] ?? '-' ?></td>
                                                    <td>
                                                        <a href="index.php?module=rekam_medis&action=edit_riwayat_kehamilan&id=<?= $rk['id_riwayat_kehamilan'] ?>" class="btn btn-warning btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="index.php?module=rekam_medis&action=hapus_riwayat_kehamilan&id=<?= $rk['id_riwayat_kehamilan'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="10" class="text-center">Tidak ada data riwayat kehamilan</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Status Ginekologi -->
                        <div class="tab-pane fade" id="status-ginekologi" role="tabpanel">
                            <div class="d-flex justify-content-end mb-3">
                                <a href="index.php?module=rekam_medis&action=tambah_status_ginekologi&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Status Ginekologi
                                </a>
                            </div>
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
                                    <tbody>
                                        <?php if (isset($statusGinekologi) && count($statusGinekologi) > 0): ?>
                                            <?php foreach ($statusGinekologi as $sg): ?>
                                                <tr>
                                                    <td><?= date('d-m-Y', strtotime($sg['created_at'])) ?></td>
                                                    <td><?= $sg['Parturien'] ?? '-' ?></td>
                                                    <td><?= $sg['Abortus'] ?? '-' ?></td>
                                                    <td><?= $sg['Hari_pertama_haid_terakhir'] ? date('d-m-Y', strtotime($sg['Hari_pertama_haid_terakhir'])) : '-' ?></td>
                                                    <td><?= $sg['Kontrasepsi_terakhir'] ?? '-' ?></td>
                                                    <td><?= $sg['lama_menikah_th'] ?? '-' ?></td>
                                                    <td>
                                                        <?php error_log("ID status ginekologi: " . $sg['id_status_ginekologi']); ?>
                                                        <a href="index.php?module=rekam_medis&action=edit_status_ginekologi&id=<?= $sg['id_status_ginekologi'] ?>" class="btn btn-warning btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="index.php?module=rekam_medis&action=hapus_status_ginekologi&id=<?= $sg['id_status_ginekologi'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ada data status ginekologi</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Kunjungan & Pemeriksaan -->
                    <div class="riwayat-section mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Riwayat Kunjungan & Pemeriksaan</h5>
                            <a href="index.php?module=rekam_medis&action=tambah_pemeriksaan&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-primary btn-sm">
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
                            <?php foreach ($riwayatPemeriksaan as $index => $rp): ?>
                                <div class="riwayat-item">
                                    <div class="riwayat-header" onclick="toggleRiwayat(<?= $index ?>)">
                                        <div>
                                            <strong><?= date('d-m-Y', strtotime($rp['tgl_registrasi'])) ?> <?= $rp['jam_reg'] ?></strong>
                                            <?php if (!empty($rp['nm_dokter'])): ?>
                                                - Dr. <?= $rp['nm_dokter'] ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-chevron-down riwayat-toggle" id="toggle-<?= $index ?>"></i>
                                        </div>
                                    </div>
                                    <div class="riwayat-content" id="content-<?= $index ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <th width="150">No. Rawat</th>
                                                        <td><?= $rp['no_rawat'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status Bayar</th>
                                                        <td><?= $rp['status_bayar'] ?></td>
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
                                        <div class="mt-3">
                                            <h6 class="mb-2">Hasil Pemeriksaan<?= $rp['tgl_pemeriksaan'] ? ': ' . date('d-m-Y H:i:s', strtotime($rp['tgl_pemeriksaan'])) : '' ?></h6>
                                            <!-- Debug info -->
                                            <?php if (isset($_GET['debug'])): ?>
                                                <div class="alert alert-info">
                                                    <pre><?php print_r($rp); ?></pre>
                                                </div>
                                            <?php endif; ?>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <th width="150">BB/TB</th>
                                                            <td><?= ($rp['bb'] || $rp['tb']) ? ($rp['bb'] ?: '-') . ' kg / ' . ($rp['tb'] ?: '-') . ' cm' : '-' ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Tekanan Darah</th>
                                                            <td><?= $rp['td'] ? $rp['td'] . ' mmHg' : '-' ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Nadi</th>
                                                            <td><?= $rp['nadi'] ? $rp['nadi'] . ' x/menit' : '-' ?></td>
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
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2 d-flex justify-content-between">
                                            <div>
                                                <a href="index.php?module=rekam_medis&action=edit_kunjungan&no_rawat=<?= $rp['no_rawat'] ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i> Edit Kunjungan
                                                </a>
                                                <a href="index.php?module=rekam_medis&action=hapus_kunjungan&no_rawat=<?= $rp['no_rawat'] ?>" class="btn btn-danger btn-sm ms-1" onclick="return confirm('Apakah Anda yakin ingin menghapus kunjungan ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </div>
                                            <div>
                                                <?php if (empty($rp['keluhan_utama'])): ?>
                                                    <a href="index.php?module=rekam_medis&action=form_penilaian_medis_ralan_kandungan&no_rawat=<?= $rp['no_rawat'] ?>" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-plus"></i> Tambah Pemeriksaan
                                                    </a>
                                                <?php else: ?>
                                                    <a href="index.php?module=rekam_medis&action=edit_pemeriksaan&id=<?= $rp['no_rawat'] ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i> Edit Pemeriksaan
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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

    // Tambahkan fungsi untuk toggle riwayat
    function toggleRiwayat(index) {
        const content = document.getElementById(`content-${index}`);
        const toggle = document.getElementById(`toggle-${index}`);

        if (content.style.display === 'block') {
            content.style.display = 'none';
            toggle.classList.remove('active');
        } else {
            content.style.display = 'block';
            toggle.classList.add('active');
        }
    }

    // Tampilkan riwayat pertama saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        const firstContent = document.getElementById('content-0');
        const firstToggle = document.getElementById('toggle-0');
        if (firstContent && firstToggle) {
            firstContent.style.display = 'block';
            firstToggle.classList.add('active');
        }
    });

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
</script>