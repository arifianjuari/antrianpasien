<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Declare global connection variable
global $conn;

// Dapatkan root directory project
$root_dir = dirname(dirname(dirname(__DIR__)));

// Include database configuration if not already included
if (!isset($conn) || !($conn instanceof PDO)) {
    require_once $root_dir . '/config/database.php';
}

// Include base URL configuration if not already included
if (!isset($base_url)) {
    require_once $root_dir . '/config/config.php';
}

// Log status koneksi
error_log("Checking database connection in manajemen_antrian.php");

// Cek koneksi database
if (!isset($conn) || !($conn instanceof PDO)) {
    error_log("Database connection not available in manajemen_antrian.php");
    die("Koneksi database tidak tersedia. Silakan hubungi administrator.");
}

try {
    // Test koneksi
    $test = $conn->query("SELECT 1");
    if (!$test) {
        throw new PDOException("Koneksi database tidak dapat melakukan query");
    }
    error_log("Database connection test successful in manajemen_antrian.php");
} catch (PDOException $e) {
    error_log("Database test failed in manajemen_antrian.php: " . $e->getMessage());
    die("Koneksi database bermasalah: " . $e->getMessage());
}

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Filter dan pengurutan
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'waktu_desc';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Filter default: tampilkan semua kecuali yang dibatalkan dan selesai
$default_filter = true;
if (isset($_GET['clear_filter']) && $_GET['clear_filter'] == '1') {
    $default_filter = false;
}

// Query untuk mengambil data pendaftaran dengan join ke tabel pasien
try {
    $query = "
        SELECT 
            p.ID_Pendaftaran,
            pas.no_rkm_medis,
            p.nm_pasien as Nama_Pasien,
            p.Keluhan,
            p.Status_Pendaftaran,
            p.Waktu_Pendaftaran,
            jr.Hari,
            jr.Jam_Mulai,
            jr.Jam_Selesai,
            jr.Jenis_Layanan,
            tp.Nama_Tempat,
            d.Nama_Dokter,
            pas.no_tlp
        FROM 
            pendaftaran p
        JOIN pasien pas ON p.no_ktp = pas.no_ktp
        JOIN jadwal_rutin jr ON p.ID_Jadwal = jr.ID_Jadwal_Rutin
        JOIN tempat_praktek tp ON p.ID_Tempat_Praktek = tp.ID_Tempat_Praktek
        JOIN dokter d ON p.ID_Dokter = d.ID_Dokter
        WHERE 1=1
    ";

    if (!empty($status_filter)) {
        $query .= " AND p.Status_Pendaftaran = :status";
    } else if ($default_filter) {
        $query .= " AND p.Status_Pendaftaran NOT IN ('Dibatalkan', 'Selesai')";
    }

    if (!empty($search)) {
        $query .= " AND (p.nm_pasien LIKE :search OR p.ID_Pendaftaran LIKE :search OR pas.no_rkm_medis LIKE :search)";
    }

    // Filter berdasarkan hari
    if (!empty($_GET['hari'])) {
        $query .= " AND jr.Hari = :hari";
    }

    // Filter berdasarkan dokter
    if (!empty($_GET['dokter'])) {
        $query .= " AND d.Nama_Dokter = :dokter";
    }

    // Filter berdasarkan tempat
    if (!empty($_GET['tempat'])) {
        $query .= " AND tp.Nama_Tempat = :tempat";
    }

    switch ($sort_by) {
        case 'waktu_asc':
            $query .= " ORDER BY p.Waktu_Pendaftaran ASC";
            break;
        case 'nama_asc':
            $query .= " ORDER BY pas.nm_pasien ASC";
            break;
        case 'nama_desc':
            $query .= " ORDER BY pas.nm_pasien DESC";
            break;
        case 'status_asc':
            $query .= " ORDER BY p.Status_Pendaftaran ASC";
            break;
        case 'status_desc':
            $query .= " ORDER BY p.Status_Pendaftaran DESC";
            break;
        case 'hari_asc':
            $query .= " ORDER BY CASE jr.Hari 
                        WHEN 'Senin' THEN 1 
                        WHEN 'Selasa' THEN 2 
                        WHEN 'Rabu' THEN 3 
                        WHEN 'Kamis' THEN 4 
                        WHEN 'Jumat' THEN 5 
                        WHEN 'Sabtu' THEN 6 
                        WHEN 'Minggu' THEN 7 
                        ELSE 8 END ASC, jr.Jam_Mulai ASC";
            break;
        case 'waktu_desc':
        default:
            $query .= " ORDER BY p.Waktu_Pendaftaran DESC";
            break;
    }

    $stmt = $conn->prepare($query);

    if (!empty($status_filter)) {
        $stmt->bindParam(':status', $status_filter);
    }

    if (!empty($search)) {
        $search_param = "%$search%";
        $stmt->bindParam(':search', $search_param);
    }

    // Bind parameter untuk filter hari
    if (!empty($_GET['hari'])) {
        $stmt->bindParam(':hari', $_GET['hari']);
    }

    // Bind parameter untuk filter dokter
    if (!empty($_GET['dokter'])) {
        $stmt->bindParam(':dokter', $_GET['dokter']);
    }

    // Bind parameter untuk filter tempat
    if (!empty($_GET['tempat'])) {
        $stmt->bindParam(':tempat', $_GET['tempat']);
    }

    $stmt->execute();
    $antrian = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $antrian = [];
}

// Hitung jumlah antrian berdasarkan status
try {
    $query_count = "
        SELECT 
            Status_Pendaftaran, 
            COUNT(*) as jumlah 
        FROM 
            pendaftaran 
        GROUP BY 
            Status_Pendaftaran
    ";
    $stmt_count = $conn->query($query_count);
    $status_counts = [];

    while ($row = $stmt_count->fetch(PDO::FETCH_ASSOC)) {
        $status_counts[$row['Status_Pendaftaran']] = $row['jumlah'];
    }

    $total_antrian = 0;
    foreach ($status_counts as $status => $count) {
        if ($status !== 'Dibatalkan' && $status !== 'Selesai') {
            $total_antrian += $count;
        }
    }

    $total_keseluruhan = array_sum($status_counts);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $status_counts = [];
    $total_antrian = 0;
    $total_keseluruhan = 0;
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <!-- Statistik Antrian -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card shadow-sm">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-2 col-6 text-center mb-2 mb-md-0">
                                            <div class="bg-light rounded p-2 stat-box">
                                                <h6 class="mb-0">Total Aktif</h6>
                                                <h3 class="mb-0"><?= $total_antrian ?></h3>
                                                <small>Pendaftaran Aktif</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-6 text-center mb-2 mb-md-0">
                                            <div class="bg-warning bg-opacity-25 rounded p-2 stat-box">
                                                <h6 class="mb-0">Menunggu</h6>
                                                <h3 class="mb-0"><?= isset($status_counts['Menunggu Konfirmasi']) ? $status_counts['Menunggu Konfirmasi'] : 0 ?></h3>
                                                <small>Konfirmasi</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-6 text-center mb-2 mb-md-0">
                                            <div class="bg-success bg-opacity-25 rounded p-2 stat-box">
                                                <h6 class="mb-0">Dikonfirmasi</h6>
                                                <h3 class="mb-0"><?= isset($status_counts['Dikonfirmasi']) ? $status_counts['Dikonfirmasi'] : 0 ?></h3>
                                                <small>Pendaftaran</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-6 text-center mb-2 mb-md-0">
                                            <div class="bg-info bg-opacity-25 rounded p-2 stat-box">
                                                <h6 class="mb-0">Selesai</h6>
                                                <h3 class="mb-0"><?= isset($status_counts['Selesai']) ? $status_counts['Selesai'] : 0 ?></h3>
                                                <small>Pendaftaran</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-6 text-center mb-2 mb-md-0">
                                            <div class="bg-secondary bg-opacity-25 rounded p-2 stat-box">
                                                <h6 class="mb-0">Total Semua</h6>
                                                <h3 class="mb-0"><?= $total_keseluruhan ?></h3>
                                                <small>Semua Pendaftaran</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-6 text-center mb-2 mb-md-0">
                                            <div class="bg-danger bg-opacity-25 rounded p-2 stat-box">
                                                <h6 class="mb-0">Dibatalkan</h6>
                                                <h3 class="mb-0"><?= isset($status_counts['Dibatalkan']) ? $status_counts['Dibatalkan'] : 0 ?></h3>
                                                <small>Pendaftaran</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter dan Pengurutan -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <form method="GET" class="d-flex gap-2 filter-form">
                                <input type="hidden" name="module" value="rekam_medis">
                                <input type="hidden" name="action" value="manajemen_antrian">
                                <div class="search-container">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Cari nama pasien, No.RM, atau ID pendaftaran..."
                                        value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="filter-container">
                                    <select name="status" class="form-select">
                                        <option value="" <?= $status_filter === '' ? 'selected' : '' ?>>Semua Status</option>
                                        <option value="Menunggu Konfirmasi" <?= $status_filter === 'Menunggu Konfirmasi' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                                        <option value="Dikonfirmasi" <?= $status_filter === 'Dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                                        <option value="Dibatalkan" <?= $status_filter === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                        <option value="Selesai" <?= $status_filter === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                    </select>
                                </div>
                                <div class="filter-container">
                                    <select name="hari" class="form-select">
                                        <option value="">Semua Hari</option>
                                        <option value="Senin">Senin</option>
                                        <option value="Selasa">Selasa</option>
                                        <option value="Rabu">Rabu</option>
                                        <option value="Kamis">Kamis</option>
                                        <option value="Jumat">Jumat</option>
                                        <option value="Sabtu">Sabtu</option>
                                        <option value="Minggu">Minggu</option>
                                    </select>
                                </div>
                                <div class="filter-container">
                                    <select name="dokter" class="form-select">
                                        <option value="">Semua Dokter</option>
                                        <?php
                                        $query_dokter = "SELECT DISTINCT Nama_Dokter FROM dokter WHERE Status_Aktif = 1";
                                        $stmt_dokter = $conn->query($query_dokter);
                                        while ($row = $stmt_dokter->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='" . htmlspecialchars($row['Nama_Dokter']) . "'>" . htmlspecialchars($row['Nama_Dokter']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="filter-container">
                                    <select name="tempat" class="form-select">
                                        <option value="">Semua Tempat</option>
                                        <?php
                                        $query_tempat = "SELECT DISTINCT Nama_Tempat FROM tempat_praktek WHERE Status_Aktif = 1";
                                        $stmt_tempat = $conn->query($query_tempat);
                                        while ($row = $stmt_tempat->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='" . htmlspecialchars($row['Nama_Tempat']) . "'>" . htmlspecialchars($row['Nama_Tempat']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="filter-container">
                                    <select name="sort" class="form-select">
                                        <option value="waktu_desc" <?= $sort_by === 'waktu_desc' ? 'selected' : '' ?>>Waktu Terbaru</option>
                                        <option value="waktu_asc" <?= $sort_by === 'waktu_asc' ? 'selected' : '' ?>>Waktu Terlama</option>
                                        <option value="nama_asc" <?= $sort_by === 'nama_asc' ? 'selected' : '' ?>>Nama (A-Z)</option>
                                        <option value="nama_desc" <?= $sort_by === 'nama_desc' ? 'selected' : '' ?>>Nama (Z-A)</option>
                                        <option value="status_asc" <?= $sort_by === 'status_asc' ? 'selected' : '' ?>>Status (A-Z)</option>
                                        <option value="status_desc" <?= $sort_by === 'status_desc' ? 'selected' : '' ?>>Status (Z-A)</option>
                                        <option value="hari_asc" <?= $sort_by === 'hari_asc' ? 'selected' : '' ?>>Hari (Senin-Minggu)</option>
                                    </select>
                                </div>
                                <div class="button-container">
                                    <button type="submit" class="btn btn-primary btn-icon" data-bs-toggle="tooltip" title="Cari">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <?php if (!empty($search) || !empty($status_filter) || $sort_by !== 'waktu_desc'): ?>
                                    <div class="button-container">
                                        <a href="index.php?module=rekam_medis&action=manajemen_antrian" class="btn btn-secondary btn-icon" data-bs-toggle="tooltip" title="Reset Filter">
                                            <i class="bi bi-x-circle"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if ($default_filter): ?>
                                    <div class="button-container">
                                        <a href="index.php?module=rekam_medis&action=manajemen_antrian&clear_filter=1" class="btn btn-info btn-icon" data-bs-toggle="tooltip" title="Tampilkan Semua Data">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="button-container">
                                        <a href="index.php?module=rekam_medis&action=manajemen_antrian" class="btn btn-warning btn-icon" data-bs-toggle="tooltip" title="Sembunyikan Data Selesai/Dibatalkan">
                                            <i class="bi bi-filter"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="<?php echo $base_url; ?>/pendaftaran/form_pendaftaran_pasien.php" class="btn btn-primary btn-icon me-2" data-bs-toggle="tooltip" title="Tambah Pendaftaran Baru">
                                <i class="bi bi-plus-circle"></i>
                            </a>
                            <button type="button" class="btn btn-success btn-icon" onclick="refreshPage()" data-bs-toggle="tooltip" title="Refresh Data">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>

                    <?php if (empty($antrian)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Tidak ada data antrian saat ini.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Aksi</th>
                                        <th>Nama Pasien</th>
                                        <th>Waktu Daftar</th>
                                        <th>Keluhan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($antrian as $a): ?>
                                        <tr>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <!-- Tombol untuk melihat rekam medis -->
                                                    <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $a['no_rkm_medis'] ?>&source=antrian"
                                                        class="btn btn-primary btn-sm btn-icon" data-bs-toggle="tooltip"
                                                        title="Lihat Rekam Medis">
                                                        <i class="bi bi-journal-medical"></i>
                                                    </a>

                                                    <?php if ($a['Status_Pendaftaran'] !== 'Menunggu Konfirmasi'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning btn-icon"
                                                            onclick="updateStatusDirect('<?= $a['ID_Pendaftaran'] ?>', 'Menunggu Konfirmasi')"
                                                            data-bs-toggle="tooltip" title="Ubah ke Menunggu Konfirmasi">
                                                            <i class="bi bi-hourglass"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($a['Status_Pendaftaran'] !== 'Dikonfirmasi'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success btn-icon"
                                                            onclick="updateStatusDirect('<?= $a['ID_Pendaftaran'] ?>', 'Dikonfirmasi')"
                                                            data-bs-toggle="tooltip" title="Konfirmasi Pendaftaran">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($a['Status_Pendaftaran'] !== 'Selesai'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-info btn-icon"
                                                            onclick="updateStatusDirect('<?= $a['ID_Pendaftaran'] ?>', 'Selesai')"
                                                            data-bs-toggle="tooltip" title="Tandai Selesai">
                                                            <i class="bi bi-flag"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($a['Status_Pendaftaran'] !== 'Dibatalkan'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-icon"
                                                            onclick="updateStatusDirect('<?= $a['ID_Pendaftaran'] ?>', 'Dibatalkan')"
                                                            data-bs-toggle="tooltip" title="Batalkan Pendaftaran">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if (!empty($a['no_tlp'])): ?>
                                                        <?php
                                                        // Bersihkan nomor telepon dari karakter non-numerik
                                                        $no_tlp_clean = preg_replace('/[^0-9]/', '', $a['no_tlp']);

                                                        // Pastikan format nomor telepon benar (awali dengan 62)
                                                        if (substr($no_tlp_clean, 0, 1) == '0') {
                                                            $no_tlp_clean = '62' . substr($no_tlp_clean, 1);
                                                        } elseif (substr($no_tlp_clean, 0, 2) != '62') {
                                                            $no_tlp_clean = '62' . $no_tlp_clean;
                                                        }

                                                        // Buat pesan untuk WhatsApp
                                                        $pesan = "Halo " . $a['Nama_Pasien'] . ", ";
                                                        $pesan .= "pendaftaran Anda dengan ID " . $a['ID_Pendaftaran'] . " ";
                                                        $pesan .= "pada tanggal " . date('d/m/Y H:i', strtotime($a['Waktu_Pendaftaran'])) . " ";
                                                        $pesan .= "saat ini berstatus " . $a['Status_Pendaftaran'] . ".";

                                                        // Encode pesan untuk URL
                                                        $pesan_encoded = urlencode($pesan);

                                                        // Buat URL WhatsApp
                                                        $whatsapp_url = "https://wa.me/" . $no_tlp_clean . "?text=" . $pesan_encoded;
                                                        ?>
                                                        <a href="<?= $whatsapp_url ?>" target="_blank"
                                                            class="btn btn-sm btn-success btn-icon"
                                                            data-bs-toggle="tooltip" title="Hubungi via WhatsApp">
                                                            <i class="bi bi-whatsapp"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($a['Nama_Pasien']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($a['Waktu_Pendaftaran'])) ?></td>
                                            <td><?= !empty($a['Keluhan']) ? htmlspecialchars($a['Keluhan']) : '-' ?></td>
                                            <td>
                                                <span class="badge <?= getStatusBadgeClass($a['Status_Pendaftaran']) ?>">
                                                    <?= htmlspecialchars($a['Status_Pendaftaran']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Pendaftaran -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detail Pendaftaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function updateStatusDirect(id, newStatus) {
        if (confirm(`Apakah Anda yakin ingin mengubah status menjadi "${newStatus}"?`)) {
            const formData = new FormData();
            formData.append('id_pendaftaran', id);
            formData.append('status', newStatus);

            fetch('modules/rekam_medis/controllers/update_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal mengupdate status: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengupdate status');
                });
        }
    }

    function viewDetail(id) {
        const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
        detailModal.show();

        fetch(`modules/rekam_medis/controllers/get_pendaftaran_detail.php?id=${id}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('detailContent').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('detailContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Terjadi kesalahan saat memuat data: ${error.message}
                </div>
            `;
            });
    }

    function refreshPage() {
        location.reload();
    }

    // Inisialisasi tooltip
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<?php
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'Menunggu Konfirmasi':
            return 'bg-warning text-dark';
        case 'Dikonfirmasi':
            return 'bg-success';
        case 'Dibatalkan':
            return 'bg-danger';
        case 'Selesai':
            return 'bg-info';
        default:
            return 'bg-secondary';
    }
}

// Additional CSS
$additional_css = "
    .card {
        border: none;
        border-radius: 10px;
    }
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .badge {
        font-size: 0.875rem;
        padding: 0.5em 0.75em;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        margin-right: 0.25rem;
    }
    .modal-header {
        border-bottom: 0;
    }
    .modal-footer {
        border-top: 0;
    }
    .form-control, .form-select, .btn {
        height: 32px;
        font-size: 0.85rem;
        padding: 4px 8px;
    }
    .form-select {
        padding-right: 24px;
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 12px;
        white-space: nowrap;
        min-width: unset;
    }
    .btn i {
        margin-right: 4px;
        font-size: 0.85rem;
    }
    .filter-form {
        flex-wrap: wrap;
        align-items: center;
        gap: 4px !important;
    }
    .search-container {
        width: 200px;
    }
    .filter-container {
        width: 130px;
    }
    .button-container {
        display: inline-flex;
        gap: 4px;
    }
    .button-container .btn {
        margin: 0;
    }
    @media (max-width: 1200px) {
        .search-container {
            width: 180px;
        }
        .filter-container {
            width: 120px;
        }
    }
    @media (max-width: 992px) {
        .search-container {
            width: 100%;
            margin-bottom: 8px;
        }
        .filter-container {
            width: 48%;
            margin-bottom: 8px;
        }
        .button-container {
            margin-bottom: 8px;
        }
    }
    @media (max-width: 768px) {
        .form-control, .form-select, .btn {
            font-size: 0.85rem;
            height: 32px;
        }
        .filter-container {
            width: 100%;
        }
        .button-container {
            width: 100%;
            justify-content: flex-start;
        }
        .button-container .btn {
            flex: 1;
        }
    }
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }
    .btn-icon i {
        margin: 0;
        font-size: 1rem;
    }
    .btn-group .btn-icon {
        width: 28px;
        height: 28px;
    }
    .btn-group .btn-icon i {
        font-size: 0.875rem;
    }
    .filter-form .btn-icon {
        margin: 0;
    }
    @media (max-width: 768px) {
        .btn-icon {
            width: 36px;
            height: 36px;
        }
        .btn-group .btn-icon {
            width: 32px;
            height: 32px;
        }
    }
";
?>