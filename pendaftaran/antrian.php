<?php
session_start();
require_once '../config/database.php';
$page_title = "Daftar Antrian Pasien";

// Cek apakah user sudah login (opsional)
$is_logged_in = isset($_SESSION['user_id']);

// Filter tanggal
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$id_tempat_praktek = isset($_GET['tempat']) ? $_GET['tempat'] : '';
$id_dokter = isset($_GET['dokter']) ? $_GET['dokter'] : '';

// Ambil data tempat praktek
try {
    $query_tempat = "SELECT ID_Tempat_Praktek, Nama_Tempat FROM tempat_praktek WHERE Status_Aktif = 1";
    $stmt_tempat = $conn->prepare($query_tempat);
    $stmt_tempat->execute();
    $tempat_praktek = $stmt_tempat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $tempat_praktek = [];
}

// Ambil data dokter
try {
    $query_dokter = "SELECT ID_Dokter, Nama_Dokter FROM dokter WHERE Status_Aktif = 1";
    $stmt_dokter = $conn->prepare($query_dokter);
    $stmt_dokter->execute();
    $dokter = $stmt_dokter->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $dokter = [];
}

// Ambil data antrian
$antrian = [];
$error_message = '';

try {
    $query = "
        SELECT 
            p.ID_Pendaftaran,
            p.nm_pasien,
            p.Status_Pendaftaran,
            jr.Hari,
            jr.Jam_Mulai,
            jr.Jam_Selesai,
            jr.Jenis_Layanan,
            tp.Nama_Tempat,
            d.Nama_Dokter
        FROM 
            pendaftaran p
        JOIN 
            jadwal_rutin jr ON p.ID_Jadwal = jr.ID_Jadwal_Rutin
        JOIN 
            tempat_praktek tp ON p.ID_Tempat_Praktek = tp.ID_Tempat_Praktek
        JOIN 
            dokter d ON p.ID_Dokter = d.ID_Dokter
        WHERE 1=1
    ";

    $params = [];

    if (!empty($_GET['hari'])) {
        $query .= " AND jr.Hari = :hari";
        $params[':hari'] = $_GET['hari'];
    }

    if (!empty($_GET['tempat'])) {
        $query .= " AND p.ID_Tempat_Praktek = :tempat";
        $params[':tempat'] = $_GET['tempat'];
    }

    if (!empty($_GET['dokter'])) {
        $query .= " AND p.ID_Dokter = :dokter";
        $params[':dokter'] = $_GET['dokter'];
    }

    $query .= " ORDER BY 
        CASE jr.Hari
            WHEN 'Senin' THEN 1
            WHEN 'Selasa' THEN 2
            WHEN 'Rabu' THEN 3
            WHEN 'Kamis' THEN 4
            WHEN 'Jumat' THEN 5
            WHEN 'Sabtu' THEN 6
            WHEN 'Minggu' THEN 7
        END ASC,
        jr.Jam_Mulai ASC,
        p.Waktu_Pendaftaran ASC";

    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $antrian = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $antrian = [];
}

// Cek status pendaftaran berdasarkan ID
$cek_status_result = null;
$id_pendaftaran = isset($_POST['id_pendaftaran']) ? trim($_POST['id_pendaftaran']) : '';

if (!empty($id_pendaftaran)) {
    try {
        $query = "
            SELECT 
                p.*,
                tp.Nama_Tempat,
                d.Nama_Dokter,
                d.Spesialisasi,
                CASE
                    WHEN p.ID_Jadwal LIKE 'JR%' THEN 
                        (SELECT CONCAT(Jam_Mulai, ' - ', Jam_Selesai, ' (', Jenis_Layanan, ')') 
                         FROM jadwal_rutin 
                         WHERE ID_Jadwal_Rutin = p.ID_Jadwal)
                    ELSE 
                        (SELECT CONCAT(Jam_Mulai, ' - ', Jam_Selesai, ' (', Jenis_Layanan, ')') 
                         FROM jadwal_praktek 
                         WHERE ID_Jadwal_Praktek = p.ID_Jadwal)
                END AS Jadwal
            FROM 
                pendaftaran p
            LEFT JOIN 
                tempat_praktek tp ON p.ID_Tempat_Praktek = tp.ID_Tempat_Praktek
            LEFT JOIN 
                dokter d ON p.ID_Dokter = d.ID_Dokter
            WHERE 
                p.ID_Pendaftaran = :id_pendaftaran
        ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_pendaftaran', $id_pendaftaran);
        $stmt->execute();
        $cek_status_result = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        $error_message = "Terjadi kesalahan saat memeriksa status pendaftaran.";
    }
}

// Start output buffering
ob_start();
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Cek Status Pendaftaran</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="row g-3">
                        <div class="col-md-8">
                            <label for="id_pendaftaran" class="form-label">ID Pendaftaran</label>
                            <input type="text" class="form-control" id="id_pendaftaran" name="id_pendaftaran" placeholder="Masukkan ID Pendaftaran (contoh: REG-20240501-0001)" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Cek Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($cek_status_result): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow border-0">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Hasil Pencarian</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 mb-3">Informasi Pasien</h6>
                                <p><strong>ID Pendaftaran:</strong> <?php echo htmlspecialchars($cek_status_result['ID_Pendaftaran']); ?></p>
                                <p><strong>Nama:</strong> <?php echo htmlspecialchars($cek_status_result['nm_pasien']); ?></p>
                                <p><strong>Tanggal Lahir:</strong> <?php echo date('d-m-Y', strtotime($cek_status_result['tgl_lahir'])); ?></p>
                                <p><strong>Jenis Kelamin:</strong> <?php echo htmlspecialchars($cek_status_result['jk']); ?></p>
                                <p><strong>No. Telepon:</strong> <?php echo htmlspecialchars($cek_status_result['no_tlp']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 mb-3">Informasi Kunjungan</h6>
                                <p><strong>Tempat Praktek:</strong> <?php echo htmlspecialchars($cek_status_result['Nama_Tempat']); ?></p>
                                <p><strong>Dokter:</strong> <?php echo htmlspecialchars($cek_status_result['Nama_Dokter']); ?> (<?php echo htmlspecialchars($cek_status_result['Spesialisasi']); ?>)</p>
                                <p><strong>Tanggal Kunjungan:</strong> <?php echo date('d-m-Y', strtotime($cek_status_result['Tanggal_Kunjungan'])); ?></p>
                                <p><strong>Jadwal:</strong> <?php echo htmlspecialchars($cek_status_result['Jadwal']); ?></p>
                                <p>
                                    <strong>Status:</strong>
                                    <span class="badge <?php
                                                        echo ($cek_status_result['Status_Pendaftaran'] == 'Menunggu Konfirmasi') ? 'bg-warning' : (($cek_status_result['Status_Pendaftaran'] == 'Dikonfirmasi') ? 'bg-success' : (($cek_status_result['Status_Pendaftaran'] == 'Dibatalkan') ? 'bg-danger' : 'bg-secondary'));
                                                        ?>">
                                        <?php echo htmlspecialchars($cek_status_result['Status_Pendaftaran']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Antrian Pasien</h5>
                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#filterSection">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
                <div class="collapse" id="filterSection">
                    <div class="card-body bg-light">
                        <form id="filterForm" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="hari" class="form-label">Hari</label>
                                <select class="form-select" id="hari" name="hari">
                                    <option value="">Semua Hari</option>
                                    <?php
                                    $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                    foreach ($hari as $h) {
                                        $selected = (isset($_GET['hari']) && $_GET['hari'] == $h) ? 'selected' : '';
                                        echo "<option value=\"$h\" $selected>$h</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="tempat" class="form-label">Tempat Praktek</label>
                                <select class="form-select" id="tempat" name="tempat">
                                    <option value="">Semua Tempat</option>
                                    <?php foreach ($tempat_praktek as $tp): ?>
                                        <option value="<?= htmlspecialchars($tp['ID_Tempat_Praktek']) ?>"
                                            <?= (isset($_GET['tempat']) && $_GET['tempat'] == $tp['ID_Tempat_Praktek']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tp['Nama_Tempat']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="dokter" class="form-label">Dokter</label>
                                <select class="form-select" id="dokter" name="dokter">
                                    <option value="">Semua Dokter</option>
                                    <?php foreach ($dokter as $d): ?>
                                        <option value="<?= htmlspecialchars($d['ID_Dokter']) ?>"
                                            <?= (isset($_GET['dokter']) && $_GET['dokter'] == $d['ID_Dokter']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($d['Nama_Dokter']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php elseif (empty($antrian)): ?>
                        <div class="alert alert-info">
                            Tidak ada data antrian untuk filter yang dipilih.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No. Antrian</th>
                                        <th>Nama Pasien</th>
                                        <th>Hari</th>
                                        <th>Jadwal</th>
                                        <th>Tempat Praktek</th>
                                        <th>Dokter</th>
                                        <th>Jenis Layanan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($antrian as $a): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($a['ID_Pendaftaran']) ?></td>
                                            <td><?= htmlspecialchars($a['nm_pasien']) ?></td>
                                            <td><?= htmlspecialchars($a['Hari']) ?></td>
                                            <td><?= htmlspecialchars($a['Jam_Mulai']) ?> - <?= htmlspecialchars($a['Jam_Selesai']) ?></td>
                                            <td><?= htmlspecialchars($a['Nama_Tempat']) ?></td>
                                            <td><?= htmlspecialchars($a['Nama_Dokter']) ?></td>
                                            <td><?= htmlspecialchars($a['Jenis_Layanan']) ?></td>
                                            <td><?= htmlspecialchars($a['Status_Pendaftaran']) ?></td>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form when filter changes
        const filterForm = document.querySelector('#filterSection form');
        const filterInputs = filterForm.querySelectorAll('input, select');

        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    });
</script>

<?php
$content = ob_get_clean();

// Additional CSS
$additional_css = "
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
    .card-header {
        background-color: #0d6efd;
    }
    .border-bottom {
        border-bottom: 2px solid #dee2e6 !important;
        margin-bottom: 1rem;
    }
";

// Include template
include_once '../template/layout.php';
?>