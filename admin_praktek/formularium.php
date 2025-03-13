<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

// Cek apakah user adalah admin
if (!isAdmin()) {
    header('Location: ' . $base_url . '/login.php');
    exit;
}

// Fungsi untuk membuat UUID v4
function generateUUID()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

// Fungsi untuk format harga
function formatRupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Daftar kategori obat
$kategori_list = [
    'Analgesik',
    'Antibiotik',
    'Antiinflamasi',
    'Antihipertensi',
    'Antidiabetes',
    'Vitamin dan Suplemen',
    'Hormon',
    'Obat Kulit',
    'Obat Mata',
    'Obat Saluran Pencernaan',
    'Obat Saluran Pernapasan',
    'Lainnya'
];

// Daftar bentuk sediaan
$bentuk_sediaan_list = [
    'Tablet',
    'Kapsul',
    'Sirup',
    'Suspensi',
    'Injeksi',
    'Salep',
    'Krim',
    'Tetes',
    'Suppositoria',
    'Inhaler',
    'Lainnya'
];

// Proses tambah data
if (isset($_POST['tambah'])) {
    $id_obat = generateUUID();
    $nama_obat = $_POST['nama_obat'];
    $nama_generik = $_POST['nama_generik'] ?? '';
    $bentuk_sediaan = $_POST['bentuk_sediaan'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $catatan_obat = $_POST['catatan_obat'] ?? '';
    $harga = $_POST['harga'];
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    try {
        $stmt = $conn->prepare("INSERT INTO formularium (id_obat, nama_obat, nama_generik, bentuk_sediaan, kategori, catatan_obat, harga, status_aktif) 
                VALUES (:id_obat, :nama_obat, :nama_generik, :bentuk_sediaan, :kategori, :catatan_obat, :harga, :status_aktif)");

        $stmt->bindParam(':id_obat', $id_obat);
        $stmt->bindParam(':nama_obat', $nama_obat);
        $stmt->bindParam(':nama_generik', $nama_generik);
        $stmt->bindParam(':bentuk_sediaan', $bentuk_sediaan);
        $stmt->bindParam(':kategori', $kategori);
        $stmt->bindParam(':catatan_obat', $catatan_obat);
        $stmt->bindParam(':harga', $harga);
        $stmt->bindParam(':status_aktif', $status_aktif);

        $stmt->execute();
        $success_message = "Data obat berhasil ditambahkan";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Proses edit data
if (isset($_POST['edit'])) {
    $id_obat = $_POST['id_obat'];
    $nama_obat = $_POST['nama_obat'];
    $nama_generik = $_POST['nama_generik'] ?? '';
    $bentuk_sediaan = $_POST['bentuk_sediaan'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $catatan_obat = $_POST['catatan_obat'] ?? '';
    $harga = $_POST['harga'];
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    try {
        $stmt = $conn->prepare("UPDATE formularium SET 
                nama_obat = :nama_obat,
                nama_generik = :nama_generik,
                bentuk_sediaan = :bentuk_sediaan,
                kategori = :kategori,
                catatan_obat = :catatan_obat,
                harga = :harga,
                status_aktif = :status_aktif
                WHERE id_obat = :id_obat");

        $stmt->bindParam(':id_obat', $id_obat);
        $stmt->bindParam(':nama_obat', $nama_obat);
        $stmt->bindParam(':nama_generik', $nama_generik);
        $stmt->bindParam(':bentuk_sediaan', $bentuk_sediaan);
        $stmt->bindParam(':kategori', $kategori);
        $stmt->bindParam(':catatan_obat', $catatan_obat);
        $stmt->bindParam(':harga', $harga);
        $stmt->bindParam(':status_aktif', $status_aktif);

        $stmt->execute();
        $success_message = "Data obat berhasil diperbarui";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Proses hapus data
if (isset($_GET['hapus'])) {
    $id_obat = $_GET['hapus'];

    try {
        $stmt = $conn->prepare("DELETE FROM formularium WHERE id_obat = :id_obat");
        $stmt->bindParam(':id_obat', $id_obat);
        $stmt->execute();
        $success_message = "Data obat berhasil dihapus";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Ambil data untuk ditampilkan
try {
    $stmt = $conn->query("SELECT * FROM formularium ORDER BY nama_obat ASC");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
    $result = [];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formularium - Admin</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= $base_url ?>/assets/css/styles.css" rel="stylesheet">
</head>

<body>
    <?php include_once '../template/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-12">
                    <h2 class="page-title">Formularium</h2>
                    <p class="text-muted">Kelola data obat dan formularium klinik</p>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Obat</h5>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="bi bi-plus-circle"></i> Tambah Obat
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabelFormularium" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Obat</th>
                                            <th>Nama Generik</th>
                                            <th>Bentuk Sediaan</th>
                                            <th>Kategori</th>
                                            <th>Harga</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        foreach ($result as $row):
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['nama_obat']) ?></td>
                                                <td><?= htmlspecialchars($row['nama_generik']) ?></td>
                                                <td><?= htmlspecialchars($row['bentuk_sediaan']) ?></td>
                                                <td><?= htmlspecialchars($row['kategori']) ?></td>
                                                <td><?= formatRupiah($row['harga']) ?></td>
                                                <td>
                                                    <span class="badge <?= $row['status_aktif'] ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= $row['status_aktif'] ? 'Aktif' : 'Nonaktif' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-info"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEdit"
                                                        data-id="<?= $row['id_obat'] ?>"
                                                        data-nama="<?= htmlspecialchars($row['nama_obat']) ?>"
                                                        data-generik="<?= htmlspecialchars($row['nama_generik']) ?>"
                                                        data-bentuk="<?= htmlspecialchars($row['bentuk_sediaan']) ?>"
                                                        data-kategori="<?= htmlspecialchars($row['kategori']) ?>"
                                                        data-catatan="<?= htmlspecialchars($row['catatan_obat']) ?>"
                                                        data-harga="<?= $row['harga'] ?>"
                                                        data-status="<?= $row['status_aktif'] ?>">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <a href="#" class="btn btn-sm btn-danger btn-hapus"
                                                        data-id="<?= $row['id_obat'] ?>"
                                                        data-nama="<?= htmlspecialchars($row['nama_obat']) ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahLabel">Tambah Obat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama_obat" class="form-label">Nama Obat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_obat" name="nama_obat" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_generik" class="form-label">Nama Generik</label>
                            <input type="text" class="form-control" id="nama_generik" name="nama_generik">
                        </div>
                        <div class="mb-3">
                            <label for="bentuk_sediaan" class="form-label">Bentuk Sediaan</label>
                            <select class="form-select" id="bentuk_sediaan" name="bentuk_sediaan">
                                <option value="">Pilih Bentuk Sediaan</option>
                                <?php foreach ($bentuk_sediaan_list as $bentuk): ?>
                                    <option value="<?= $bentuk ?>"><?= $bentuk ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="kategori" class="form-label">Kategori</label>
                            <select class="form-select" id="kategori" name="kategori">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategori_list as $kategori): ?>
                                    <option value="<?= $kategori ?>"><?= $kategori ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="harga" class="form-label">Harga <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga" name="harga" min="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="catatan_obat" class="form-label">Catatan</label>
                            <textarea class="form-control" id="catatan_obat" name="catatan_obat" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="status_aktif" name="status_aktif" checked>
                                <label class="form-check-label" for="status_aktif">Status Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditLabel">Edit Obat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_id_obat" name="id_obat">
                        <div class="mb-3">
                            <label for="edit_nama_obat" class="form-label">Nama Obat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_nama_obat" name="nama_obat" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_nama_generik" class="form-label">Nama Generik</label>
                            <input type="text" class="form-control" id="edit_nama_generik" name="nama_generik">
                        </div>
                        <div class="mb-3">
                            <label for="edit_bentuk_sediaan" class="form-label">Bentuk Sediaan</label>
                            <select class="form-select" id="edit_bentuk_sediaan" name="bentuk_sediaan">
                                <option value="">Pilih Bentuk Sediaan</option>
                                <?php foreach ($bentuk_sediaan_list as $bentuk): ?>
                                    <option value="<?= $bentuk ?>"><?= $bentuk ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_kategori" class="form-label">Kategori</label>
                            <select class="form-select" id="edit_kategori" name="kategori">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategori_list as $kategori): ?>
                                    <option value="<?= $kategori ?>"><?= $kategori ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_harga" class="form-label">Harga <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="edit_harga" name="harga" min="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_catatan_obat" class="form-label">Catatan</label>
                            <textarea class="form-control" id="edit_catatan_obat" name="catatan_obat" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit_status_aktif" name="status_aktif">
                                <label class="form-check-label" for="edit_status_aktif">Status Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#tabelFormularium').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });

            // Mengisi data ke modal edit
            $('#modalEdit').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var nama = button.data('nama');
                var generik = button.data('generik');
                var bentuk = button.data('bentuk');
                var kategori = button.data('kategori');
                var catatan = button.data('catatan');
                var harga = button.data('harga');
                var status = button.data('status');

                var modal = $(this);
                modal.find('#edit_id_obat').val(id);
                modal.find('#edit_nama_obat').val(nama);
                modal.find('#edit_nama_generik').val(generik);
                modal.find('#edit_bentuk_sediaan').val(bentuk);
                modal.find('#edit_kategori').val(kategori);
                modal.find('#edit_catatan_obat').val(catatan);
                modal.find('#edit_harga').val(harga);
                modal.find('#edit_status_aktif').prop('checked', status == 1);
            });

            // Konfirmasi hapus dengan SweetAlert2
            $('.btn-hapus').on('click', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var nama = $(this).data('nama');

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Anda yakin ingin menghapus obat "${nama}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `?hapus=${id}`;
                    }
                });
            });

            // Auto-hide alert setelah 5 detik
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>

</html>