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
    $stmt = $pdo->query("
        SELECT * FROM pengumuman 
        WHERE status_aktif = 1 
        AND (tanggal_berakhir IS NULL OR tanggal_berakhir >= CURDATE())
        AND tanggal_mulai <= CURDATE()
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $pengumuman = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query untuk mengambil data antrian hari ini
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_antrian,
               SUM(CASE WHEN Status_Pendaftaran = 'selesai' THEN 1 ELSE 0 END) as sudah_dilayani,
               SUM(CASE WHEN Status_Pendaftaran = 'menunggu' THEN 1 ELSE 0 END) as sedang_menunggu
        FROM pendaftaran 
        WHERE DATE(Waktu_Perkiraan) = ?
    ");
    $stmt->execute([$today]);
    $statistik = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query untuk antrian yang sedang dilayani
    $stmt = $pdo->prepare("
        SELECT p.*, p.nm_pasien as nama 
        FROM pendaftaran p 
        WHERE p.Status_Pendaftaran = 'dilayani' 
        AND DATE(p.Waktu_Perkiraan) = ? 
        ORDER BY p.Waktu_Perkiraan DESC 
        LIMIT 1
    ");
    $stmt->execute([$today]);
    $antrian_sekarang = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query untuk antrian berikutnya
    $stmt = $pdo->prepare("
        SELECT p.*, p.nm_pasien as nama 
        FROM pendaftaran p 
        WHERE p.Status_Pendaftaran = 'menunggu' 
        AND DATE(p.Waktu_Perkiraan) = ? 
        ORDER BY p.Waktu_Perkiraan ASC 
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
            <!-- Kolom Pengumuman dan Video -->
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-megaphone"></i> Pengumuman Terkini
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="pengumuman-container">
                            <?php if (!empty($pengumuman)): ?>
                                <?php foreach ($pengumuman as $p): ?>
                                    <div class="pengumuman-item mb-3 p-3 border-bottom">
                                        <h6 class="fw-bold"><?= htmlspecialchars($p['judul']) ?></h6>
                                        <p class="mb-1"><?= htmlspecialchars($p['isi_pengumuman']) ?></p>
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
                <!-- Section untuk YouTube Video -->
                <div id="youtubeSection" class="mt-3" style="display: none;">
                    <div class="card">
                        <div class="card-body p-0">
                            <div id="youtubePlayer" class="ratio ratio-16x9">
                                <!-- YouTube iframe akan ditambahkan melalui JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Jam dan Antrian -->
            <div class="col-lg-4">
                <!-- Card Jam Digital -->
                <div class="card mb-4">
                    <div class="card-body text-center py-3">
                        <h2 class="display-4 mb-2" id="jamDigital">00:00:00</h2>
                        <h4 class="mb-2" id="tanggalHari">Senin, 1 Januari 2024</h4>
                        <div class="alert alert-info py-2 mb-0">
                            <i class="bi bi-info-circle"></i> Status: Jam Praktek
                        </div>
                    </div>
                </div>

                <!-- Card Daftar Antrian -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-ol"></i> Daftar Antrian Hari Ini
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Pasien</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="antrian-list">
                                    <?php if (!empty($antrian_berikutnya)): ?>
                                        <?php foreach ($antrian_berikutnya as $index => $antrian): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <span class="fw-bold"><?= $antrian['ID_Pendaftaran'] ?></span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars($antrian['nm_pasien']) ?></div>
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock"></i>
                                                        <?= date('H:i', strtotime($antrian['Waktu_Perkiraan'])) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning rounded-pill">
                                                        <?= htmlspecialchars($antrian['Status_Pendaftaran']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Tidak ada antrian</td>
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
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-shape bg-light-primary text-primary rounded me-3">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Total Antrian</h6>
                                <h4 class="mb-0" data-stat="total_antrian"><?= $statistik['total_antrian'] ?? 0 ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-shape bg-light-success text-success rounded me-3">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Sudah Dilayani</h6>
                                <h4 class="mb-0" data-stat="sudah_dilayani"><?= $statistik['sudah_dilayani'] ?? 0 ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-shape bg-light-warning text-warning rounded me-3">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Sedang Menunggu</h6>
                                <h4 class="mb-0" data-stat="sedang_menunggu"><?= $statistik['sedang_menunggu'] ?? 0 ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-shape bg-light-info text-info rounded me-3">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Estimasi Waktu</h6>
                                <h4 class="mb-0" data-stat="estimasi_waktu">~<?= ($statistik['sedang_menunggu'] ?? 0) * 15 ?> Menit</h4>
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
                    <div class="mb-3">
                        <label class="form-label">Link Video YouTube</label>
                        <input type="text" class="form-control" id="youtubeLink" placeholder="Masukkan link video YouTube">
                        <small class="text-muted">Format: https://www.youtube.com/watch?v=VIDEO_ID</small>
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

    .pengumuman-container {
        scrollbar-width: thin;
        height: 120px;
        overflow-y: auto;
        padding: 10px;
    }

    .pengumuman-item p {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 5px !important;
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .pengumuman-item {
        padding: 8px !important;
        margin-bottom: 8px !important;
        border-bottom: 1px solid #eee;
    }

    .pengumuman-item:last-child {
        margin-bottom: 0 !important;
        border-bottom: none;
    }

    .pengumuman-item h6 {
        margin-bottom: 4px;
        font-size: 0.95rem;
    }

    .pengumuman-item small {
        font-size: 0.8rem;
    }

    #jamDigital {
        font-size: 2.5rem;
        line-height: 1.2;
    }

    #tanggalHari {
        font-size: 1.2rem;
    }

    .alert {
        margin-bottom: 0;
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

    .bg-pink {
        background-color: #ff4081;
    }

    /* Tambahan style untuk daftar antrian */
    .table {
        margin-bottom: 0;
    }

    .table td,
    .table th {
        padding: 0.75rem;
        vertical-align: middle;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, .02);
    }

    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }

    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }

    .table-responsive::-webkit-scrollbar {
        width: 5px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 5px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
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
                <h1 class="display-1 mb-3">${data.antrian_sekarang.ID_Pendaftaran}</h1>
                <h4>${data.antrian_sekarang.nm_pasien}</h4>
                <p class="text-muted">Mulai: ${new Date(data.antrian_sekarang.Waktu_Perkiraan).toLocaleTimeString('id-ID')}</p>
            `;
        } else {
            antrianSekarangContainer.innerHTML = '<h4 class="text-muted">Belum ada antrian yang dilayani</h4>';
        }

        // Update antrian berikutnya
        const antrianBerikutnyaBody = document.querySelector('#antrian-berikutnya tbody');
        if (data.antrian_berikutnya && data.antrian_berikutnya.length > 0) {
            antrianBerikutnyaBody.innerHTML = data.antrian_berikutnya.map(antrian => `
                <tr>
                    <td>${antrian.ID_Pendaftaran}</td>
                    <td>${antrian.nm_pasien}</td>
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
            queueCount: document.getElementById('queueCount').value,
            youtubeLink: document.getElementById('youtubeLink').value
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

    // Fungsi untuk mengekstrak ID video YouTube dari URL
    function getYoutubeVideoId(url) {
        if (!url) return null;
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    // Fungsi untuk menerapkan pengaturan
    function applySettings(settings) {
        // Terapkan pengaturan tampilan pengumuman
        const announcementSection = document.querySelector('.col-lg-8');
        announcementSection.style.display = settings.showAnnouncement ? 'block' : 'none';

        // Terapkan pengaturan YouTube
        const youtubeSection = document.getElementById('youtubeSection');
        const youtubePlayer = document.getElementById('youtubePlayer');
        const videoId = getYoutubeVideoId(settings.youtubeLink);

        if (videoId) {
            youtubeSection.style.display = 'block';
            youtubePlayer.innerHTML = `
                <iframe 
                    width="100%" 
                    height="100%" 
                    src="https://www.youtube.com/embed/${videoId}" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
                </iframe>
            `;
        } else {
            youtubeSection.style.display = 'none';
            youtubePlayer.innerHTML = '';
        }

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
            document.getElementById('youtubeLink').value = settings.youtubeLink || '';
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