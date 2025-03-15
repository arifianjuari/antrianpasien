<?php
// Widget Pengumuman
// File ini akan diinclude di halaman beranda atau halaman lain yang memerlukan widget pengumuman

// Kredensial database jika belum ada koneksi
if (!isset($conn) || !($conn instanceof mysqli)) {
    $db_host = 'auth-db1151.hstgr.io';
    $db_username = 'u609399718_adminpraktek';
    $db_password = 'Obgin@12345';
    $db_database = 'u609399718_praktekobgin';

    // Buat koneksi
    $conn = new mysqli($db_host, $db_username, $db_password, $db_database);

    // Cek koneksi database
    if ($conn->connect_error) {
        error_log("Error koneksi database di widget pengumuman: " . $conn->connect_error);
        return; // Keluar dari widget jika koneksi gagal
    }

    $conn_created_here = true; // Tandai bahwa koneksi dibuat di sini
}

// Update status pengumuman yang sudah melewati tanggal_berakhir
// Hanya jalankan update ini sekali per hari menggunakan session
$update_key = 'pengumuman_status_updated_' . date('Y-m-d');
if (!isset($_SESSION[$update_key])) {
    $current_date = date('Y-m-d');

    // Update status_aktif menjadi 0 untuk pengumuman yang sudah melewati tanggal_berakhir
    $update_query = "UPDATE pengumuman 
                    SET status_aktif = 0 
                    WHERE status_aktif = 1 
                    AND tanggal_berakhir IS NOT NULL 
                    AND tanggal_berakhir < ?";

    try {
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("s", $current_date);
        $update_stmt->execute();
        $affected_rows = $update_stmt->affected_rows;

        if ($affected_rows > 0) {
            error_log(date('Y-m-d H:i:s') . " - " . $affected_rows . " pengumuman dinonaktifkan karena sudah melewati tanggal_berakhir.");
        }

        $update_stmt->close();
    } catch (Exception $e) {
        error_log("Error mengupdate status pengumuman: " . $e->getMessage());
    }

    // Tandai bahwa update sudah dilakukan hari ini
    $_SESSION[$update_key] = true;
}

// Ambil pengumuman aktif (maksimal 2)
$pengumuman_widget = [];
$current_date = date('Y-m-d');

$query = "SELECT p.*, u.username 
          FROM pengumuman p 
          LEFT JOIN users u ON p.dibuat_oleh = u.id 
          WHERE p.status_aktif = 1 
          AND p.tanggal_mulai <= ? 
          AND (p.tanggal_berakhir IS NULL OR p.tanggal_berakhir >= ?)
          ORDER BY p.created_at DESC
          LIMIT 2";

try {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $current_date, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pengumuman_widget[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error mengambil data pengumuman: " . $e->getMessage());
}

// Tutup koneksi jika dibuat di sini
if (isset($conn_created_here) && $conn_created_here) {
    $conn->close();
}
?>

<?php if (count($pengumuman_widget) > 0): ?>
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center" style="background-color: #e67e22 !important;">
            <h5 class="mb-0"><i class="bi bi-megaphone"></i> Pengumuman Terbaru</h5>
            <a href="<?php echo $base_url; ?>/pengumuman.php" class="btn btn-sm btn-light">Lihat Semua</a>
        </div>
        <div class="card-body p-0">
            <?php if (count($pengumuman_widget) === 2): ?>
                <!-- Tampilan untuk 2 pengumuman -->
                <div class="row g-0">
                    <?php foreach ($pengumuman_widget as $item): ?>
                        <div class="col-md-6 border-end">
                            <div class="p-3 h-100 d-flex flex-column">
                                <h6 class="card-title text-primary"><?php echo htmlspecialchars($item['judul']); ?></h6>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-calendar-event"></i>
                                    <?php echo date('d-m-Y', strtotime($item['tanggal_mulai'])); ?>
                                    <?php if (!empty($item['tanggal_berakhir'])): ?>
                                        s/d <?php echo date('d-m-Y', strtotime($item['tanggal_berakhir'])); ?>
                                    <?php endif; ?>
                                </p>
                                <div class="pengumuman-preview mb-2 flex-grow-1" style="overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                    <?php
                                    // Strip HTML tags dan potong teks
                                    $preview = strip_tags($item['isi_pengumuman']);
                                    echo strlen($preview) > 80 ? substr($preview, 0, 80) . '...' : $preview;
                                    ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-auto view-pengumuman"
                                    data-bs-toggle="modal" data-bs-target="#pengumumanModal"
                                    data-id="<?php echo $item['id_pengumuman']; ?>"
                                    data-judul="<?php echo htmlspecialchars($item['judul']); ?>"
                                    data-isi="<?php echo htmlspecialchars($item['isi_pengumuman']); ?>"
                                    data-mulai="<?php echo date('d-m-Y', strtotime($item['tanggal_mulai'])); ?>"
                                    data-berakhir="<?php echo !empty($item['tanggal_berakhir']) ? date('d-m-Y', strtotime($item['tanggal_berakhir'])) : '-'; ?>"
                                    data-penulis="<?php echo htmlspecialchars($item['username'] ?? 'Admin'); ?>">
                                    <i class="bi bi-eye"></i> Baca Selengkapnya
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Tampilan untuk 1 pengumuman -->
                <div class="list-group list-group-flush">
                    <?php foreach ($pengumuman_widget as $item): ?>
                        <div class="list-group-item list-group-item-action p-3">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 text-primary"><?php echo htmlspecialchars($item['judul']); ?></h6>
                                <small class="text-muted">
                                    <?php echo date('d-m-Y', strtotime($item['tanggal_mulai'])); ?>
                                </small>
                            </div>
                            <div class="pengumuman-preview mb-2" style="overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                <?php
                                // Strip HTML tags dan potong teks
                                $preview = strip_tags($item['isi_pengumuman']);
                                echo strlen($preview) > 100 ? substr($preview, 0, 100) . '...' : $preview;
                                ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary view-pengumuman"
                                data-bs-toggle="modal" data-bs-target="#pengumumanModal"
                                data-id="<?php echo $item['id_pengumuman']; ?>"
                                data-judul="<?php echo htmlspecialchars($item['judul']); ?>"
                                data-isi="<?php echo htmlspecialchars($item['isi_pengumuman']); ?>"
                                data-mulai="<?php echo date('d-m-Y', strtotime($item['tanggal_mulai'])); ?>"
                                data-berakhir="<?php echo !empty($item['tanggal_berakhir']) ? date('d-m-Y', strtotime($item['tanggal_berakhir'])) : '-'; ?>"
                                data-penulis="<?php echo htmlspecialchars($item['username'] ?? 'Admin'); ?>">
                                <i class="bi bi-eye"></i> Baca Selengkapnya
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Detail Pengumuman -->
    <div class="modal fade" id="pengumumanModal" tabindex="-1" aria-labelledby="pengumumanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pengumumanModalLabel">Detail Pengumuman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 id="modal-judul" class="mb-3"></h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Tanggal:</strong> <span id="modal-tanggal"></span></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p><strong>Oleh:</strong> <span id="modal-penulis"></span></p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div id="modal-isi"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Tampilkan detail pengumuman pada modal
            $('.view-pengumuman').click(function() {
                const judul = $(this).data('judul');
                const isi = $(this).data('isi');
                const mulai = $(this).data('mulai');
                const berakhir = $(this).data('berakhir');
                const penulis = $(this).data('penulis');

                let tanggalText = mulai;
                if (berakhir !== '-') {
                    tanggalText += ' s/d ' + berakhir;
                }

                $('#modal-judul').text(judul);
                $('#modal-isi').html(isi);
                $('#modal-tanggal').text(tanggalText);
                $('#modal-penulis').text(penulis);
            });
        });
    </script>
<?php endif; ?>