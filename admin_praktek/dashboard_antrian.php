<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include file konfigurasi
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

// Cek status login dan role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu!";
    header('Location: ' . $base_url . '/login.php');
    exit;
}

// Cek role admin
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini!";
    header('Location: ' . $base_url . '/index.php');
    exit;
}

try {
    // Query untuk mengambil data pengumuman terkini
    $stmt = $pdo->query("SELECT * FROM pengumuman WHERE status = 'active' ORDER BY created_at DESC LIMIT 5");
    $pengumuman = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query untuk mengambil data antrian hari ini
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_antrian,
               SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as sudah_dilayani,
               SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as sedang_menunggu
        FROM antrian 
        WHERE DATE(tanggal) = ?
    ");
    $stmt->execute([$today]);
    $statistik = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query untuk antrian yang sedang dilayani
    $stmt = $pdo->prepare("
        SELECT a.*, p.nama 
        FROM antrian a 
        JOIN pasien p ON a.id_pasien = p.id 
        WHERE a.status = 'dilayani' 
        AND DATE(a.tanggal) = ? 
        ORDER BY a.updated_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$today]);
    $antrian_sekarang = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query untuk antrian berikutnya
    $stmt = $pdo->prepare("
        SELECT a.*, p.nama 
        FROM antrian a 
        JOIN pasien p ON a.id_pasien = p.id 
        WHERE a.status = 'menunggu' 
        AND DATE(a.tanggal) = ? 
        ORDER BY a.no_antrian ASC 
        LIMIT 3
    ");
    $stmt->execute([$today]);
    $antrian_berikutnya = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error
    error_log("Database Error: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat mengambil data. Silakan coba lagi nanti.";
}

// Include template header dan sidebar
require_once __DIR__ . '/../template/header.php';
require_once __DIR__ . '/../template/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Header Dashboard -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">Dashboard Antrian</h4>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#settingModal">
                        <i class="bi bi-gear"></i> Pengaturan Tampilan
                    </button>
                </div>
            </div>
        </div>

        <!-- Row untuk Pengumuman dan Jam -->
        <div class="row mb-4">
            <!-- Kolom Pengumuman -->
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-megaphone"></i> Pengumuman Terkini
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="pengumuman-container" style="height: 200px; overflow-y: auto;">
                            <?php if (!empty($pengumuman)): ?>
                                <?php foreach ($pengumuman as $p): ?>
                                    <div class="pengumuman-item mb-3 p-3 border-bottom">
                                        <h6 class="fw-bold"><?= htmlspecialchars($p['judul']) ?></h6>
                                        <p class="mb-1"><?= htmlspecialchars($p['isi']) ?></p>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <p>Tidak ada pengumuman terkini</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Jam dan Tanggal -->
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h2 class="display-4 mb-3" id="jamDigital">00:00:00</h2>
                        <h4 class="mb-3" id="tanggalHari">Senin, 1 Januari 2024</h4>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Status: Jam Praktek
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row untuk Antrian -->
        <div class="row">
            <!-- Antrian Saat Ini -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-check"></i> Sedang Dilayani
                        </h5>
                    </div>
                    <div class="card-body text-center" id="antrian-sekarang">
                        <?php if ($antrian_sekarang): ?>
                            <h1 class="display-1 mb-3"><?= htmlspecialchars($antrian_sekarang['no_antrian']) ?></h1>
                            <h4><?= htmlspecialchars($antrian_sekarang['nama']) ?></h4>
                            <p class="text-muted">Mulai: <?= date('H:i:s', strtotime($antrian_sekarang['updated_at'])) ?></p>
                        <?php else: ?>
                            <h4 class="text-muted">Belum ada antrian yang dilayani</h4>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Antrian Berikutnya -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-lines-fill"></i> Antrian Berikutnya
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="antrian-berikutnya">
                                <thead>
                                    <tr>
                                        <th>No. Antrian</th>
                                        <th>Nama</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($antrian_berikutnya)): ?>
                                        <?php foreach ($antrian_berikutnya as $antrian): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($antrian['no_antrian']) ?></td>
                                                <td><?= htmlspecialchars($antrian['nama']) ?></td>
                                                <td><span class="badge bg-warning">Menunggu</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Tidak ada antrian berikutnya</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row untuk Statistik -->
        <div class="row">
            <!-- Total Antrian -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted mb-1">Total Antrian</h6>
                                <h3 class="mb-0" data-stat="total_antrian"><?= $statistik['total_antrian'] ?? 0 ?></h3>
                            </div>
                            <div class="icon-shape bg-light-primary rounded-circle text-primary">
                                <i class="bi bi-people fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sudah Dilayani -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted mb-1">Sudah Dilayani</h6>
                                <h3 class="mb-0" data-stat="sudah_dilayani"><?= $statistik['sudah_dilayani'] ?? 0 ?></h3>
                            </div>
                            <div class="icon-shape bg-light-success rounded-circle text-success">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sedang Menunggu -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted mb-1">Sedang Menunggu</h6>
                                <h3 class="mb-0" data-stat="sedang_menunggu"><?= $statistik['sedang_menunggu'] ?? 0 ?></h3>
                            </div>
                            <div class="icon-shape bg-light-warning rounded-circle text-warning">
                                <i class="bi bi-clock fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estimasi Waktu -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted mb-1">Estimasi Waktu</h6>
                                <h3 class="mb-0" data-stat="estimasi_waktu">~<?= ($statistik['sedang_menunggu'] ?? 0) * 15 ?> Menit</h3>
                            </div>
                            <div class="icon-shape bg-light-info rounded-circle text-info">
                                <i class="bi bi-hourglass-split fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pengaturan -->
<div class="modal fade" id="settingModal" tabindex="-1" aria-labelledby="settingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="settingModalLabel">Pengaturan Tampilan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="settingForm">
                    <div class="mb-3">
                        <label class="form-label">Tampilkan Pengumuman</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showAnnouncement" checked>
                            <label class="form-check-label" for="showAnnouncement">Aktif</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Interval Refresh (detik)</label>
                        <input type="number" class="form-control" id="refreshInterval" value="30" min="5">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Antrian Ditampilkan</label>
                        <input type="number" class="form-control" id="queueCount" value="5" min="1" max="10">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="saveSettings()">Simpan Pengaturan</button>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-shape {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-light-primary {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }

    .bg-light-success {
        background-color: rgba(var(--bs-success-rgb), 0.1);
    }

    .bg-light-warning {
        background-color: rgba(var(--bs-warning-rgb), 0.1);
    }

    .bg-light-info {
        background-color: rgba(var(--bs-info-rgb), 0.1);
    }

    .pengumuman-container::-webkit-scrollbar {
        width: 5px;
    }

    .pengumuman-container::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .pengumuman-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 5px;
    }

    .pengumuman-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

<script>
    // Fungsi untuk memperbarui jam digital
    function updateClock() {
        const now = new Date();
        const options = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };

        document.getElementById('jamDigital').textContent = now.toLocaleTimeString('id-ID');
        document.getElementById('tanggalHari').textContent = now.toLocaleDateString('id-ID', options);
    }

    // Update jam setiap detik
    setInterval(updateClock, 1000);
    updateClock(); // Panggil sekali untuk menghindari delay

    // Fungsi untuk memuat data antrian
    function loadQueueData() {
        fetch('get_queue_data.php')
            .then(response => response.json())
            .then(data => {
                // Update data antrian di halaman
                updateQueueDisplay(data);
            })
            .catch(error => console.error('Error:', error));
    }

    // Fungsi untuk memperbarui tampilan data antrian
    function updateQueueDisplay(data) {
        // Update statistik
        if (data.statistik) {
            document.querySelector('[data-stat="total_antrian"]').textContent = data.statistik.total_antrian || 0;
            document.querySelector('[data-stat="sudah_dilayani"]').textContent = data.statistik.sudah_dilayani || 0;
            document.querySelector('[data-stat="sedang_menunggu"]').textContent = data.statistik.sedang_menunggu || 0;
            document.querySelector('[data-stat="estimasi_waktu"]').textContent =
                '~' + ((data.statistik.sedang_menunggu || 0) * 15) + ' Menit';
        }

        // Update antrian sekarang
        const antrianSekarangContainer = document.querySelector('#antrian-sekarang');
        if (data.antrian_sekarang) {
            antrianSekarangContainer.innerHTML = `
                <h1 class="display-1 mb-3">${data.antrian_sekarang.no_antrian}</h1>
                <h4>${data.antrian_sekarang.nama}</h4>
                <p class="text-muted">Mulai: ${new Date(data.antrian_sekarang.updated_at).toLocaleTimeString('id-ID')}</p>
            `;
        } else {
            antrianSekarangContainer.innerHTML = '<h4 class="text-muted">Belum ada antrian yang dilayani</h4>';
        }

        // Update antrian berikutnya
        const antrianBerikutnyaBody = document.querySelector('#antrian-berikutnya tbody');
        if (data.antrian_berikutnya && data.antrian_berikutnya.length > 0) {
            antrianBerikutnyaBody.innerHTML = data.antrian_berikutnya.map(antrian => `
                <tr>
                    <td>${antrian.no_antrian}</td>
                    <td>${antrian.nama}</td>
                    <td><span class="badge bg-warning">Menunggu</span></td>
                </tr>
            `).join('');
        } else {
            antrianBerikutnyaBody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center">Tidak ada antrian berikutnya</td>
                </tr>
            `;
        }

        // Update pengumuman
        const pengumumanContainer = document.querySelector('.pengumuman-container');
        if (data.pengumuman && data.pengumuman.length > 0) {
            pengumumanContainer.innerHTML = data.pengumuman.map(p => `
                <div class="pengumuman-item mb-3 p-3 border-bottom">
                    <h6 class="fw-bold">${p.judul}</h6>
                    <p class="mb-1">${p.isi}</p>
                    <small class="text-muted">${new Date(p.created_at).toLocaleString('id-ID')}</small>
                </div>
            `).join('');
        } else {
            pengumumanContainer.innerHTML = `
                <div class="text-center text-muted">
                    <p>Tidak ada pengumuman terkini</p>
                </div>
            `;
        }
    }

    // Fungsi untuk menyimpan pengaturan
    function saveSettings() {
        const settings = {
            showAnnouncement: document.getElementById('showAnnouncement').checked,
            refreshInterval: document.getElementById('refreshInterval').value,
            queueCount: document.getElementById('queueCount').value
        };

        // Simpan ke localStorage
        localStorage.setItem('dashboardSettings', JSON.stringify(settings));

        // Tutup modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('settingModal'));
        modal.hide();

        // Terapkan pengaturan
        applySettings(settings);

        // Tampilkan notifikasi
        alert('Pengaturan berhasil disimpan!');
    }

    // Fungsi untuk menerapkan pengaturan
    function applySettings(settings) {
        // Terapkan pengaturan tampilan pengumuman
        const announcementSection = document.querySelector('.col-lg-8');
        announcementSection.style.display = settings.showAnnouncement ? 'block' : 'none';

        // Set interval refresh
        clearInterval(window.queueRefreshInterval);
        window.queueRefreshInterval = setInterval(loadQueueData, settings.refreshInterval * 1000);
    }

    // Fungsi untuk memuat pengaturan
    function loadSettings() {
        const settings = JSON.parse(localStorage.getItem('dashboardSettings'));
        if (settings) {
            document.getElementById('showAnnouncement').checked = settings.showAnnouncement;
            document.getElementById('refreshInterval').value = settings.refreshInterval;
            document.getElementById('queueCount').value = settings.queueCount;
            applySettings(settings);
        }
    }

    // Muat pengaturan saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        loadSettings();
        // Set interval default jika tidak ada pengaturan
        if (!window.queueRefreshInterval) {
            window.queueRefreshInterval = setInterval(loadQueueData, 30000); // 30 detik
        }
    });
</script>

<?php
require_once __DIR__ . '/../template/footer.php';
?>