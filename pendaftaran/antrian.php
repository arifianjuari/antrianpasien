<?php
session_start();
require_once '../config/database.php';
$page_title = "Daftar Antrian Pasien";

// Cek apakah user sudah login (opsional)
$is_logged_in = isset($_SESSION['user_id']);

// Filter tanggal
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

    // Format waktu dan kelompokkan antrian berdasarkan hari, tempat, dan dokter
    $antrian_by_day_place = [];
    foreach ($antrian as &$a) {
        $a['Jam_Mulai_Format'] = date('H:i', strtotime($a['Jam_Mulai']));
        $a['Jam_Selesai_Format'] = date('H:i', strtotime($a['Jam_Selesai']));
        $a['Waktu_Daftar_Format'] = date('d/m/Y H:i', strtotime($a['Waktu_Pendaftaran']));

        $key = $a['Hari'] . '_' . $a['Nama_Tempat'] . '_' . $a['Nama_Dokter'];
        if (!isset($antrian_by_day_place[$key])) {
            $antrian_by_day_place[$key] = [
                'hari' => $a['Hari'],
                'tempat' => $a['Nama_Tempat'],
                'dokter' => $a['Nama_Dokter'],
                'antrian' => []
            ];
        }
        $antrian_by_day_place[$key]['antrian'][] = $a;
    }
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
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>Daftar Antrian Pasien</h5>
                    <button class="btn btn-sm btn-light rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#filterSection">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
                <div class="collapse" id="filterSection">
                    <div class="card-body bg-light border-bottom border-primary">
                        <form id="filterForm" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="hari" class="form-label fw-bold"><i class="fas fa-calendar-day me-1"></i>Hari</label>
                                <select class="form-select rounded-pill" id="hari" name="hari">
                                    <option value="">Semua Hari</option>
                                    <?php
                                    $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                    foreach ($hari_list as $h) {
                                        $selected = (isset($_GET['hari']) && $_GET['hari'] == $h) ? 'selected' : '';
                                        echo "<option value=\"$h\" $selected>$h</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="tempat" class="form-label fw-bold"><i class="fas fa-hospital me-1"></i>Tempat Praktek</label>
                                <select class="form-select rounded-pill" id="tempat" name="tempat">
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
                                <label for="dokter" class="form-label fw-bold"><i class="fas fa-user-md me-1"></i>Dokter</label>
                                <select class="form-select rounded-pill" id="dokter" name="dokter">
                                    <option value="">Semua Dokter</option>
                                    <?php foreach ($dokter as $d): ?>
                                        <option value="<?= htmlspecialchars($d['ID_Dokter']) ?>"
                                            <?= (isset($_GET['dokter']) && $_GET['dokter'] == $d['ID_Dokter']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($d['Nama_Dokter']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger rounded-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                                <div><?php echo htmlspecialchars($error_message); ?></div>
                            </div>
                        </div>
                    <?php elseif (empty($antrian)): ?>
                        <div class="alert alert-info rounded-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle fa-2x me-3"></i>
                                <div>Tidak ada data antrian untuk filter yang dipilih.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info rounded-4 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle fa-2x me-3"></i>
                                <div>Nomor antrian dihitung berdasarkan hari dan waktu pendaftaran. Pasien yang mendaftar lebih awal untuk hari yang sama akan mendapatkan nomor antrian yang lebih kecil.</div>
                            </div>
                        </div>

                        <!-- Tampilan Antrian Minimalis -->
                        <div class="row">
                            <?php foreach ($antrian_by_day_place as $group): ?>
                                <div class="col-md-12 mb-4">
                                    <div class="card border-0 shadow-sm rounded-4">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                                            <h6 class="mb-0 fw-bold">
                                                <i class="fas fa-calendar-day me-2 text-primary"></i><?= htmlspecialchars($group['hari']) ?>
                                                <span class="mx-2">|</span>
                                                <i class="fas fa-hospital me-2 text-primary"></i><?= htmlspecialchars($group['tempat']) ?>
                                                <span class="mx-2">|</span>
                                                <i class="fas fa-user-md me-2 text-primary"></i><?= htmlspecialchars($group['dokter']) ?>
                                            </h6>
                                            <span class="badge bg-primary rounded-pill"><?= count($group['antrian']) ?> Antrian</span>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="text-center">No</th>
                                                            <th>Pasien</th>
                                                            <th>Waktu Daftar</th>
                                                            <th>Jadwal</th>
                                                            <th>Layanan</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($group['antrian'] as $a): ?>
                                                            <tr>
                                                                <td class="text-center">
                                                                    <div class="antrian-number"><?= htmlspecialchars($a['Nomor_Urut']) ?></div>
                                                                </td>
                                                                <td>
                                                                    <div class="fw-bold"><?= htmlspecialchars($a['nm_pasien']) ?></div>
                                                                </td>
                                                                <td>
                                                                    <div class="small">
                                                                        <i class="far fa-clock me-1"></i><?= $a['Waktu_Daftar_Format'] ?>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-light text-dark">
                                                                        <i class="far fa-clock me-1"></i><?= $a['Jam_Mulai_Format'] ?> - <?= $a['Jam_Selesai_Format'] ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge <?= strpos($a['Jenis_Layanan'], 'BPJS') !== false ? 'bg-success' : 'bg-primary' ?> rounded-pill">
                                                                        <?= htmlspecialchars($a['Jenis_Layanan']) ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-<?= $a['Status_Pendaftaran'] == 'Menunggu' ? 'warning' : ($a['Status_Pendaftaran'] == 'Dalam Proses' ? 'info' : 'secondary') ?> rounded-pill">
                                                                        <?= htmlspecialchars($a['Status_Pendaftaran']) ?>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tambahkan floating WhatsApp button -->
<div class="floating-whatsapp">
    <a href="https://wa.me/6285190086842?text=Halo%20Admin%2C%20saya%20ingin%20bertanya%20tentang%20antrian%20pasien." target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>
    <span class="tooltip-text">Hubungi Admin via WhatsApp</span>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form when filter changes
        const filterForm = document.querySelector('#filterForm');
        const filterInputs = filterForm.querySelectorAll('select');

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
                        <i class="fas fa-sync-alt me-2"></i>
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
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .card-header {
        background-color: #0d6efd;
    }
    .border-bottom {
        border-bottom: 2px solid #dee2e6 !important;
    }
    .border-primary {
        border-color: #0d6efd !important;
    }
    .antrian-number {
        background-color: #0d6efd;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    .rounded-4 {
        border-radius: 0.75rem !important;
    }
    .rounded-top-4 {
        border-top-left-radius: 0.75rem !important;
        border-top-right-radius: 0.75rem !important;
    }
    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
    }
    .badge {
        font-weight: 500;
    }
    .rounded-pill {
        border-radius: 50rem !important;
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