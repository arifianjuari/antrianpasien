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
$hari = isset($_GET['hari']) ? $_GET['hari'] : '';

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
            d.Nama_Dokter,
            p.Waktu_Pendaftaran,
            (SELECT COUNT(*) + 1 FROM pendaftaran p2 
             JOIN jadwal_rutin jr2 ON p2.ID_Jadwal = jr2.ID_Jadwal_Rutin 
             WHERE jr2.Hari = jr.Hari 
             AND p2.Waktu_Pendaftaran < p.Waktu_Pendaftaran
             AND p2.Status_Pendaftaran NOT IN ('Dibatalkan', 'Selesai')) AS Nomor_Urut
        FROM 
            pendaftaran p
        JOIN 
            jadwal_rutin jr ON p.ID_Jadwal = jr.ID_Jadwal_Rutin
        JOIN 
            tempat_praktek tp ON p.ID_Tempat_Praktek = tp.ID_Tempat_Praktek
        JOIN 
            dokter d ON p.ID_Dokter = d.ID_Dokter
        WHERE p.Status_Pendaftaran NOT IN ('Dibatalkan', 'Selesai')
    ";

    $params = [];

    if (!empty($_GET['tanggal'])) {
        $query .= " AND DATE(p.Waktu_Pendaftaran) = :tanggal";
        $params[':tanggal'] = $_GET['tanggal'];
    }

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

// Start output buffering
ob_start();
?>

<div class="container py-4">
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
                            <div class="col-md-3">
                                <label for="tanggal" class="form-label">Tanggal Pendaftaran</label>
                                <input type="date" class="form-select" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
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
                            <div class="col-md-3">
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
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Nomor antrian dihitung berdasarkan hari dan waktu pendaftaran. Pasien yang mendaftar lebih awal untuk hari yang sama akan mendapatkan nomor antrian yang lebih kecil.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No. Antrian</th>
                                        <th>ID Pendaftaran</th>
                                        <th>Nama Pasien</th>
                                        <th>Hari</th>
                                        <th>Jadwal</th>
                                        <th>Tempat Praktek</th>
                                        <th>Dokter</th>
                                        <th>Jenis Layanan</th>
                                        <th>Waktu Daftar</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($antrian as $a): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?= htmlspecialchars($a['Nomor_Urut']) ?></span></td>
                                            <td><?= htmlspecialchars($a['ID_Pendaftaran']) ?></td>
                                            <td><?= htmlspecialchars($a['nm_pasien']) ?></td>
                                            <td><?= htmlspecialchars($a['Hari']) ?></td>
                                            <td><?= htmlspecialchars($a['Jam_Mulai']) ?> - <?= htmlspecialchars($a['Jam_Selesai']) ?></td>
                                            <td><?= htmlspecialchars($a['Nama_Tempat']) ?></td>
                                            <td><?= htmlspecialchars($a['Nama_Dokter']) ?></td>
                                            <td><?= htmlspecialchars($a['Jenis_Layanan']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($a['Waktu_Pendaftaran'])) ?></td>
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

        // Auto refresh halaman setiap 1 menit (60000 milidetik)
        const refreshInterval = 60000; // 1 menit

        // Tampilkan pesan notifikasi refresh
        function showRefreshNotification() {
            const notification = document.createElement('div');
            notification.className = 'position-fixed bottom-0 end-0 p-3';
            notification.style.zIndex = '5';
            notification.innerHTML = `
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-info text-white">
                        <strong class="me-auto">Informasi</strong>
                        <small>Baru saja</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        Halaman diperbarui secara otomatis.
                    </div>
                </div>
            `;
            document.body.appendChild(notification);

            // Hapus notifikasi setelah 3 detik
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Set interval untuk refresh otomatis
        setInterval(function() {
            // Simpan posisi scroll saat ini
            const scrollPosition = window.scrollY;

            // Simpan posisi scroll di sessionStorage
            sessionStorage.setItem('scrollPosition', scrollPosition);

            // Refresh halaman
            location.reload();
        }, refreshInterval);

        // Kembalikan posisi scroll setelah refresh
        const savedScrollPosition = sessionStorage.getItem('scrollPosition');
        if (savedScrollPosition) {
            window.scrollTo(0, parseInt(savedScrollPosition));
            showRefreshNotification();
        }
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
    .badge.bg-primary {
        font-size: 1rem;
        padding: 0.5rem 0.8rem;
        border-radius: 50%;
        min-width: 2.5rem;
        display: inline-block;
        text-align: center;
    }
";

// Include template
include_once '../template/layout.php';
?>