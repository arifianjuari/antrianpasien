<?php
require_once 'config/database.php';
require_once 'modules/rekam_medis/models/RekamMedis.php';
require_once 'modules/rekam_medis/models/TindakanMedis.php';
require_once 'modules/rekam_medis/models/StatusGinekologi.php';

class RekamMedisController
{
    private $rekamMedisModel;
    private $tindakanMedisModel;
    private $pdo;

    public function __construct($pdo)
    {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            error_log("Invalid PDO connection passed to RekamMedisController");
            throw new Exception("Koneksi database tidak valid");
        }

        try {
            // Test koneksi
            $test = $pdo->query("SELECT 1");
            if (!$test) {
                throw new PDOException("Koneksi database tidak dapat melakukan query");
            }
            error_log("Database connection test successful in RekamMedisController");
        } catch (PDOException $e) {
            error_log("Database test failed in RekamMedisController: " . $e->getMessage());
            throw new Exception("Koneksi database bermasalah: " . $e->getMessage());
        }

        $this->pdo = $pdo;
        $this->rekamMedisModel = new RekamMedis($pdo);
        $this->tindakanMedisModel = new TindakanMedis($pdo);
    }

    public function index()
    {
        // Alihkan ke halaman data pasien sebagai halaman utama
        header('Location: index.php?module=rekam_medis&action=data_pasien');
        exit;
    }

    public function dataPasien()
    {
        // Debugging
        echo "<!-- Debug: dataPasien() function called -->";

        // Inisialisasi variabel pencarian
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10; // Jumlah data per halaman
        $offset = ($page - 1) * $limit;

        try {
            // Query untuk menghitung total data
            $count_query = "SELECT COUNT(*) FROM pasien";
            if (!empty($search)) {
                $count_query .= " WHERE no_rkm_medis LIKE :search OR nm_pasien LIKE :search OR no_ktp LIKE :search";
            }

            $count_stmt = $this->pdo->prepare($count_query);
            if (!empty($search)) {
                $count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            }
            $count_stmt->execute();
            $total_records = $count_stmt->fetchColumn();

            // Hitung total halaman
            $total_pages = ceil($total_records / $limit);

            // Query untuk mengambil data pasien
            $query = "SELECT * FROM pasien";
            if (!empty($search)) {
                $query .= " WHERE no_rkm_medis LIKE :search OR nm_pasien LIKE :search OR no_ktp LIKE :search";
            }
            $query .= " ORDER BY nm_pasien ASC LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($query);
            if (!empty($search)) {
                $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $pasien = $stmt->fetchAll();

            // Debugging
            echo "<!-- Debug: Total pasien: " . count($pasien) . " -->";
        } catch (PDOException $e) {
            // Debugging
            echo "<!-- Debug: Error: " . $e->getMessage() . " -->";

            $_SESSION['error'] = "Error: " . $e->getMessage();
            $pasien = [];
            $total_pages = 0;
        }

        // Data wilayah statis
        $kecamatan = [
            ['kd_kec' => '1', 'nm_kec' => 'Batu'],
            ['kd_kec' => '2', 'nm_kec' => 'Bumiaji'],
            ['kd_kec' => '3', 'nm_kec' => 'Junrejo'],
            ['kd_kec' => '4', 'nm_kec' => 'Pujon'],
            ['kd_kec' => '5', 'nm_kec' => 'Ngantang'],
            ['kd_kec' => '6', 'nm_kec' => 'Lainnya']
        ];

        $kelurahan = [
            ['kd_kel' => '1', 'nm_kel' => 'Sisir'],
            ['kd_kel' => '2', 'nm_kel' => 'Temas'],
            ['kd_kel' => '3', 'nm_kel' => 'Ngaglik'],
            ['kd_kel' => '4', 'nm_kel' => 'Songgokerto'],
            ['kd_kel' => '5', 'nm_kel' => 'Lainnya']
        ];

        $kabupaten = [
            ['kd_kab' => '1', 'nm_kab' => 'Kota Batu'],
            ['kd_kab' => '2', 'nm_kab' => 'Kota Malang'],
            ['kd_kab' => '3', 'nm_kab' => 'Kabupaten Malang'],
            ['kd_kab' => '4', 'nm_kab' => 'Lainnya']
        ];

        $cara_bayar = [
            ['kd_pj' => 'UMU', 'nm_pj' => 'Umum'],
            ['kd_pj' => 'BPJ', 'nm_pj' => 'BPJS'],
            ['kd_pj' => 'ASR', 'nm_pj' => 'Asuransi'],
            ['kd_pj' => 'KOR', 'nm_pj' => 'Korporasi']
        ];

        include 'modules/rekam_medis/views/data_pasien.php';
    }

    public function cariPasien()
    {
        $keyword = $_POST['keyword'] ?? '';

        if (empty($keyword)) {
            header('Location: index.php?module=rekam_medis&action=data_pasien');
            exit;
        }

        // Redirect ke halaman data pasien dengan parameter pencarian
        header('Location: index.php?module=rekam_medis&action=data_pasien&search=' . urlencode($keyword));
        exit;
    }

    public function tambahPasien()
    {
        // Data wilayah statis
        $kecamatan = [
            ['kd_kec' => '1', 'nm_kec' => 'Batu'],
            ['kd_kec' => '2', 'nm_kec' => 'Bumiaji'],
            ['kd_kec' => '3', 'nm_kec' => 'Junrejo'],
            ['kd_kec' => '4', 'nm_kec' => 'Pujon'],
            ['kd_kec' => '5', 'nm_kec' => 'Ngantang'],
            ['kd_kec' => '6', 'nm_kec' => 'Lainnya']
        ];

        $kelurahan = [
            ['kd_kel' => '1', 'nm_kel' => 'Sisir'],
            ['kd_kel' => '2', 'nm_kel' => 'Temas'],
            ['kd_kel' => '3', 'nm_kel' => 'Ngaglik'],
            ['kd_kel' => '4', 'nm_kel' => 'Songgokerto'],
            ['kd_kel' => '5', 'nm_kel' => 'Lainnya']
        ];

        $kabupaten = [
            ['kd_kab' => '1', 'nm_kab' => 'Kota Batu'],
            ['kd_kab' => '2', 'nm_kab' => 'Kota Malang'],
            ['kd_kab' => '3', 'nm_kab' => 'Kabupaten Malang'],
            ['kd_kab' => '4', 'nm_kab' => 'Lainnya']
        ];

        $cara_bayar = [
            ['kd_pj' => 'UMU', 'nm_pj' => 'Umum'],
            ['kd_pj' => 'BPJ', 'nm_pj' => 'BPJS'],
            ['kd_pj' => 'ASR', 'nm_pj' => 'Asuransi'],
            ['kd_pj' => 'KOR', 'nm_pj' => 'Korporasi']
        ];

        include 'modules/rekam_medis/views/form_tambah_pasien.php';
    }

    public function simpanPasien()
    {
        // Ambil data dari form
        $nama_pasien = $_POST['nama_pasien'] ?? '';
        $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
        $tgl_lahir = $_POST['tgl_lahir'] ?? '';
        $alamat = $_POST['alamat'] ?? '';
        $pekerjaan = $_POST['pekerjaan'] ?? '';
        $no_tlp = $_POST['no_tlp'] ?? '';
        $no_ktp = $_POST['no_ktp'] ?? '';
        $kd_kec = $_POST['kd_kec'] ?? '';
        $nm_ibu = $_POST['nm_ibu'] ?? '';
        $namakeluarga = $_POST['namakeluarga'] ?? '';
        $kd_pj = $_POST['kd_pj'] ?? '';
        $kd_kel = $_POST['kd_kel'] ?? '';
        $kd_kab = $_POST['kd_kab'] ?? '';

        // Validasi data
        if (empty($nama_pasien) || empty($jenis_kelamin) || empty($tgl_lahir)) {
            $_SESSION['error'] = 'Data pasien tidak lengkap';
            header('Location: index.php?module=rekam_medis&action=tambah_pasien');
            exit;
        }

        try {
            // Generate nomor rekam medis
            $stmt = $this->pdo->prepare("SELECT MAX(CAST(no_rkm_medis AS UNSIGNED)) as max_id FROM pasien");
            $stmt->execute();
            $result = $stmt->fetch();
            $next_id = (int)$result['max_id'] + 1;
            $no_rkm_medis = str_pad($next_id, 6, '0', STR_PAD_LEFT);

            // Hitung umur
            $umur = date_diff(date_create($tgl_lahir), date_create('today'))->y;
            $tgl_daftar = date('Y-m-d');

            // Simpan data pasien
            $stmt = $this->pdo->prepare("
                INSERT INTO pasien (
                    no_rkm_medis, nm_pasien, jk, tgl_lahir, alamat, pekerjaan, 
                    no_tlp, umur, kd_kec, nm_ibu, namakeluarga, kd_pj, 
                    kd_kel, kd_kab, tgl_daftar, no_ktp
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");

            $stmt->execute([
                $no_rkm_medis,
                $nama_pasien,
                $jenis_kelamin,
                $tgl_lahir,
                $alamat,
                $pekerjaan,
                $no_tlp,
                $umur,
                $kd_kec,
                $nm_ibu,
                $namakeluarga,
                $kd_pj,
                $kd_kel,
                $kd_kab,
                $tgl_daftar,
                $no_ktp
            ]);

            $_SESSION['success'] = 'Data pasien berhasil ditambahkan';
            header('Location: index.php?module=rekam_medis&action=data_pasien');
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Gagal menambahkan data pasien: ' . $e->getMessage();
            header('Location: index.php?module=rekam_medis&action=tambah_pasien');
        }
        exit;
    }

    public function updatePasien()
    {
        // Validasi data
        $no_rkm_medis = $_POST['no_rkm_medis'] ?? '';

        if (empty($no_rkm_medis)) {
            $_SESSION['error'] = 'Parameter ID pasien tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        // Hitung umur berdasarkan tanggal lahir
        $tgl_lahir = $_POST['tgl_lahir'] ?? '';
        $umur = '';
        if (!empty($tgl_lahir)) {
            $umur = date_diff(date_create($tgl_lahir), date_create('today'))->y;
        }

        // Data pasien yang akan diupdate
        $data = [
            'nm_pasien' => $_POST['nm_pasien'] ?? '',
            'jk' => $_POST['jk'] ?? '',
            'tgl_lahir' => $tgl_lahir,
            'umur' => $umur,
            'alamat' => $_POST['alamat'] ?? '',
            'kd_kel' => $_POST['kd_kel'] ?? '',
            'kd_kec' => $_POST['kd_kec'] ?? '',
            'kd_kab' => $_POST['kd_kab'] ?? '',
            'no_tlp' => $_POST['no_tlp'] ?? '',
            'pekerjaan' => $_POST['pekerjaan'] ?? '',
            'agama' => $_POST['agama'] ?? '',
            'stts_nikah' => $_POST['stts_nikah'] ?? '',
            'kd_pj' => $_POST['kd_pj'] ?? '',
            'no_peserta' => $_POST['no_peserta'] ?? '',
            'tmp_lahir' => $_POST['tmp_lahir'] ?? '',
            'email' => $_POST['email'] ?? '',
            'keluarga' => $_POST['keluarga'] ?? '',
            'namakeluarga' => $_POST['namakeluarga'] ?? '',
            'nm_ibu' => $_POST['nm_ibu'] ?? '',
            'no_ktp' => $_POST['no_ktp'] ?? '',
            'gol_darah' => $_POST['gol_darah'] ?? '',
            'pnd' => $_POST['pnd'] ?? '',
            'tgl_daftar' => $_POST['tgl_daftar'] ?? ''
        ];

        try {
            // Update data pasien
            $result = $this->rekamMedisModel->updatePasien($no_rkm_medis, $data);

            if ($result) {
                $_SESSION['success'] = 'Data pasien berhasil diperbarui';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui data pasien';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
        }

        // Redirect ke halaman detail pasien dengan parameter refresh dan waktu untuk memastikan cache browser tidak digunakan
        header('Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=' . $no_rkm_medis . '&refresh=1&t=' . time());
        exit;
    }

    public function detailPasien($no_rkm_medis)
    {
        try {
            // Log untuk debugging
            error_log("detailPasien called for no_rkm_medis: " . $no_rkm_medis);

            // Pastikan koneksi database menggunakan kredensial yang benar
            global $db2_host, $db2_username, $db2_password, $db2_database;
            error_log("Using database: $db2_host, $db2_database");

            // Cek koneksi database yang digunakan model
            error_log("Model PDO connection: " . ($this->rekamMedisModel->getPdoStatus() ? "Connected" : "Not connected"));

            $pasien = $this->rekamMedisModel->getPasienById($no_rkm_medis);
            if (!$pasien) {
                error_log("Pasien tidak ditemukan: " . $no_rkm_medis);
                echo "<div class='alert alert-danger'>Data pasien tidak ditemukan</div>";
                return;
            }

            error_log("Pasien ditemukan: " . json_encode($pasien));

            // Data wilayah statis
            $kecamatan = [
                ['kd_kec' => '1', 'nm_kec' => 'Batu'],
                ['kd_kec' => '2', 'nm_kec' => 'Bumiaji'],
                ['kd_kec' => '3', 'nm_kec' => 'Junrejo'],
                ['kd_kec' => '4', 'nm_kec' => 'Pujon'],
                ['kd_kec' => '5', 'nm_kec' => 'Ngantang'],
                ['kd_kec' => '6', 'nm_kec' => 'Lainnya']
            ];

            $kabupaten = [
                ['kd_kab' => '1', 'nm_kab' => 'Kota Batu'],
                ['kd_kab' => '2', 'nm_kab' => 'Kota Malang'],
                ['kd_kab' => '3', 'nm_kab' => 'Kabupaten Malang'],
                ['kd_kab' => '4', 'nm_kab' => 'Lainnya']
            ];

            // Ambil riwayat pemeriksaan
            $riwayatPemeriksaan = $this->rekamMedisModel->getRiwayatPemeriksaan($no_rkm_medis);
            error_log("Riwayat pemeriksaan count in controller: " . count($riwayatPemeriksaan));

            // Jika tidak ada riwayat pemeriksaan, coba ambil langsung dari database
            if (empty($riwayatPemeriksaan)) {
                error_log("No records found via model, trying direct database query");
                try {
                    // Buat koneksi langsung ke database
                    $directPdo = new PDO(
                        "mysql:host=$db2_host;dbname=$db2_database;charset=utf8mb4",
                        $db2_username,
                        $db2_password,
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );

                    // Query langsung ke tabel reg_periksa
                    $stmt = $directPdo->prepare("
                        SELECT * FROM reg_periksa 
                        WHERE no_rkm_medis = ? 
                        ORDER BY tgl_registrasi DESC, jam_reg DESC
                    ");
                    $stmt->execute([$no_rkm_medis]);
                    $riwayatPemeriksaan = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    error_log("Direct query found " . count($riwayatPemeriksaan) . " records");
                } catch (PDOException $e) {
                    error_log("Error in direct database query: " . $e->getMessage());
                }
            }

            // Data lainnya tetap sama
            $skriningKehamilan = $this->rekamMedisModel->getSkriningKehamilan($no_rkm_medis);
            $riwayatKehamilan = $this->rekamMedisModel->getRiwayatKehamilan($no_rkm_medis);
            $statusObstetri = $this->rekamMedisModel->getStatusObstetri($no_rkm_medis);

            // Memuat data status ginekologi
            $statusGinekologiModel = new StatusGinekologi($this->pdo);
            $statusGinekologi = $statusGinekologiModel->getStatusGinekologiByPasien($no_rkm_medis);

            $riwayatPenilaianRalan = $this->rekamMedisModel->getRiwayatPenilaianMedis($no_rkm_medis);
            $riwayatPemeriksaanObstetri = $this->rekamMedisModel->getRiwayatPemeriksaanObstetri($no_rkm_medis);
            $riwayatPemeriksaanGinekologi = $this->rekamMedisModel->getRiwayatPemeriksaanGinekologi($no_rkm_medis);

            // Tampilkan view dengan path yang benar
            error_log("Mencoba menampilkan view detail_pasien.php");
            include 'modules/rekam_medis/views/detail_pasien.php';
            error_log("View detail_pasien.php berhasil ditampilkan");
        } catch (Exception $e) {
            error_log("Error di detailPasien: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
            echo "<a href='index.php?module=rekam_medis' class='btn btn-primary'>Kembali</a>";
        }
    }

    public function tambahTindakanMedis()
    {
        // Ambil daftar dokter
        $stmt = $this->pdo->prepare("SELECT * FROM dokter WHERE Status_Aktif = 1");
        $stmt->execute();
        $dokter = $stmt->fetchAll();

        include 'modules/rekam_medis/views/form_tindakan_medis.php';
    }

    public function simpanTindakanMedis()
    {
        $no_rkm_medis = $_POST['no_rkm_medis'] ?? '';
        $ID_Dokter = $_POST['ID_Dokter'] ?? '';
        $tgl_tindakan = $_POST['tgl_tindakan'] ?? date('Y-m-d');
        $jam_tindakan = $_POST['jam_tindakan'] ?? date('H:i:s');
        $kode_tindakan = $_POST['kode_tindakan'] ?? '';
        $nama_tindakan = $_POST['nama_tindakan'] ?? '';
        $deskripsi_tindakan = $_POST['deskripsi_tindakan'] ?? '';
        $hasil_tindakan = $_POST['hasil_tindakan'] ?? '';
        $catatan = $_POST['catatan'] ?? '';

        // Validasi
        if (empty($no_rkm_medis) || empty($ID_Dokter) || empty($nama_tindakan)) {
            $_SESSION['error'] = 'Data tidak lengkap';
            header('Location: index.php?module=rekam_medis&action=tambah_tindakan_medis');
            exit;
        }

        // Simpan tindakan medis
        $data = [
            'no_rkm_medis' => $no_rkm_medis,
            'ID_Dokter' => $ID_Dokter,
            'tgl_tindakan' => $tgl_tindakan,
            'jam_tindakan' => $jam_tindakan,
            'kode_tindakan' => $kode_tindakan,
            'nama_tindakan' => $nama_tindakan,
            'deskripsi_tindakan' => $deskripsi_tindakan,
            'hasil_tindakan' => $hasil_tindakan,
            'catatan' => $catatan
        ];

        $id_tindakan = $this->tindakanMedisModel->createTindakanMedis($data);

        if ($id_tindakan) {
            $_SESSION['success'] = 'Tindakan medis berhasil ditambahkan';
            header('Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=' . $no_rkm_medis);
        } else {
            $_SESSION['error'] = 'Gagal menambahkan tindakan medis';
            header('Location: index.php?module=rekam_medis&action=tambah_tindakan_medis');
        }
        exit;
    }

    public function editTindakanMedis($id)
    {
        // Ambil data tindakan medis
        $tindakan = $this->tindakanMedisModel->getTindakanMedisById($id);

        if (!$tindakan) {
            $_SESSION['error'] = 'Tindakan medis tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        // Ambil daftar dokter
        $stmt = $this->pdo->prepare("SELECT * FROM dokter WHERE Status_Aktif = 1");
        $stmt->execute();
        $dokter = $stmt->fetchAll();

        include 'modules/rekam_medis/views/form_tindakan_medis_edit.php';
    }

    public function updateTindakanMedis()
    {
        $id_tindakan = $_POST['id_tindakan'] ?? '';
        $no_rkm_medis = $_POST['no_rkm_medis'] ?? '';
        $ID_Dokter = $_POST['ID_Dokter'] ?? '';
        $tgl_tindakan = $_POST['tgl_tindakan'] ?? '';
        $jam_tindakan = $_POST['jam_tindakan'] ?? '';
        $kode_tindakan = $_POST['kode_tindakan'] ?? '';
        $nama_tindakan = $_POST['nama_tindakan'] ?? '';
        $deskripsi_tindakan = $_POST['deskripsi_tindakan'] ?? '';
        $hasil_tindakan = $_POST['hasil_tindakan'] ?? '';
        $catatan = $_POST['catatan'] ?? '';

        // Validasi
        if (empty($id_tindakan) || empty($no_rkm_medis) || empty($ID_Dokter) || empty($nama_tindakan)) {
            $_SESSION['error'] = 'Data tidak lengkap';
            header('Location: index.php?module=rekam_medis&action=edit_tindakan_medis&id=' . $id_tindakan);
            exit;
        }

        // Update tindakan medis
        $data = [
            'no_rkm_medis' => $no_rkm_medis,
            'ID_Dokter' => $ID_Dokter,
            'tgl_tindakan' => $tgl_tindakan,
            'jam_tindakan' => $jam_tindakan,
            'kode_tindakan' => $kode_tindakan,
            'nama_tindakan' => $nama_tindakan,
            'deskripsi_tindakan' => $deskripsi_tindakan,
            'hasil_tindakan' => $hasil_tindakan,
            'catatan' => $catatan
        ];

        $result = $this->tindakanMedisModel->updateTindakanMedis($id_tindakan, $data);

        if ($result) {
            $_SESSION['success'] = 'Tindakan medis berhasil diperbarui';
            header('Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=' . $no_rkm_medis);
        } else {
            $_SESSION['error'] = 'Gagal memperbarui tindakan medis';
            header('Location: index.php?module=rekam_medis&action=edit_tindakan_medis&id=' . $id_tindakan);
        }
        exit;
    }

    public function hapusTindakanMedis($id)
    {
        // Ambil data tindakan medis
        $tindakan = $this->tindakanMedisModel->getTindakanMedisById($id);

        if (!$tindakan) {
            $_SESSION['error'] = 'Tindakan medis tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        // Hapus tindakan medis
        $result = $this->tindakanMedisModel->deleteTindakanMedis($id);

        if ($result) {
            $_SESSION['success'] = 'Tindakan medis berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Gagal menghapus tindakan medis';
        }

        header('Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=' . $tindakan['no_rkm_medis']);
        exit;
    }

    public function detailTindakanMedis($id)
    {
        // Ambil data tindakan medis
        $tindakan = $this->tindakanMedisModel->getTindakanMedisById($id);

        if (!$tindakan) {
            $_SESSION['error'] = 'Tindakan medis tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        include 'modules/rekam_medis/views/detail_tindakan_medis.php';
    }

    public function tambahPenilaianMedis()
    {
        // Ambil no_rkm_medis dari parameter URL
        $no_rkm_medis = $_GET['id'] ?? '';

        if (empty($no_rkm_medis)) {
            $_SESSION['error'] = 'Parameter ID pasien tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        // Ambil data pasien
        $stmt = $this->pdo->prepare("SELECT * FROM pasien WHERE no_rkm_medis = ?");
        $stmt->execute([$no_rkm_medis]);
        $pasien = $stmt->fetch();

        if (!$pasien) {
            $_SESSION['error'] = 'Data pasien tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        // Ambil daftar dokter
        $stmt = $this->pdo->prepare("SELECT * FROM dokter WHERE Status_Aktif = 1");
        $stmt->execute();
        $dokter = $stmt->fetchAll();

        include 'modules/rekam_medis/views/form_penilaian_medis.php';
    }

    public function simpanPenilaianMedis()
    {
        error_log("POST Data: " . print_r($_POST, true));
        error_log("GET Data: " . print_r($_GET, true));

        // Validasi field yang diperlukan
        if (empty($_POST['no_rkm_medis']) || empty($_POST['kd_dokter']) || empty($_POST['keluhan_utama'])) {
            error_log("Validasi gagal: Ada field yang kosong");
            $_SESSION['error'] = "Data pasien, dokter, dan keluhan utama harus diisi";
            header('Location: index.php?module=rekam_medis&action=tambah_penilaian_medis&id=' . $_POST['no_rkm_medis']);
            exit;
        }

        try {
            // Dapatkan nomor registrasi terakhir untuk hari ini
            $stmt = $this->pdo->prepare("
                SELECT MAX(CAST(no_reg AS UNSIGNED)) as max_reg 
                FROM reg_periksa 
                WHERE DATE(tgl_registrasi) = CURDATE()
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            $reg_number = ((int)$result['max_reg'] ?? 0) + 1;

            // Format no_reg dengan padding 3 digit
            $no_reg = str_pad($reg_number, 3, '0', STR_PAD_LEFT);

            // Format no_rawat: no_rkm_medis-YYYYMMDD-[nomor urut]
            $no_rawat = sprintf(
                "%s-%s-%d",
                $_POST['no_rkm_medis'],
                date('Ymd'),
                $reg_number
            );

            // Periksa apakah no_reg sudah ada
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reg_periksa WHERE no_reg = ? AND DATE(tgl_registrasi) = CURDATE()");
            $stmt->execute([$no_reg]);
            if ($stmt->fetchColumn() > 0) {
                // Jika sudah ada, tambahkan timestamp untuk memastikan keunikan
                $no_reg = $no_reg . date('His');
            }

            // 1. Buat record reg_periksa
            $stmt = $this->pdo->prepare("
                INSERT INTO reg_periksa (
                    no_reg,
                    no_rawat,
                    tgl_registrasi,
                    jam_reg,
                    kd_dokter,
                    no_rkm_medis,
                    status_lanjut,
                    stts,
                    stts_daftar
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $no_reg,
                $no_rawat,
                date('Y-m-d'),
                date('H:i:s'),
                $_POST['kd_dokter'],
                $_POST['no_rkm_medis'],
                'Ralan',
                'Belum',
                'Baru'
            ]);

            // 2. Simpan ke tabel tindakan_medis
            $stmt = $this->pdo->prepare("
                INSERT INTO tindakan_medis (
                    no_rawat,
                    no_rkm_medis,
                    ID_Dokter,
                    tgl_tindakan,
                    jam_tindakan,
                    nama_tindakan,
                    deskripsi_tindakan,
                    hasil_tindakan,
                    catatan
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $no_rawat,
                $_POST['no_rkm_medis'],
                $_POST['kd_dokter'],
                date('Y-m-d'),
                date('H:i:s'),
                'Pemeriksaan Medis',
                $_POST['keluhan_utama'],
                $_POST['diagnosis'] ?? '',
                $_POST['tata'] ?? ''
            ]);

            // 3. Simpan ke tabel penilaian_medis_ralan_kandungan
            $stmt = $this->pdo->prepare("
                INSERT INTO penilaian_medis_ralan_kandungan (
                    no_rawat,
                    tanggal,
                    kd_dokter,
                    anamnesis,
                    hubungan,
                    keluhan_utama,
                    rps,
                    rpd,
                    rpk,
                    rpo,
                    alergi,
                    keadaan,
                    kesadaran,
                    td,
                    nadi,
                    suhu,
                    rr,
                    bb,
                    tb,
                    tfu,
                    tbj,
                    his,
                    kontraksi,
                    djj,
                    inspeksi,
                    inspekulo,
                    diagnosis,
                    tata
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $no_rawat,
                date('Y-m-d H:i:s'),
                $_POST['kd_dokter'],
                $_POST['anamnesis'] ?? 'Autoanamnesis',
                $_POST['hubungan'] ?? '-',
                $_POST['keluhan_utama'],
                $_POST['rps'] ?? '',
                $_POST['rpd'] ?? '',
                $_POST['rpk'] ?? '',
                $_POST['rpo'] ?? '',
                $_POST['alergi'] ?? '',
                $_POST['keadaan'] ?? 'Sehat',
                $_POST['kesadaran'] ?? 'Compos Mentis',
                $_POST['td'] ?? '',
                $_POST['nadi'] ?? '',
                $_POST['suhu'] ?? '',
                $_POST['rr'] ?? '',
                $_POST['bb'] ?? '',
                $_POST['tb'] ?? '',
                $_POST['tfu'] ?? '',
                $_POST['tbj'] ?? '',
                $_POST['his'] ?? '',
                $_POST['kontraksi'] ?? 'Tidak',
                $_POST['djj'] ?? '',
                $_POST['inspeksi'] ?? '',
                $_POST['inspekulo'] ?? '',
                $_POST['diagnosis'] ?? '',
                $_POST['tata'] ?? ''
            ]);

            $_SESSION['success'] = 'Penilaian medis berhasil disimpan';
            header('Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=' . $_POST['no_rkm_medis']);
            exit;
        } catch (PDOException $e) {
            error_log("Error in simpanPenilaianMedis: " . $e->getMessage());
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            header('Location: index.php?module=rekam_medis&action=tambah_penilaian_medis&id=' . $_POST['no_rkm_medis']);
            exit;
        } catch (Exception $e) {
            error_log("Error in simpanPenilaianMedis: " . $e->getMessage());
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            header('Location: index.php?module=rekam_medis&action=tambah_penilaian_medis&id=' . $_POST['no_rkm_medis']);
            exit;
        }
    }

    public function manajemenAntrian()
    {
        // Tampilkan halaman manajemen antrian
        // View akan menggunakan koneksi database global
        include 'modules/rekam_medis/views/manajemen_antrian.php';
    }

    public function tambahPenilaianRalan()
    {
        $no_rkm_medis = $_GET['id'];

        // Ambil data pasien
        $pasien = $this->rekamMedisModel->getPasienById($no_rkm_medis);
        if (!$pasien) {
            $_SESSION['error'] = 'Pasien tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        // Generate no_rawat
        $tanggal = date('Y-m-d');
        $no_rawat = $no_rkm_medis . '/' . date('Ymd');

        include 'modules/rekam_medis/views/form_penilaian_ralan.php';
    }

    public function simpanPenilaianRalan()
    {
        $data = [
            'no_rawat' => $_POST['no_rkm_medis'] . '/' . date('Ymd'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'tanggal' => date('Y-m-d'),
            'jam' => date('H:i:s'),
            'kd_dokter' => $_SESSION['user_id'], // Sesuaikan dengan ID dokter yang login
            'anamnesis' => $_POST['anamnesis'],
            'hubungan' => $_POST['hubungan'],
            'keluhan_utama' => $_POST['keluhan_utama'],
            'rps' => $_POST['rps'],
            'rpd' => $_POST['rpd'],
            'rpk' => $_POST['rpk'],
            'rpo' => $_POST['rpo'],
            'alergi' => $_POST['alergi'],
            'keadaan' => $_POST['keadaan'],
            'kesadaran' => $_POST['kesadaran'],
            'td' => $_POST['td'],
            'nadi' => $_POST['nadi'],
            'suhu' => $_POST['suhu'],
            'rr' => $_POST['rr'],
            'bb' => $_POST['bb'],
            'tb' => $_POST['tb'],
            'lila' => $_POST['lila'],
            'tfu' => $_POST['tfu'],
            'tbj' => $_POST['tbj'],
            'his' => $_POST['his'],
            'kontraksi' => $_POST['kontraksi'],
            'djj' => $_POST['djj'],
            'inspeksi' => $_POST['inspeksi'],
            'inspekulo' => $_POST['inspekulo'],
            'fluxus' => $_POST['fluxus'],
            'fluor' => $_POST['fluor'],
            'dalam' => $_POST['dalam'],
            'pembukaan' => $_POST['pembukaan'],
            'portio' => $_POST['portio'],
            'ketuban' => $_POST['ketuban'],
            'presentasi' => $_POST['presentasi'],
            'penurunan' => $_POST['penurunan'],
            'denominator' => $_POST['denominator'],
            'ukuran_panggul' => $_POST['ukuran_panggul'],
            'diagnosa' => $_POST['diagnosa'],
            'tindakan' => $_POST['tindakan'],
            'edukasi' => $_POST['edukasi']
        ];

        if ($this->rekamMedisModel->tambahPenilaianMedisRalanKandungan($data)) {
            $_SESSION['success'] = 'Data penilaian medis berhasil disimpan';
        } else {
            $_SESSION['error'] = 'Gagal menyimpan data penilaian medis';
        }

        header('Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=' . $_POST['no_rkm_medis']);
        exit;
    }

    public function editPenilaianRalan()
    {
        $no_rawat = $_GET['id'];

        // Ambil data penilaian medis
        $penilaian_medis = $this->rekamMedisModel->getPenilaianMedisRalanKandunganByNoRawat($no_rawat);
        if (!$penilaian_medis) {
            $_SESSION['error'] = 'Data penilaian medis tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        $no_rkm_medis = $penilaian_medis['no_rkm_medis'];
        include 'modules/rekam_medis/views/form_penilaian_ralan.php';
    }

    public function updatePenilaianRalan()
    {
        $data = [
            'no_rawat' => $_POST['no_rawat'],
            'anamnesis' => $_POST['anamnesis'],
            'hubungan' => $_POST['hubungan'],
            'keluhan_utama' => $_POST['keluhan_utama'],
            'rps' => $_POST['rps'],
            'rpd' => $_POST['rpd'],
            'rpk' => $_POST['rpk'],
            'rpo' => $_POST['rpo'],
            'alergi' => $_POST['alergi'],
            'keadaan' => $_POST['keadaan'],
            'kesadaran' => $_POST['kesadaran'],
            'td' => $_POST['td'],
            'nadi' => $_POST['nadi'],
            'suhu' => $_POST['suhu'],
            'rr' => $_POST['rr'],
            'bb' => $_POST['bb'],
            'tb' => $_POST['tb'],
            'lila' => $_POST['lila'],
            'tfu' => $_POST['tfu'],
            'tbj' => $_POST['tbj'],
            'his' => $_POST['his'],
            'kontraksi' => $_POST['kontraksi'],
            'djj' => $_POST['djj'],
            'inspeksi' => $_POST['inspeksi'],
            'inspekulo' => $_POST['inspekulo'],
            'fluxus' => $_POST['fluxus'],
            'fluor' => $_POST['fluor'],
            'dalam' => $_POST['dalam'],
            'pembukaan' => $_POST['pembukaan'],
            'portio' => $_POST['portio'],
            'ketuban' => $_POST['ketuban'],
            'presentasi' => $_POST['presentasi'],
            'penurunan' => $_POST['penurunan'],
            'denominator' => $_POST['denominator'],
            'ukuran_panggul' => $_POST['ukuran_panggul'],
            'diagnosa' => $_POST['diagnosa'],
            'tindakan' => $_POST['tindakan'],
            'edukasi' => $_POST['edukasi']
        ];

        if ($this->rekamMedisModel->updatePenilaianMedisRalanKandungan($data)) {
            $_SESSION['success'] = 'Data penilaian medis berhasil diperbarui';
        } else {
            $_SESSION['error'] = 'Gagal memperbarui data penilaian medis';
        }

        header('Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=' . $_POST['no_rkm_medis']);
        exit;
    }

    public function detailPenilaianRalan()
    {
        $no_rawat = $_GET['id'];

        // Ambil data penilaian medis
        $penilaian_medis = $this->rekamMedisModel->getPenilaianMedisRalanKandunganByNoRawat($no_rawat);
        if (!$penilaian_medis) {
            $_SESSION['error'] = 'Data penilaian medis tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        include 'modules/rekam_medis/views/detail_penilaian_ralan.php';
    }

    public function tambah_penilaian_medis_ralan_kandungan()
    {
        $no_rkm_medis = $_GET['no_rkm_medis'];

        // Ambil data pasien
        $pasien = $this->rekamMedisModel->getPasienById($no_rkm_medis);
        if (!$pasien) {
            $_SESSION['error'] = 'Pasien tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        // Generate no_rawat
        $tanggal = date('Y-m-d');
        $no_rawat = $no_rkm_medis . '/' . date('Ymd');

        include 'modules/rekam_medis/views/form_penilaian_medis_ralan_kandungan.php';
    }

    public function simpan_penilaian_medis_ralan_kandungan()
    {
        try {
            error_log("=== MULAI PROSES SIMPAN PENILAIAN MEDIS RALAN KANDUNGAN ===");
            error_log("Raw POST Data: " . file_get_contents('php://input'));
            error_log("POST Array: " . print_r($_POST, true));
            error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
            error_log("Content Type: " . $_SERVER['CONTENT_TYPE']);

            // Validasi data yang diperlukan
            if (empty($_POST['no_rawat'])) {
                error_log("no_rawat kosong");
                throw new Exception('No rawat tidak boleh kosong');
            }

            if (empty($_POST['keluhan_utama'])) {
                error_log("keluhan_utama kosong");
                throw new Exception('Keluhan utama tidak boleh kosong');
            }

            // Siapkan data sesuai struktur tabel
            $data = [
                'no_rawat' => $_POST['no_rawat'],
                'tanggal' => date('Y-m-d H:i:s'),
                'anamnesis' => $_POST['anamnesis'] ?? 'Autoanamnesis',
                'hubungan' => $_POST['hubungan'] ?? '-',
                'keluhan_utama' => $_POST['keluhan_utama'],
                'rps' => $_POST['rps'] ?? '',
                'rpd' => $_POST['rpd'] ?? '',
                'rpk' => $_POST['rpk'] ?? '',
                'rpo' => $_POST['rpo'] ?? '',
                'alergi' => $_POST['alergi'] ?? '',
                'keadaan' => $_POST['keadaan'] ?? 'Sehat',
                'gcs' => $_POST['gcs'] ?? '',
                'kesadaran' => $_POST['kesadaran'] ?? 'Compos Mentis',
                'td' => $_POST['td'] ?? '',
                'nadi' => $_POST['nadi'] ?? '',
                'rr' => $_POST['rr'] ?? '',
                'suhu' => $_POST['suhu'] ?? '',
                'spo' => $_POST['spo'] ?? '',
                'bb' => $_POST['bb'] ?? '',
                'tb' => $_POST['tb'] ?? '',
                'kepala' => $_POST['kepala'] ?? 'Normal',
                'mata' => $_POST['mata'] ?? 'Normal',
                'gigi' => $_POST['gigi'] ?? 'Normal',
                'tht' => $_POST['tht'] ?? 'Normal',
                'thoraks' => $_POST['thoraks'] ?? 'Normal',
                'abdomen' => $_POST['abdomen'] ?? 'Normal',
                'genital' => $_POST['genital'] ?? 'Normal',
                'ekstremitas' => $_POST['ekstremitas'] ?? 'Normal',
                'kulit' => $_POST['kulit'] ?? 'Normal',
                'ket_fisik' => $_POST['ket_fisik'] ?? '',
                'ultra' => $_POST['ultra'] ?? '',
                'lab' => $_POST['lab'] ?? '',
                'diagnosis' => $_POST['diagnosis'] ?? '',
                'tata' => $_POST['tata'] ?? ''
            ];

            error_log("Data yang akan disimpan: " . print_r($data, true));

            // Query untuk menyimpan data
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO penilaian_medis_ralan_kandungan ($columns) VALUES ($values)";

            error_log("SQL Query: " . $sql);
            error_log("SQL Values: " . print_r(array_values($data), true));

            $stmt = $this->pdo->prepare($sql);

            if (!$stmt) {
                error_log("Error preparing statement: " . print_r($this->pdo->errorInfo(), true));
                throw new Exception('Gagal mempersiapkan query');
            }

            if ($stmt->execute(array_values($data))) {
                error_log("Data berhasil disimpan");
                $_SESSION['success'] = 'Data penilaian medis berhasil disimpan';

                // Ambil no_rkm_medis dari reg_periksa
                $stmt = $this->pdo->prepare("SELECT no_rkm_medis FROM reg_periksa WHERE no_rawat = ?");
                $stmt->execute([$_POST['no_rawat']]);
                $no_rkm_medis = $stmt->fetchColumn();

                error_log("Redirect ke detail pasien dengan no_rkm_medis: " . $no_rkm_medis);
                header('Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=' . $no_rkm_medis);
                exit;
            } else {
                error_log("Error executing statement: " . print_r($stmt->errorInfo(), true));
                throw new Exception('Gagal menyimpan data penilaian medis: ' . implode(", ", $stmt->errorInfo()));
            }
        } catch (Exception $e) {
            error_log("Error in simpan_penilaian_medis_ralan_kandungan: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    public function simpan_pemeriksaan()
    {
        try {
            error_log("Starting simpan_pemeriksaan");

            // Validasi input
            if (!isset($_POST['no_rkm_medis']) || !isset($_POST['status_bayar'])) {
                throw new Exception("Data yang diperlukan tidak lengkap");
            }

            // Siapkan data untuk disimpan
            $data = [
                'no_rawat' => $_POST['no_rawat'],
                'no_rkm_medis' => $_POST['no_rkm_medis'],
                'tgl_registrasi' => $_POST['tgl_registrasi'],
                'jam_reg' => $_POST['jam_reg'],
                'status_bayar' => $_POST['status_bayar']
            ];

            error_log("Attempting to save pemeriksaan with data: " . json_encode($data));

            // Simpan ke database
            $result = $this->rekamMedisModel->tambahPemeriksaan($data);

            if ($result) {
                $_SESSION['success'] = "Kunjungan baru berhasil ditambahkan";
                header("Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=" . $_POST['no_rkm_medis']);
                exit;
            } else {
                throw new Exception("Gagal menyimpan data kunjungan");
            }
        } catch (Exception $e) {
            error_log("Error in simpan_pemeriksaan: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header("Location: index.php?module=rekam_medis&action=tambah_pemeriksaan&no_rkm_medis=" . $_POST['no_rkm_medis']);
            exit;
        }
    }

    public function editPasien()
    {
        // Set header untuk mencegah caching
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $no_rkm_medis = $_GET['id'] ?? '';

        if (empty($no_rkm_medis)) {
            $_SESSION['error'] = 'Parameter ID pasien tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        // Ambil data pasien dengan parameter waktu untuk mencegah cache
        $pasien = $this->rekamMedisModel->getPasienById($no_rkm_medis);

        // Debug: Log data pasien yang diambil
        error_log("Data pasien untuk form edit: " . json_encode($pasien));

        if (!$pasien) {
            $_SESSION['error'] = 'Data pasien tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        // Data wilayah statis
        $kecamatan = [
            ['kd_kec' => '1', 'nm_kec' => 'Batu'],
            ['kd_kec' => '2', 'nm_kec' => 'Bumiaji'],
            ['kd_kec' => '3', 'nm_kec' => 'Junrejo'],
            ['kd_kec' => '4', 'nm_kec' => 'Pujon'],
            ['kd_kec' => '5', 'nm_kec' => 'Ngantang'],
            ['kd_kec' => '6', 'nm_kec' => 'Lainnya']
        ];

        $kelurahan = [
            ['kd_kel' => '1', 'nm_kel' => 'Sisir'],
            ['kd_kel' => '2', 'nm_kel' => 'Temas'],
            ['kd_kel' => '3', 'nm_kel' => 'Ngaglik'],
            ['kd_kel' => '4', 'nm_kel' => 'Songgokerto'],
            ['kd_kel' => '5', 'nm_kel' => 'Lainnya']
        ];

        $kabupaten = [
            ['kd_kab' => '1', 'nm_kab' => 'Kota Batu'],
            ['kd_kab' => '2', 'nm_kab' => 'Kota Malang'],
            ['kd_kab' => '3', 'nm_kab' => 'Kabupaten Malang'],
            ['kd_kab' => '4', 'nm_kab' => 'Lainnya']
        ];

        $cara_bayar = [
            ['kd_pj' => 'UMU', 'nm_pj' => 'Umum'],
            ['kd_pj' => 'BPJ', 'nm_pj' => 'BPJS'],
            ['kd_pj' => 'ASR', 'nm_pj' => 'Asuransi'],
            ['kd_pj' => 'KOR', 'nm_pj' => 'Korporasi']
        ];

        include 'modules/rekam_medis/views/form_edit_pasien.php';
    }

    public function detail_pemeriksaan()
    {
        $no_rawat = $_GET['id'];
        error_log("detail_pemeriksaan called for no_rawat: " . $no_rawat);

        // Ambil data pemeriksaan
        $pemeriksaan = $this->rekamMedisModel->getPenilaianMedisRalanKandunganByNoRawat($no_rawat);
        if (!$pemeriksaan) {
            $_SESSION['error'] = 'Data pemeriksaan tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        // Ambil data pasien
        $pasien = $this->rekamMedisModel->getPasienById($pemeriksaan['no_rkm_medis']);
        if (!$pasien) {
            $_SESSION['error'] = 'Data pasien tidak ditemukan';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        // Tampilkan view
        include 'modules/rekam_medis/views/detail_pemeriksaan.php';
    }

    public function edit_pemeriksaan()
    {
        error_log("Starting edit_pemeriksaan function");

        if (!isset($_GET['id']) || empty($_GET['id'])) {
            error_log("No ID provided in edit_pemeriksaan");
            $_SESSION['error'] = 'ID pemeriksaan tidak valid';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        $no_rawat = $_GET['id'];
        error_log("Processing edit_pemeriksaan for no_rawat: " . $no_rawat);

        try {
            // Ambil data pemeriksaan dari tabel penilaian_medis_ralan_kandungan
            $stmt = $this->pdo->prepare("
                SELECT * FROM penilaian_medis_ralan_kandungan 
                WHERE no_rawat = ?
            ");
            $stmt->execute([$no_rawat]);
            $pemeriksaan = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pemeriksaan) {
                error_log("No pemeriksaan data found for no_rawat: " . $no_rawat);
                $_SESSION['error'] = 'Data pemeriksaan tidak ditemukan';
                header('Location: index.php?module=rekam_medis');
                exit;
            }

            // Ambil data pasien dari reg_periksa dan pasien
            $stmt = $this->pdo->prepare("
                SELECT p.*, rp.no_rawat, rp.tgl_registrasi, rp.jam_reg
                FROM reg_periksa rp
                JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
                WHERE rp.no_rawat = ?
            ");
            $stmt->execute([$no_rawat]);
            $pasien = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pasien) {
                error_log("No patient data found for no_rawat: " . $no_rawat);
                $_SESSION['error'] = 'Data pasien tidak ditemukan';
                header('Location: index.php?module=rekam_medis');
                exit;
            }

            error_log("Found pemeriksaan and patient data, loading edit form");

            // Tampilkan form edit
            include 'modules/rekam_medis/views/form_edit_pemeriksaan.php';
        } catch (Exception $e) {
            error_log('Error in edit_pemeriksaan: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?module=rekam_medis');
            exit;
        }
    }

    public function update_pemeriksaan()
    {
        error_log("Starting update_pemeriksaan");
        error_log("POST data: " . print_r($_POST, true));

        // Validasi data
        if (!isset($_POST['no_rawat']) || empty($_POST['no_rawat'])) {
            $_SESSION['error'] = 'Data pemeriksaan tidak valid';
            header('Location: index.php?module=rekam_medis');
            exit;
        }

        try {
            // Siapkan data untuk update
            $data = [
                'no_rawat' => $_POST['no_rawat'],
                'keluhan_utama' => $_POST['keluhan_utama'],
                'rps' => $_POST['rps'],
                'rpd' => $_POST['rpd'],
                'alergi' => $_POST['alergi'],
                'gcs' => $_POST['gcs'],
                'td' => $_POST['td'],
                'nadi' => $_POST['nadi'],
                'rr' => $_POST['rr'],
                'suhu' => $_POST['suhu'],
                'spo' => $_POST['spo'],
                'bb' => $_POST['bb'],
                'tb' => $_POST['tb'],
                'kepala' => $_POST['kepala'],
                'mata' => $_POST['mata'],
                'gigi' => $_POST['gigi'],
                'tht' => $_POST['tht'],
                'thoraks' => $_POST['thoraks'],
                'abdomen' => $_POST['abdomen'],
                'genital' => $_POST['genital'],
                'ekstremitas' => $_POST['ekstremitas'],
                'kulit' => $_POST['kulit'],
                'ket_fisik' => $_POST['ket_fisik'],
                'ultra' => $_POST['ultra'],
                'lab' => $_POST['lab'],
                'diagnosis' => $_POST['diagnosis'],
                'tata' => $_POST['tata']
            ];

            // Log data yang akan diupdate
            error_log("Data to update: " . json_encode($data));

            // Update menggunakan model
            $result = $this->rekamMedisModel->updatePemeriksaan($data);

            if ($result) {
                error_log("Update successful");
                $_SESSION['success'] = 'Data pemeriksaan berhasil diperbarui';
            } else {
                error_log("Update failed or no changes made");
                $_SESSION['warning'] = 'Tidak ada perubahan data';
            }

            // Ambil no_rkm_medis untuk redirect
            $stmt = $this->pdo->prepare("SELECT no_rkm_medis FROM reg_periksa WHERE no_rawat = ?");
            $stmt->execute([$_POST['no_rawat']]);
            $no_rkm_medis = $stmt->fetchColumn();

            header('Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=' . $no_rkm_medis . '&refresh=1&t=' . time());
            exit;
        } catch (Exception $e) {
            error_log("Error in update_pemeriksaan: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage();
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    public function tambah_pemeriksaan()
    {
        try {
            if (!isset($_GET['no_rkm_medis'])) {
                throw new Exception('No RM tidak ditemukan');
            }

            $no_rkm_medis = $_GET['no_rkm_medis'];
            $pasien = $this->rekamMedisModel->getPasienById($no_rkm_medis);

            if (!$pasien) {
                throw new Exception('Data pasien tidak ditemukan');
            }

            // Generate nomor rawat dan nomor registrasi
            $tgl_registrasi = date('Y-m-d');
            $no_rawat = $this->rekamMedisModel->generateNoRawat($tgl_registrasi);

            include 'modules/rekam_medis/views/form_tambah_pemeriksaan.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?module=rekam_medis');
            exit;
        }
    }

    public function formPenilaianMedisRalanKandungan()
    {
        $no_rawat = $_GET['no_rawat'] ?? '';

        if (empty($no_rawat)) {
            $_SESSION['error'] = "Nomor rawat tidak valid";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        // Ambil data pasien berdasarkan no_rawat
        $stmt = $this->pdo->prepare("
            SELECT p.*, rp.no_rawat, rp.tgl_registrasi, rp.jam_reg
            FROM reg_periksa rp
            JOIN pasien p ON rp.no_rkm_medis = p.no_rkm_medis
            WHERE rp.no_rawat = ?
        ");
        $stmt->execute([$no_rawat]);
        $data = $stmt->fetch();

        if (!$data) {
            $_SESSION['error'] = "Data pasien tidak ditemukan";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        include 'modules/rekam_medis/views/form_penilaian_medis_ralan_kandungan.php';
    }

    public function edit_kunjungan()
    {
        try {
            error_log('=== Debug Edit Kunjungan ===');
            error_log('Timestamp: ' . date('Y-m-d H:i:s'));

            // Ambil no_rawat dari parameter
            $no_rawat = isset($_GET['no_rawat']) ? $_GET['no_rawat'] : null;
            error_log('no_rawat: ' . ($no_rawat ?? 'null'));

            if (!$no_rawat) {
                throw new Exception('Parameter no_rawat tidak ditemukan');
            }

            // Ambil data kunjungan - hanya kolom yang ada di tabel reg_periksa
            $query = "SELECT rp.no_reg, rp.no_rawat, rp.tgl_registrasi, rp.jam_reg, 
                     rp.no_rkm_medis, rp.status_bayar,
                     p.nm_pasien, p.tgl_lahir, p.jk
                     FROM reg_periksa rp 
                     LEFT JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
                     WHERE rp.no_rawat = ?";
            error_log('Query: ' . $query);

            $stmt = $this->pdo->prepare($query);
            if (!$stmt) {
                error_log('PDO prepare error: ' . json_encode($this->pdo->errorInfo()));
                throw new Exception('Gagal mempersiapkan query');
            }

            $result = $stmt->execute([$no_rawat]);
            if (!$result) {
                error_log('PDO execute error: ' . json_encode($stmt->errorInfo()));
                throw new Exception('Gagal mengeksekusi query');
            }

            $kunjungan = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log('Data kunjungan: ' . json_encode($kunjungan));

            if (!$kunjungan) {
                throw new Exception('Data kunjungan tidak ditemukan');
            }

            // Cek keberadaan file view
            $view_file = BASE_PATH . '/modules/rekam_medis/views/form_edit_kunjungan.php';
            error_log('View file path: ' . $view_file);

            if (!file_exists($view_file)) {
                error_log('View file tidak ditemukan: ' . $view_file);
                throw new Exception('File view tidak ditemukan');
            }

            error_log('Loading view file...');
            require $view_file;
            error_log('View file loaded successfully');
        } catch (Exception $e) {
            error_log('Error in edit_kunjungan: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $_SESSION['error'] = $e->getMessage();

            // Jika kita memiliki no_rkm_medis, arahkan ke halaman detail pasien
            if (isset($kunjungan) && isset($kunjungan['no_rkm_medis'])) {
                header('Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=' . $kunjungan['no_rkm_medis']);
            } else {
                header('Location: index.php?module=rekam_medis');
            }
            exit;
        }
    }

    public function update_kunjungan()
    {
        try {
            error_log('=== Debug Update Kunjungan ===');
            error_log('Timestamp: ' . date('Y-m-d H:i:s'));
            error_log('POST data: ' . json_encode($_POST));

            // Validasi input
            $required = ['no_rawat', 'tgl_registrasi', 'jam_reg', 'no_rkm_medis'];
            foreach ($required as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Field $field harus diisi");
                }
            }

            // Ambil data lama untuk logging
            $stmt = $this->pdo->prepare("SELECT * FROM reg_periksa WHERE no_rawat = ?");
            $stmt->execute([$_POST['no_rawat']]);
            $old_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$old_data) {
                throw new Exception("Data kunjungan dengan no_rawat " . $_POST['no_rawat'] . " tidak ditemukan");
            }

            error_log('Data lama: ' . json_encode($old_data));

            // Update data kunjungan - hanya kolom yang ada di tabel reg_periksa
            $query = "UPDATE reg_periksa SET 
                     tgl_registrasi = ?,
                     jam_reg = ?,
                     status_bayar = ?
                     WHERE no_rawat = ?";
            error_log('Query: ' . $query);

            $stmt = $this->pdo->prepare($query);
            if (!$stmt) {
                error_log('PDO prepare error: ' . json_encode($this->pdo->errorInfo()));
                throw new Exception('Gagal mempersiapkan query');
            }

            $params = [
                $_POST['tgl_registrasi'],
                $_POST['jam_reg'],
                $_POST['status_bayar'] ?? 'Belum Bayar',
                $_POST['no_rawat']
            ];
            error_log('Execute params: ' . json_encode($params));

            $result = $stmt->execute($params);
            if (!$result) {
                error_log('PDO execute error: ' . json_encode($stmt->errorInfo()));
                throw new Exception('Gagal mengupdate data kunjungan');
            }

            // Ambil data baru untuk logging
            $stmt = $this->pdo->prepare("SELECT * FROM reg_periksa WHERE no_rawat = ?");
            $stmt->execute([$_POST['no_rawat']]);
            $new_data = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log('Data baru: ' . json_encode($new_data));

            error_log('Update successful');
            $_SESSION['success'] = 'Data kunjungan berhasil diupdate';
            header('Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=' . $_POST['no_rkm_medis']);
            exit;
        } catch (Exception $e) {
            error_log('Error in update_kunjungan: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $_SESSION['error'] = $e->getMessage();

            if (isset($_POST['no_rawat']) && isset($_POST['no_rkm_medis'])) {
                header('Location: index.php?module=rekam_medis&action=edit_kunjungan&no_rawat=' . $_POST['no_rawat']);
            } else {
                header('Location: index.php?module=rekam_medis');
            }
            exit;
        }
    }

    public function update_status()
    {
        // Fungsi ini tidak lagi diperlukan karena kolom 'stts' sudah dihapus
        $_SESSION['error'] = 'Fungsi ini tidak lagi tersedia karena perubahan struktur database';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function hapus_kunjungan()
    {
        try {
            error_log("=== MULAI PROSES HAPUS KUNJUNGAN ===");

            // Pastikan no_rawat ada
            if (!isset($_GET['no_rawat']) || empty($_GET['no_rawat'])) {
                throw new Exception('Parameter no_rawat tidak ditemukan');
            }

            $no_rawat = $_GET['no_rawat'];
            error_log("No Rawat yang akan dihapus: " . $no_rawat);

            // Cek apakah data ada dan ambil no_rkm_medis
            error_log("Memeriksa keberadaan data...");
            $check_stmt = $this->pdo->prepare("SELECT no_rkm_medis FROM reg_periksa WHERE no_rawat = ?");
            $check_stmt->execute([$no_rawat]);
            $data = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                error_log("Data tidak ditemukan untuk no_rawat: " . $no_rawat);
                throw new Exception("Data kunjungan tidak ditemukan");
            }

            $no_rkm_medis = $data['no_rkm_medis'];
            error_log("No RM ditemukan: " . $no_rkm_medis);

            // Mulai transaksi
            $this->pdo->beginTransaction();

            try {
                // Hapus dari tabel penilaian_medis_ralan_kandungan jika ada
                $stmt1 = $this->pdo->prepare("DELETE FROM penilaian_medis_ralan_kandungan WHERE no_rawat = ?");
                $stmt1->execute([$no_rawat]);
                error_log("Menghapus dari penilaian_medis_ralan_kandungan: " . $stmt1->rowCount() . " baris");

                // Hapus dari tabel tindakan_medis jika ada
                $stmt2 = $this->pdo->prepare("DELETE FROM tindakan_medis WHERE no_rawat = ?");
                $stmt2->execute([$no_rawat]);
                error_log("Menghapus dari tindakan_medis: " . $stmt2->rowCount() . " baris");

                // Hapus dari tabel reg_periksa
                $stmt3 = $this->pdo->prepare("DELETE FROM reg_periksa WHERE no_rawat = ?");
                $stmt3->execute([$no_rawat]);
                error_log("Menghapus dari reg_periksa: " . $stmt3->rowCount() . " baris");

                if ($stmt3->rowCount() === 0) {
                    throw new Exception("Gagal menghapus data kunjungan");
                }

                // Commit transaksi
                $this->pdo->commit();
                error_log("Transaksi berhasil di-commit");

                $_SESSION['success'] = "Kunjungan berhasil dihapus";
                header("Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=" . $no_rkm_medis);
            } catch (Exception $e) {
                // Rollback jika terjadi error
                $this->pdo->rollBack();
                error_log("Rollback transaksi: " . $e->getMessage());
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header("Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=" . $no_rkm_medis);
            }
        } catch (Exception $e) {
            error_log("ERROR: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = "Error: " . $e->getMessage();

            // Jika kita memiliki no_rkm_medis, arahkan ke halaman detail pasien
            if (isset($no_rkm_medis)) {
                header("Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=" . $no_rkm_medis);
            } else {
                // Jika tidak ada no_rkm_medis, baru arahkan ke daftar rekam medis
                header("Location: index.php?module=rekam_medis");
            }
        }
        exit;
    }

    public function update_status_bayar()
    {
        try {
            if (!isset($_GET['no_rawat'])) {
                throw new Exception('No rawat tidak ditemukan');
            }

            $no_rawat = $_GET['no_rawat'];

            if ($this->rekamMedisModel->updateStatusBayar($no_rawat)) {
                $_SESSION['success'] = 'Status pembayaran berhasil diubah menjadi Sudah Bayar';
            } else {
                $_SESSION['error'] = 'Gagal mengubah status pembayaran';
            }

            // Redirect kembali ke halaman detail pasien
            $no_rkm_medis = $this->rekamMedisModel->getNoRkmMedisByNoRawat($no_rawat);

            if ($no_rkm_medis) {
                header('Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=' . $no_rkm_medis);
            } else {
                header('Location: index.php?module=rekam_medis');
            }
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?module=rekam_medis');
            exit;
        }
    }

    public function getPdoStatus()
    {
        return $this->rekamMedisModel->getPdoStatus();
    }

    // Fungsi untuk menampilkan form tambah status obstetri
    public function tambah_status_obstetri()
    {
        // Pastikan parameter no_rkm_medis tersedia
        if (!isset($_GET['no_rkm_medis'])) {
            $_SESSION['error'] = "Parameter no_rkm_medis tidak ditemukan";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        $no_rkm_medis = $_GET['no_rkm_medis'];
        $pasien = $this->rekamMedisModel->getPasienById($no_rkm_medis);

        if (!$pasien) {
            $_SESSION['error'] = "Data pasien tidak ditemukan";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        // Tampilkan form tambah status obstetri
        include 'modules/rekam_medis/views/form_status_obstetri.php';
    }

    // Fungsi untuk menyimpan data status obstetri
    public function simpan_status_obstetri()
    {
        // Debugging
        error_log("=== DEBUG SIMPAN STATUS OBSTETRI ===");
        error_log("POST data: " . print_r($_POST, true));

        // Validasi data yang dikirimkan
        if (!isset($_POST['no_rkm_medis']) || empty($_POST['no_rkm_medis'])) {
            $_SESSION['error'] = "No. Rekam Medis tidak boleh kosong";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        // Siapkan data untuk disimpan
        $data = [
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'gravida' => isset($_POST['gravida']) ? $_POST['gravida'] : null,
            'paritas' => isset($_POST['paritas']) ? $_POST['paritas'] : null,
            'abortus' => isset($_POST['abortus']) ? $_POST['abortus'] : null,
            'tanggal_hpht' => isset($_POST['tanggal_hpht']) ? $_POST['tanggal_hpht'] : null,
            'tanggal_tp' => isset($_POST['tanggal_tp']) ? $_POST['tanggal_tp'] : null,
            'tanggal_tp_penyesuaian' => isset($_POST['tanggal_tp_penyesuaian']) ? $_POST['tanggal_tp_penyesuaian'] : null,
            'faktor_risiko_umum' => isset($_POST['faktor_risiko_umum']) ? $_POST['faktor_risiko_umum'] : [],
            'faktor_risiko_obstetri' => isset($_POST['faktor_risiko_obstetri']) ? $_POST['faktor_risiko_obstetri'] : [],
            'faktor_risiko_preeklampsia' => isset($_POST['faktor_risiko_preeklampsia']) ? $_POST['faktor_risiko_preeklampsia'] : [],
            'hasil_faktor_risiko' => isset($_POST['hasil_faktor_risiko']) ? $_POST['hasil_faktor_risiko'] : null
        ];

        // Simpan data status obstetri
        $result = $this->rekamMedisModel->tambahStatusObstetri($data);

        if ($result) {
            $_SESSION['success'] = "Data status obstetri berhasil disimpan";
        } else {
            $_SESSION['error'] = "Gagal menyimpan data status obstetri";
        }

        // Redirect ke halaman detail pasien
        header("Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=" . $_POST['no_rkm_medis']);
        exit;
    }

    // Fungsi untuk menampilkan form edit status obstetri
    public function edit_status_obstetri()
    {
        // Pastikan parameter id tersedia
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "Parameter ID tidak ditemukan";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        $id_status_obstetri = $_GET['id'];
        $statusObstetri = $this->rekamMedisModel->getStatusObstetriById($id_status_obstetri);

        if (!$statusObstetri) {
            $_SESSION['error'] = "Data status obstetri tidak ditemukan";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        $pasien = $this->rekamMedisModel->getPasienById($statusObstetri['no_rkm_medis']);

        // Tampilkan form edit status obstetri
        include 'modules/rekam_medis/views/form_status_obstetri.php';
    }

    // Fungsi untuk mengupdate data status obstetri
    public function update_status_obstetri()
    {
        // Debugging
        error_log("=== DEBUG UPDATE STATUS OBSTETRI ===");
        error_log("POST data: " . print_r($_POST, true));

        // Validasi data yang dikirimkan
        if (!isset($_POST['id_status_obstetri']) || empty($_POST['id_status_obstetri'])) {
            $_SESSION['error'] = "ID Status Obstetri tidak boleh kosong";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        // Siapkan data untuk diupdate
        $data = [
            'id_status_obstetri' => $_POST['id_status_obstetri'],
            'gravida' => isset($_POST['gravida']) ? $_POST['gravida'] : null,
            'paritas' => isset($_POST['paritas']) ? $_POST['paritas'] : null,
            'abortus' => isset($_POST['abortus']) ? $_POST['abortus'] : null,
            'tanggal_hpht' => isset($_POST['tanggal_hpht']) ? $_POST['tanggal_hpht'] : null,
            'tanggal_tp' => isset($_POST['tanggal_tp']) ? $_POST['tanggal_tp'] : null,
            'tanggal_tp_penyesuaian' => isset($_POST['tanggal_tp_penyesuaian']) ? $_POST['tanggal_tp_penyesuaian'] : null,
            'faktor_risiko_umum' => isset($_POST['faktor_risiko_umum']) ? $_POST['faktor_risiko_umum'] : [],
            'faktor_risiko_obstetri' => isset($_POST['faktor_risiko_obstetri']) ? $_POST['faktor_risiko_obstetri'] : [],
            'faktor_risiko_preeklampsia' => isset($_POST['faktor_risiko_preeklampsia']) ? $_POST['faktor_risiko_preeklampsia'] : [],
            'hasil_faktor_risiko' => isset($_POST['hasil_faktor_risiko']) ? $_POST['hasil_faktor_risiko'] : null
        ];

        // Update data status obstetri
        $result = $this->rekamMedisModel->updateStatusObstetri($data);

        if ($result) {
            $_SESSION['success'] = "Data status obstetri berhasil diupdate";
        } else {
            $_SESSION['error'] = "Gagal mengupdate data status obstetri";
        }

        // Redirect ke halaman detail pasien
        header("Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=" . $_POST['no_rkm_medis']);
        exit;
    }

    // Fungsi untuk menghapus data status obstetri
    public function hapus_status_obstetri()
    {
        // Pastikan parameter id tersedia
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "Parameter ID tidak ditemukan";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        $id_status_obstetri = $_GET['id'];
        $statusObstetri = $this->rekamMedisModel->getStatusObstetriById($id_status_obstetri);

        if (!$statusObstetri) {
            $_SESSION['error'] = "Data status obstetri tidak ditemukan";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        // Simpan no_rkm_medis untuk redirect
        $no_rkm_medis = $statusObstetri['no_rkm_medis'];

        // Hapus data status obstetri
        $result = $this->rekamMedisModel->hapusStatusObstetri($id_status_obstetri);

        if ($result) {
            $_SESSION['success'] = "Data status obstetri berhasil dihapus";
        } else {
            $_SESSION['error'] = "Gagal menghapus data status obstetri";
        }

        // Redirect ke halaman detail pasien
        header("Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=" . $no_rkm_medis);
        exit;
    }

    // Fungsi untuk menampilkan form tambah riwayat kehamilan
    public function tambah_riwayat_kehamilan()
    {
        // Pastikan parameter no_rkm_medis ada
        if (!isset($_GET['no_rkm_medis'])) {
            $_SESSION['error'] = "Nomor rekam medis tidak ditemukan";
            header("Location: index.php?module=rekam_medis&action=data_pasien");
            exit;
        }

        // Tampilkan form tambah riwayat kehamilan
        include 'modules/rekam_medis/views/form_tambah_riwayat_kehamilan.php';
    }

    // Fungsi untuk menyimpan data riwayat kehamilan
    public function simpan_riwayat_kehamilan()
    {
        // Debugging
        error_log("=== DEBUG SIMPAN RIWAYAT KEHAMILAN ===");
        error_log("POST data: " . print_r($_POST, true));

        // Validasi data yang dikirimkan
        if (!isset($_POST['no_rkm_medis']) || empty($_POST['no_rkm_medis'])) {
            $_SESSION['error_message'] = "Nomor rekam medis tidak boleh kosong";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        if (!isset($_POST['no_urut_kehamilan']) || empty($_POST['no_urut_kehamilan'])) {
            $_SESSION['error_message'] = "Urutan kehamilan tidak boleh kosong";
            header("Location: index.php?module=rekam_medis&action=tambah_riwayat_kehamilan&no_rkm_medis=" . $_POST['no_rkm_medis']);
            exit;
        }

        if (!isset($_POST['status_kehamilan']) || empty($_POST['status_kehamilan'])) {
            $_SESSION['error_message'] = "Status kehamilan tidak boleh kosong";
            header("Location: index.php?module=rekam_medis&action=tambah_riwayat_kehamilan&no_rkm_medis=" . $_POST['no_rkm_medis']);
            exit;
        }

        // Siapkan data untuk disimpan
        $data = [
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'no_urut_kehamilan' => $_POST['no_urut_kehamilan'],
            'status_kehamilan' => $_POST['status_kehamilan'],
            'jenis_persalinan' => isset($_POST['jenis_persalinan']) ? $_POST['jenis_persalinan'] : null,
            'tempat_persalinan' => isset($_POST['tempat_persalinan']) ? $_POST['tempat_persalinan'] : null,
            'penolong_persalinan' => isset($_POST['penolong_persalinan']) ? $_POST['penolong_persalinan'] : null,
            'tanggal_persalinan' => isset($_POST['tanggal_persalinan']) && !empty($_POST['tanggal_persalinan']) ? $_POST['tanggal_persalinan'] : null,
            'jenis_kelamin_anak' => isset($_POST['jenis_kelamin_anak']) ? $_POST['jenis_kelamin_anak'] : null,
            'berat_badan_lahir' => isset($_POST['berat_badan_lahir']) && !empty($_POST['berat_badan_lahir']) ? $_POST['berat_badan_lahir'] : null,
            'kondisi_lahir' => isset($_POST['kondisi_lahir']) ? $_POST['kondisi_lahir'] : null,
            'komplikasi_kehamilan' => isset($_POST['komplikasi_kehamilan']) ? $_POST['komplikasi_kehamilan'] : null,
            'komplikasi_persalinan' => isset($_POST['komplikasi_persalinan']) ? $_POST['komplikasi_persalinan'] : null,
            'catatan' => isset($_POST['catatan']) ? $_POST['catatan'] : null
        ];

        // Simpan data riwayat kehamilan
        $result = $this->rekamMedisModel->tambahRiwayatKehamilan($data);

        if ($result) {
            $_SESSION['success_message'] = "Data riwayat kehamilan berhasil disimpan";
        } else {
            $_SESSION['error_message'] = "Gagal menyimpan data riwayat kehamilan";
        }

        // Redirect ke halaman detail pasien
        header("Location: index.php?module=rekam_medis&action=detail_pasien&no_rkm_medis=" . $_POST['no_rkm_medis']);
        exit;
    }

    // Fungsi untuk menampilkan form edit riwayat kehamilan
    public function edit_riwayat_kehamilan()
    {
        // Pastikan parameter id ada
        if (!isset($_GET['id'])) {
            $_SESSION['error_message'] = "ID riwayat kehamilan tidak ditemukan";
            header("Location: index.php?module=rekam_medis&action=data_pasien");
            exit;
        }

        // Tampilkan form edit riwayat kehamilan
        include 'modules/rekam_medis/views/form_edit_riwayat_kehamilan.php';
    }

    // Fungsi untuk mengupdate data riwayat kehamilan
    public function update_riwayat_kehamilan()
    {
        // Debugging
        error_log("=== DEBUG UPDATE RIWAYAT KEHAMILAN ===");
        error_log("POST data: " . print_r($_POST, true));

        // Validasi data yang dikirimkan
        if (!isset($_POST['id_riwayat_kehamilan']) || empty($_POST['id_riwayat_kehamilan'])) {
            $_SESSION['error_message'] = "ID Riwayat Kehamilan tidak boleh kosong";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        // Siapkan data untuk diupdate
        $data = [
            'id_riwayat_kehamilan' => $_POST['id_riwayat_kehamilan'],
            'no_urut_kehamilan' => $_POST['no_urut_kehamilan'],
            'status_kehamilan' => $_POST['status_kehamilan'],
            'jenis_persalinan' => isset($_POST['jenis_persalinan']) ? $_POST['jenis_persalinan'] : null,
            'tempat_persalinan' => isset($_POST['tempat_persalinan']) ? $_POST['tempat_persalinan'] : null,
            'penolong_persalinan' => isset($_POST['penolong_persalinan']) ? $_POST['penolong_persalinan'] : null,
            'tanggal_persalinan' => isset($_POST['tanggal_persalinan']) && !empty($_POST['tanggal_persalinan']) ? $_POST['tanggal_persalinan'] : null,
            'jenis_kelamin_anak' => isset($_POST['jenis_kelamin_anak']) ? $_POST['jenis_kelamin_anak'] : null,
            'berat_badan_lahir' => isset($_POST['berat_badan_lahir']) && !empty($_POST['berat_badan_lahir']) ? $_POST['berat_badan_lahir'] : null,
            'kondisi_lahir' => isset($_POST['kondisi_lahir']) ? $_POST['kondisi_lahir'] : null,
            'komplikasi_kehamilan' => isset($_POST['komplikasi_kehamilan']) ? $_POST['komplikasi_kehamilan'] : null,
            'komplikasi_persalinan' => isset($_POST['komplikasi_persalinan']) ? $_POST['komplikasi_persalinan'] : null,
            'catatan' => isset($_POST['catatan']) ? $_POST['catatan'] : null
        ];

        // Update data riwayat kehamilan
        $result = $this->rekamMedisModel->updateRiwayatKehamilan($data);

        if ($result) {
            $_SESSION['success_message'] = "Data riwayat kehamilan berhasil diupdate";
        } else {
            $_SESSION['error_message'] = "Gagal mengupdate data riwayat kehamilan";
        }

        // Redirect ke halaman detail pasien
        header("Location: index.php?module=rekam_medis&action=detail_pasien&no_rkm_medis=" . $_POST['no_rkm_medis']);
        exit;
    }

    // Fungsi untuk menghapus data riwayat kehamilan
    public function hapus_riwayat_kehamilan()
    {
        // Pastikan parameter id ada
        if (!isset($_GET['id'])) {
            $_SESSION['error_message'] = "ID riwayat kehamilan tidak ditemukan";
            header("Location: index.php?module=rekam_medis&action=data_pasien");
            exit;
        }

        // Ambil data riwayat kehamilan untuk mendapatkan no_rkm_medis
        $riwayatKehamilan = $this->rekamMedisModel->getRiwayatKehamilanById($_GET['id']);
        if (!$riwayatKehamilan) {
            $_SESSION['error_message'] = "Data riwayat kehamilan tidak ditemukan";
            header("Location: index.php?module=rekam_medis&action=data_pasien");
            exit;
        }

        // Hapus data riwayat kehamilan
        $result = $this->rekamMedisModel->hapusRiwayatKehamilan($_GET['id']);

        if ($result) {
            $_SESSION['success_message'] = "Data riwayat kehamilan berhasil dihapus";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus data riwayat kehamilan";
        }

        // Redirect ke halaman detail pasien
        header("Location: index.php?module=rekam_medis&action=detail_pasien&no_rkm_medis=" . $riwayatKehamilan['no_rkm_medis']);
        exit;
    }

    public function tambah_status_ginekologi()
    {
        if (!isset($_GET['no_rkm_medis'])) {
            $_SESSION['error'] = "Data pasien tidak ditemukan.";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        $pasienModel = new RekamMedis($this->pdo);
        $pasien = $pasienModel->getPasienById($_GET['no_rkm_medis']);

        if (!$pasien) {
            $_SESSION['error'] = "Data pasien tidak ditemukan.";
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        require_once 'modules/rekam_medis/views/form_status_ginekologi.php';
    }

    public function simpan_status_ginekologi()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?module=rekam_medis");
            exit;
        }

        $statusGinekologiModel = new StatusGinekologi($this->pdo);

        try {
            $result = $statusGinekologiModel->tambahStatusGinekologi($_POST);

            if ($result) {
                $_SESSION['success'] = "Data status ginekologi berhasil disimpan.";
            } else {
                $_SESSION['error'] = "Gagal menyimpan data status ginekologi.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        }

        header("Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=" . $_POST['no_rkm_medis']);
        exit;
    }

    public function edit_status_ginekologi()
    {
        try {
            if (!isset($_GET['id'])) {
                throw new Exception("ID status ginekologi tidak ditemukan.");
            }

            $statusGinekologiModel = new StatusGinekologi($this->pdo);
            $statusGinekologi = $statusGinekologiModel->getStatusGinekologiById($_GET['id']);

            if (!$statusGinekologi) {
                throw new Exception("Data status ginekologi tidak ditemukan.");
            }

            $pasienModel = new RekamMedis($this->pdo);
            $pasien = $pasienModel->getPasienById($statusGinekologi['no_rkm_medis']);

            if (!$pasien) {
                throw new Exception("Data pasien tidak ditemukan.");
            }

            require_once 'modules/rekam_medis/views/form_status_ginekologi.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();

            // Jika kita memiliki no_rkm_medis dari status ginekologi, gunakan itu untuk redirect
            if (isset($statusGinekologi) && isset($statusGinekologi['no_rkm_medis'])) {
                header("Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=" . $statusGinekologi['no_rkm_medis']);
            } else {
                header("Location: index.php?module=rekam_medis&action=data_pasien");
            }
            exit;
        }
    }

    public function update_status_ginekologi()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?module=rekam_medis&action=data_pasien");
            exit;
        }

        if (!isset($_POST['id_status_ginekologi'])) {
            $_SESSION['error'] = "ID status ginekologi tidak ditemukan.";
            header("Location: index.php?module=rekam_medis&action=data_pasien");
            exit;
        }

        $statusGinekologiModel = new StatusGinekologi($this->pdo);

        try {
            $result = $statusGinekologiModel->updateStatusGinekologi($_POST['id_status_ginekologi'], $_POST);

            if ($result) {
                $_SESSION['success'] = "Data status ginekologi berhasil diperbarui.";
            } else {
                $_SESSION['error'] = "Gagal memperbarui data status ginekologi.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        }

        header("Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=" . $_POST['no_rkm_medis']);
        exit;
    }

    public function hapus_status_ginekologi()
    {
        if (!isset($_GET['id'])) {
            $_SESSION['error'] = "ID status ginekologi tidak ditemukan.";
            header("Location: index.php?module=rekam_medis&action=data_pasien");
            exit;
        }

        $statusGinekologiModel = new StatusGinekologi($this->pdo);

        try {
            // Ambil no_rkm_medis sebelum menghapus untuk redirect
            $statusGinekologi = $statusGinekologiModel->getStatusGinekologiById($_GET['id']);
            $no_rkm_medis = $statusGinekologi['no_rkm_medis'];

            $result = $statusGinekologiModel->hapusStatusGinekologi($_GET['id']);

            if ($result) {
                $_SESSION['success'] = "Data status ginekologi berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus data status ginekologi.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        }

        header("Location: index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=" . $no_rkm_medis);
        exit;
    }
}
