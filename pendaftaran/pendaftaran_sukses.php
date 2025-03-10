<?php
// Periksa apakah session sudah dimulai dengan cara yang kompatibel dengan berbagai versi PHP
if (function_exists('session_status')) {
    // PHP 5.4.0 atau lebih baru
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
} else {
    // PHP versi lama
    if (!headers_sent()) {
        @session_start();
    }
}

// Impor konfigurasi zona waktu
require_once '../config/timezone.php';
require_once '../config/database.php';
$page_title = "Pendaftaran Berhasil";

// Ambil ID pendaftaran dari parameter URL
$id_pendaftaran = isset($_GET['id']) ? $_GET['id'] : '';

// Cek apakah ada pesan sukses di session
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Hapus pesan dari session
}

// Jika tidak ada ID pendaftaran, redirect ke halaman pendaftaran
if (empty($id_pendaftaran)) {
    header("Location: form_pendaftaran_pasien.php");
    exit;
}

// Ambil data pendaftaran
$pendaftaran = null;
$tempat_praktek = null;
$dokter = null;
$jadwal = null;

try {
    // Query untuk mengambil data pendaftaran
    $query = "
        SELECT 
            p.*,
            tp.Nama_Tempat,
            tp.Alamat_Lengkap as Alamat_Tempat,
            d.Nama_Dokter,
            d.Spesialisasi,
            jr.Hari as hari
        FROM 
            pendaftaran p
        LEFT JOIN 
            tempat_praktek tp ON p.ID_Tempat_Praktek = tp.ID_Tempat_Praktek
        LEFT JOIN 
            dokter d ON p.ID_Dokter = d.ID_Dokter
        LEFT JOIN
            jadwal_rutin jr ON p.ID_Jadwal = jr.ID_Jadwal_Rutin
        WHERE 
            p.ID_Pendaftaran = :id_pendaftaran
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_pendaftaran', $id_pendaftaran);
    $stmt->execute();
    $pendaftaran = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika pendaftaran tidak ditemukan
    if (!$pendaftaran) {
        throw new Exception("Data pendaftaran tidak ditemukan untuk ID: " . $id_pendaftaran);
    }

    // Debug: Tampilkan data yang diambil
    error_log("Data pendaftaran: " . print_r($pendaftaran, true));

    // Ambil data jadwal
    $id_jadwal = $pendaftaran['ID_Jadwal'];

    if (empty($id_jadwal)) {
        error_log("ID Jadwal kosong untuk pendaftaran ID: " . $id_pendaftaran);
    }

    // Cek apakah jadwal adalah jadwal rutin atau khusus
    $query_jadwal_rutin = "SELECT * FROM jadwal_rutin WHERE ID_Jadwal_Rutin = :id_jadwal";
    $stmt_jadwal_rutin = $conn->prepare($query_jadwal_rutin);
    $stmt_jadwal_rutin->bindParam(':id_jadwal', $id_jadwal);
    $stmt_jadwal_rutin->execute();
    $jadwal_rutin = $stmt_jadwal_rutin->fetch(PDO::FETCH_ASSOC);

    if ($jadwal_rutin) {
        $jadwal = $jadwal_rutin;
        $jadwal['Jenis_Jadwal'] = 'Rutin';
        error_log("Jadwal Rutin ditemukan: " . print_r($jadwal_rutin, true));
    } else {
        $query_jadwal_khusus = "SELECT * FROM jadwal_praktek WHERE ID_Jadwal_Praktek = :id_jadwal";
        $stmt_jadwal_khusus = $conn->prepare($query_jadwal_khusus);
        $stmt_jadwal_khusus->bindParam(':id_jadwal', $id_jadwal);
        $stmt_jadwal_khusus->execute();
        $jadwal_khusus = $stmt_jadwal_khusus->fetch(PDO::FETCH_ASSOC);

        if ($jadwal_khusus) {
            $jadwal = $jadwal_khusus;
            $jadwal['Jenis_Jadwal'] = 'Khusus';
            error_log("Jadwal Khusus ditemukan: " . print_r($jadwal_khusus, true));
        } else {
            error_log("Tidak ada jadwal yang ditemukan untuk ID: " . $id_jadwal);
        }
    }
} catch (Exception $e) {
    error_log("Error detail: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $error_message = "Terjadi kesalahan saat mengambil data pendaftaran: " . $e->getMessage();
}

// Start output buffering
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <div class="text-center mt-4">
                    <a href="form_pendaftaran_pasien.php" class="btn btn-primary">Kembali ke Form Pendaftaran</a>
                </div>
            <?php else: ?>
                <div class="card shadow border-0">
                    <div class="card-header bg-success text-white text-center py-4">
                        <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i> Pendaftaran Berhasil</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success text-center mb-4">
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mb-4">
                            <p class="lead">Terima kasih telah melakukan pendaftaran. Berikut adalah detail pendaftaran Anda:</p>
                            <?php if (strpos($success_message, 'Data pasien telah diperbarui') !== false): ?>
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-info-circle"></i> Data pasien Anda telah diperbarui sesuai dengan informasi terbaru yang Anda berikan.
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title border-bottom pb-2 mb-3">Informasi Pendaftaran</h5>
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">ID Pendaftaran:</div>
                                            <div class="col-md-8"><?php echo htmlspecialchars($pendaftaran['ID_Pendaftaran']); ?></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Status:</div>
                                            <div class="col-md-8">
                                                <span class="badge bg-warning"><?php echo htmlspecialchars($pendaftaran['Status_Pendaftaran']); ?></span>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Waktu Pendaftaran:</div>
                                            <div class="col-md-8"><?php echo date('d-m-Y H:i', strtotime($pendaftaran['Waktu_Pendaftaran'])); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title border-bottom pb-2 mb-3">Data Pasien</h5>
                                        <div class="row mb-2">
                                            <div class="col-md-5 fw-bold">Nama:</div>
                                            <div class="col-md-7"><?php echo htmlspecialchars($pendaftaran['nm_pasien']); ?></div>
                                        </div>
                                        <?php if (!empty($pendaftaran['no_ktp'])): ?>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">NIK:</div>
                                                <div class="col-md-7"><?php echo htmlspecialchars($pendaftaran['no_ktp']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="row mb-2">
                                            <div class="col-md-5 fw-bold">Tanggal Lahir:</div>
                                            <div class="col-md-7"><?php echo date('d-m-Y', strtotime($pendaftaran['tgl_lahir'])); ?></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-5 fw-bold">Jenis Kelamin:</div>
                                            <div class="col-md-7"><?php echo htmlspecialchars($pendaftaran['jk']); ?></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-5 fw-bold">No. Telepon:</div>
                                            <div class="col-md-7"><?php echo htmlspecialchars($pendaftaran['no_tlp']); ?></div>
                                        </div>
                                        <?php if (!empty($pendaftaran['alamat'])): ?>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Alamat:</div>
                                                <div class="col-md-7"><?php echo htmlspecialchars($pendaftaran['alamat']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title border-bottom pb-2 mb-3">Informasi Kunjungan</h5>
                                        <div class="row mb-2">
                                            <div class="col-md-5 fw-bold">Tempat Praktek:</div>
                                            <div class="col-md-7"><?php echo htmlspecialchars($pendaftaran['Nama_Tempat']); ?></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-5 fw-bold">Dokter:</div>
                                            <div class="col-md-7">
                                                <?php echo htmlspecialchars($pendaftaran['Nama_Dokter']); ?>
                                            </div>
                                        </div>
                                        <?php if (isset($pendaftaran['hari']) && !empty($pendaftaran['hari'])): ?>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Hari:</div>
                                                <div class="col-md-7">
                                                    <?php echo htmlspecialchars($pendaftaran['hari']); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="row mb-2">
                                            <div class="col-md-5 fw-bold">Tanggal Daftar:</div>
                                            <div class="col-md-7"><?php echo date('d-m-Y', strtotime($pendaftaran['Waktu_Pendaftaran'])); ?></div>
                                        </div>
                                        <?php if ($jadwal): ?>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Waktu Praktek:</div>
                                                <div class="col-md-7">
                                                    <?php echo date('H:i', strtotime($jadwal['Jam_Mulai'])); ?> -
                                                    <?php echo date('H:i', strtotime($jadwal['Jam_Selesai'])); ?>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Jenis Layanan:</div>
                                                <div class="col-md-7"><?php echo htmlspecialchars($jadwal['Jenis_Layanan']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($pendaftaran['Keluhan'])): ?>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title border-bottom pb-2 mb-3">Keluhan</h5>
                                            <p><?php echo nl2br(htmlspecialchars($pendaftaran['Keluhan'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-info">
                            <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Silakan simpan ID pendaftaran Anda untuk keperluan pemeriksaan status pendaftaran.</p>
                        </div>

                        <div class="text-center mt-4">
                            <a href="form_pendaftaran_pasien.php" class="btn btn-secondary me-2">Kembali ke Form Pendaftaran</a>
                            <a href="antrian.php" class="btn btn-primary">Lihat Antrian</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Buat teks WhatsApp dengan data pendaftaran
$whatsapp_text = "Halo Admin, saya telah melakukan pendaftaran dengan detail berikut:\n\n";
$whatsapp_text .= "ID Pendaftaran: " . $pendaftaran['ID_Pendaftaran'] . "\n";
$whatsapp_text .= "Nama: " . $pendaftaran['nm_pasien'] . "\n";
if (!empty($pendaftaran['no_ktp'])) {
    $whatsapp_text .= "NIK: " . $pendaftaran['no_ktp'] . "\n";
}
$whatsapp_text .= "Tanggal Lahir: " . date('d-m-Y', strtotime($pendaftaran['tgl_lahir'])) . "\n";
$whatsapp_text .= "Jenis Kelamin: " . $pendaftaran['jk'] . "\n";
$whatsapp_text .= "No. Telepon: " . $pendaftaran['no_tlp'] . "\n";
if (!empty($pendaftaran['alamat'])) {
    $whatsapp_text .= "Alamat: " . $pendaftaran['alamat'] . "\n";
}
$whatsapp_text .= "\nInformasi Kunjungan:\n";
$whatsapp_text .= "Tempat Praktek: " . $pendaftaran['Nama_Tempat'] . "\n";
$whatsapp_text .= "Dokter: " . $pendaftaran['Nama_Dokter'] . "\n";
if (isset($pendaftaran['hari']) && !empty($pendaftaran['hari'])) {
    $whatsapp_text .= "Hari: " . $pendaftaran['hari'] . "\n";
}
$whatsapp_text .= "Tanggal Daftar: " . date('d-m-Y', strtotime($pendaftaran['Waktu_Pendaftaran'])) . "\n";
if ($jadwal) {
    $whatsapp_text .= "Waktu Praktek: " . date('H:i', strtotime($jadwal['Jam_Mulai'])) . " - " . date('H:i', strtotime($jadwal['Jam_Selesai'])) . "\n";
    $whatsapp_text .= "Jenis Layanan: " . $jadwal['Jenis_Layanan'] . "\n";
}
if (!empty($pendaftaran['Keluhan'])) {
    $whatsapp_text .= "\nKeluhan: " . $pendaftaran['Keluhan'] . "\n";
}
$whatsapp_text .= "\nMohon konfirmasi pendaftaran saya. Terima kasih.";

// Encode teks untuk URL
$whatsapp_text_encoded = urlencode($whatsapp_text);

// Tambahkan floating WhatsApp icon
$whatsapp_html = '
<div class="floating-whatsapp">
    <a href="https://wa.me/6285190086842?text=' . $whatsapp_text_encoded . '" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>
    <span class="tooltip-text">Hubungi Admin via WhatsApp</span>
</div>
';

// Gabungkan konten dengan floating WhatsApp icon
$content .= $whatsapp_html;

// Additional CSS
$additional_css = "
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
    .card-header {
        background-color: #198754;
    }
    .border-bottom {
        border-bottom: 2px solid #dee2e6 !important;
        margin-bottom: 1rem;
    }
    .fw-bold {
        font-weight: 600;
    }
    
    /* Floating WhatsApp Icon */
    .floating-whatsapp {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }
    .floating-whatsapp a {
        display: block;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: #25D366;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 30px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .floating-whatsapp a:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
    }
    .floating-whatsapp .tooltip-text {
        position: absolute;
        right: 70px;
        background-color: #333;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
        visibility: hidden;
        opacity: 0;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    .floating-whatsapp:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }
";

// Include template
include_once '../template/layout.php';
?>