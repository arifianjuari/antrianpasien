<?php
session_start();
require_once '../config/database.php';
$page_title = "Jadwal Praktek Dokter";

// Filter
$id_tempat_praktek = isset($_GET['tempat']) ? $_GET['tempat'] : '';
$id_dokter = isset($_GET['dokter']) ? $_GET['dokter'] : '';
$hari = isset($_GET['hari']) ? $_GET['hari'] : date('l');

// Convert English day name to Indonesian
$hari_names = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
if (empty($hari)) {
    $hari = $hari_names[date('l')];
}

// Ambil data tempat praktek
try {
    $query = "SELECT * FROM tempat_praktek WHERE Status_Aktif = 1 ORDER BY Nama_Tempat ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tempat_praktek = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $tempat_praktek = [];
}

// Ambil data dokter
try {
    $query = "SELECT * FROM dokter WHERE Status_Aktif = 1 ORDER BY Nama_Dokter ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $dokter = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $dokter = [];
}

// Ambil jadwal rutin
$jadwal_rutin = [];
try {
    $query = "
        SELECT 
            jr.*,
            tp.Nama_Tempat,
            d.Nama_Dokter,
            d.Spesialisasi
        FROM 
            jadwal_rutin jr
        LEFT JOIN 
            tempat_praktek tp ON jr.ID_Tempat_Praktek = tp.ID_Tempat_Praktek
        LEFT JOIN 
            dokter d ON jr.ID_Dokter = d.ID_Dokter
        WHERE 
            jr.Status_Aktif = 1
    ";

    $params = [];

    if (!empty($id_tempat_praktek)) {
        $query .= " AND jr.ID_Tempat_Praktek = :id_tempat_praktek";
        $params[':id_tempat_praktek'] = $id_tempat_praktek;
    }

    if (!empty($id_dokter)) {
        $query .= " AND jr.ID_Dokter = :id_dokter";
        $params[':id_dokter'] = $id_dokter;
    }

    if (!empty($hari)) {
        $query .= " AND jr.Hari = :hari";
        $params[':hari'] = $hari;
    }

    $query .= " ORDER BY jr.Hari, jr.Jam_Mulai ASC";

    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $jadwal_rutin = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set status kuota
    foreach ($jadwal_rutin as &$jr) {
        $jr['Status_Kuota'] = $jr['Status_Aktif'] ? 'Tersedia' : 'Tidak Tersedia';
        $jr['Jenis_Jadwal'] = 'Rutin';
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $jadwal_rutin = [];
}

// Kelompokkan jadwal berdasarkan tempat praktek
$jadwal_by_tempat = [];
foreach ($jadwal_rutin as $jadwal) {
    $id_tempat = $jadwal['ID_Tempat_Praktek'];
    if (!isset($jadwal_by_tempat[$id_tempat])) {
        $jadwal_by_tempat[$id_tempat] = [
            'info_tempat' => [
                'ID_Tempat_Praktek' => $id_tempat,
                'Nama_Tempat' => $jadwal['Nama_Tempat']
            ],
            'jadwal' => []
        ];
    }
    $jadwal_by_tempat[$id_tempat]['jadwal'][] = $jadwal;
}

// Start output buffering
ob_start();
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Jadwal Praktek Dokter</h5>
                    <span class="badge bg-light text-dark"><?php echo $hari; ?></span>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="tempat" class="form-label">Tempat Praktek</label>
                            <select class="form-select" id="tempat" name="tempat">
                                <option value="">Semua Tempat</option>
                                <?php foreach ($tempat_praktek as $tp): ?>
                                    <option value="<?php echo htmlspecialchars($tp['ID_Tempat_Praktek']); ?>" <?php echo $id_tempat_praktek == $tp['ID_Tempat_Praktek'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tp['Nama_Tempat']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="dokter" class="form-label">Dokter</label>
                            <select class="form-select" id="dokter" name="dokter">
                                <option value="">Semua Dokter</option>
                                <?php foreach ($dokter as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['ID_Dokter']); ?>" <?php echo $id_dokter == $d['ID_Dokter'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['Nama_Dokter']); ?> (<?php echo htmlspecialchars($d['Spesialisasi']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="hari" class="form-label">Hari</label>
                            <select class="form-select" id="hari" name="hari">
                                <option value="">Semua Hari</option>
                                <option value="Senin" <?php echo $hari == 'Senin' ? 'selected' : ''; ?>>Senin</option>
                                <option value="Selasa" <?php echo $hari == 'Selasa' ? 'selected' : ''; ?>>Selasa</option>
                                <option value="Rabu" <?php echo $hari == 'Rabu' ? 'selected' : ''; ?>>Rabu</option>
                                <option value="Kamis" <?php echo $hari == 'Kamis' ? 'selected' : ''; ?>>Kamis</option>
                                <option value="Jumat" <?php echo $hari == 'Jumat' ? 'selected' : ''; ?>>Jumat</option>
                                <option value="Sabtu" <?php echo $hari == 'Sabtu' ? 'selected' : ''; ?>>Sabtu</option>
                                <option value="Minggu" <?php echo $hari == 'Minggu' ? 'selected' : ''; ?>>Minggu</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($jadwal_by_tempat)): ?>
        <div class="alert alert-info">
            Tidak ada jadwal praktek yang tersedia untuk kriteria yang dipilih.
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-12">
                <div class="accordion" id="accordionJadwal">
                    <?php foreach ($jadwal_by_tempat as $id_tempat => $data): ?>
                        <div class="accordion-item mb-3 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?php echo $id_tempat_praktek && $id_tempat_praktek != $id_tempat ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $id_tempat; ?>">
                                    <?php echo htmlspecialchars($data['info_tempat']['Nama_Tempat']); ?>
                                    <span class="badge bg-primary ms-2"><?php echo count($data['jadwal']); ?> Jadwal</span>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $id_tempat; ?>" class="accordion-collapse collapse <?php echo $id_tempat_praktek == $id_tempat || empty($id_tempat_praktek) ? 'show' : ''; ?>">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Dokter</th>
                                                    <th>Hari</th>
                                                    <th>Jam Praktek</th>
                                                    <th>Jenis Layanan</th>
                                                    <th>Kuota</th>
                                                    <th>Status</th>
                                                    <th>Jenis Jadwal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data['jadwal'] as $jadwal): ?>
                                                    <tr>
                                                        <td>
                                                            <?php echo htmlspecialchars($jadwal['Nama_Dokter']); ?>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($jadwal['Spesialisasi']); ?></small>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($jadwal['Hari']); ?></td>
                                                        <td><?php echo htmlspecialchars($jadwal['Jam_Mulai']) . ' - ' . htmlspecialchars($jadwal['Jam_Selesai']); ?></td>
                                                        <td><?php echo htmlspecialchars($jadwal['Jenis_Layanan']); ?></td>
                                                        <td><?php echo htmlspecialchars($jadwal['Kuota']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $jadwal['Status_Kuota'] == 'Tersedia' ? 'success' : 'danger'; ?>">
                                                                <?php echo htmlspecialchars($jadwal['Status_Kuota']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary">
                                                                <?php echo htmlspecialchars($jadwal['Jenis_Jadwal']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form when filters change
        const form = document.querySelector('form');
        const filters = form.querySelectorAll('select');

        filters.forEach(filter => {
            filter.addEventListener('change', () => {
                form.submit();
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
    .accordion-item {
        border-radius: 10px;
        overflow: hidden;
    }
    .accordion-button:not(.collapsed) {
        background-color: #e7f1ff;
        color: #0d6efd;
    }
    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
    }
    .badge {
        font-weight: 500;
    }
";

// Include template
include_once '../template/layout.php';
?>