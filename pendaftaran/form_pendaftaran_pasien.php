<?php
session_start();
// Impor konfigurasi zona waktu
require_once '../config/timezone.php';
require_once '../config/database.php';
$page_title = "Form Pendaftaran Pasien";

// Ambil parameter dari URL jika ada
$id_tempat_praktek = isset($_GET['tempat']) ? $_GET['tempat'] : '';
$id_dokter = isset($_GET['dokter']) ? $_GET['dokter'] : '';
$id_jadwal = isset($_GET['jadwal']) ? $_GET['jadwal'] : '';

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

// Ambil data kecamatan
try {
    // Menggunakan array statis untuk wilayah yang diminta
    $kecamatan = [
        ['kd_kec' => '1', 'nm_kec' => 'Batu'],
        ['kd_kec' => '2', 'nm_kec' => 'Bumiaji'],
        ['kd_kec' => '3', 'nm_kec' => 'Junrejo'],
        ['kd_kec' => '4', 'nm_kec' => 'Pujon'],
        ['kd_kec' => '5', 'nm_kec' => 'Ngantang'],
        ['kd_kec' => '6', 'nm_kec' => 'Lainnya']
    ];
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $kecamatan = [];
}

// Proses form jika disubmit
$errors = [];
$success = false;
$id_pendaftaran = '';

// Periksa koneksi database
try {
    $conn->query("SELECT 1");
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    $errors[] = "Tidak dapat terhubung ke database. Silakan coba lagi nanti atau hubungi administrator.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $no_ktp = trim($_POST['no_ktp'] ?? '');
    $nama_pasien = trim($_POST['nama_pasien'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $kd_kec = trim($_POST['kd_kec'] ?? '');
    $pekerjaan = trim($_POST['pekerjaan'] ?? '');
    $keluhan = trim($_POST['keluhan'] ?? '');
    $id_tempat_praktek = trim($_POST['id_tempat_praktek'] ?? '');
    $id_dokter = trim($_POST['id_dokter'] ?? '');
    $id_jadwal = trim($_POST['id_jadwal'] ?? '');

    // Validasi data
    if (empty($no_ktp)) {
        $errors[] = "NIK harus diisi";
    } elseif (strlen($no_ktp) != 16) {
        $errors[] = "NIK harus 16 digit";
    }
    if (empty($nama_pasien)) {
        $errors[] = "Nama pasien harus diisi";
    }
    if (empty($tanggal_lahir)) {
        $errors[] = "Tanggal lahir harus diisi";
    }
    if (empty($jenis_kelamin)) {
        $errors[] = "Jenis kelamin harus dipilih";
    }
    if (empty($nomor_telepon)) {
        $errors[] = "Nomor telepon harus diisi";
    }
    if (empty($kd_kec)) {
        $errors[] = "Wilayah harus dipilih";
    }
    if (empty($id_tempat_praktek)) {
        $errors[] = "Tempat praktek harus dipilih";
    }
    if (empty($id_dokter)) {
        $errors[] = "Dokter harus dipilih";
    }
    if (empty($id_jadwal)) {
        $errors[] = "Jadwal harus dipilih";
    }

    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        try {
            // Begin transaction
            $conn->beginTransaction();

            // Log untuk debugging
            error_log("Memulai proses pendaftaran untuk NIK: " . $no_ktp);

            // Cek apakah pasien sudah ada di tabel pasien
            $stmt = $conn->prepare("SELECT no_ktp FROM pasien WHERE no_ktp = ?");
            $stmt->execute([$no_ktp]);
            $pasien_exists = $stmt->fetch();

            error_log("Pasien exists: " . ($pasien_exists ? "Ya" : "Tidak"));

            // Jika pasien belum ada, simpan ke tabel pasien
            if (!$pasien_exists) {
                // Generate nomor RM dengan format RM-YYYYMMDD-nnn
                $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(no_rkm_medis, '-', -1) AS UNSIGNED)) as last_num FROM pasien WHERE no_rkm_medis LIKE ?");
                $prefix = 'RM-' . date('Ymd') . '-%';
                $stmt->execute([$prefix]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $next_num = 1;
                if ($result && $result['last_num']) {
                    $next_num = $result['last_num'] + 1;
                }

                $no_rkm_medis = 'RM-' . date('Ymd') . '-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);

                $nm_ibu = '-';
                $umur = date_diff(date_create($tanggal_lahir), date_create('today'))->y;
                $tgl_daftar = date('Y-m-d H:i:s');
                $namakeluarga = '-';
                $kd_pj = 'UMU'; // Umum
                $kd_kel = 0;
                $kd_kab = 0;

                error_log("Menyimpan data pasien baru dengan no_rkm_medis: " . $no_rkm_medis);

                $query = "INSERT INTO pasien (
                    no_rkm_medis, nm_pasien, no_ktp, jk, tgl_lahir, nm_ibu, 
                    alamat, pekerjaan, no_tlp, umur, kd_kec, namakeluarga, kd_pj, kd_kel, kd_kab, tgl_daftar
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($query);
                $stmt->execute([
                    $no_rkm_medis,
                    $nama_pasien,
                    $no_ktp,
                    $jenis_kelamin,
                    $tanggal_lahir,
                    $nm_ibu,
                    $alamat,
                    $pekerjaan,
                    $nomor_telepon,
                    $umur,
                    $kd_kec,
                    $namakeluarga,
                    $kd_pj,
                    $kd_kel,
                    $kd_kab,
                    $tgl_daftar
                ]);

                error_log("Data pasien baru berhasil disimpan");
            } else {
                // Update data pasien yang sudah ada
                $umur = date_diff(date_create($tanggal_lahir), date_create('today'))->y;

                error_log("Memperbarui data pasien dengan NIK: " . $no_ktp);

                $query = "UPDATE pasien SET 
                    nm_pasien = ?, 
                    jk = ?, 
                    tgl_lahir = ?, 
                    alamat = ?, 
                    pekerjaan = ?, 
                    no_tlp = ?, 
                    umur = ?, 
                    kd_kec = ? 
                    WHERE no_ktp = ?";

                $stmt = $conn->prepare($query);
                $stmt->execute([
                    $nama_pasien,
                    $jenis_kelamin,
                    $tanggal_lahir,
                    $alamat,
                    $pekerjaan,
                    $nomor_telepon,
                    $umur,
                    $kd_kec,
                    $no_ktp
                ]);

                error_log("Data pasien berhasil diperbarui");
            }

            // Buat ID pendaftaran dengan pendekatan yang lebih sederhana dan robust
            $tanggal_format = date('Ymd');

            // Gunakan pendekatan yang lebih sederhana untuk mendapatkan nomor urut terakhir
            $query = "SELECT MAX(SUBSTRING_INDEX(ID_Pendaftaran, '-', -1)) as last_number 
                      FROM pendaftaran 
                      WHERE ID_Pendaftaran LIKE ?";
            $stmt = $conn->prepare($query);
            $prefix = "REG-" . $tanggal_format . "%";
            $stmt->execute([$prefix]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Pastikan last_number adalah integer valid
            $last_number = 0; // Default ke 0
            if ($result && isset($result['last_number'])) {
                // Hapus karakter non-numerik dan konversi ke integer
                $clean_number = preg_replace('/[^0-9]/', '', $result['last_number']);
                if (is_numeric($clean_number) && $clean_number !== '') {
                    $last_number = intval($clean_number);
                }
            }

            // Log untuk debugging
            error_log("Last number found: " . ($result ? $result['last_number'] : 'none') . ", cleaned to: " . $last_number);

            $new_number = $last_number + 1;
            $id_pendaftaran = "REG-" . $tanggal_format . "-" . str_pad($new_number, 4, "0", STR_PAD_LEFT);

            // Verifikasi ID unik dengan pendekatan yang lebih sederhana
            $is_unique = false;
            $max_attempts = 100; // Tingkatkan jumlah percobaan
            $attempt = 0;

            while (!$is_unique && $attempt < $max_attempts) {
                // Cek apakah ID sudah ada
                $check_query = "SELECT COUNT(*) as count FROM pendaftaran WHERE ID_Pendaftaran = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->execute([$id_pendaftaran]);
                $check_result = $check_stmt->fetch(PDO::FETCH_ASSOC);

                if ($check_result['count'] == 0) {
                    $is_unique = true;
                } else {
                    // Jika ID sudah ada, tambahkan nomor urut
                    $new_number++;
                    $id_pendaftaran = "REG-" . $tanggal_format . "-" . str_pad($new_number, 4, "0", STR_PAD_LEFT);
                    $attempt++;

                    // Log untuk debugging
                    error_log("ID sudah ada, mencoba dengan nomor baru: " . $new_number);
                }
            }

            if (!$is_unique) {
                throw new PDOException("Tidak dapat membuat ID pendaftaran unik setelah " . $max_attempts . " percobaan");
            }

            error_log("ID Pendaftaran dibuat: " . $id_pendaftaran);

            // Simpan data pendaftaran - sesuaikan dengan struktur tabel yang ada
            $query = "INSERT INTO pendaftaran (
                        ID_Pendaftaran, 
                        no_ktp,
                        nm_pasien,
                        tgl_lahir,
                        jk,
                        no_tlp,
                        alamat,
                        Keluhan,
                        ID_Tempat_Praktek,
                        ID_Dokter,
                        ID_Jadwal,
                        Status_Pendaftaran,
                        Waktu_Pendaftaran
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Konfirmasi', NOW())";

            error_log("Query pendaftaran: " . $query);
            error_log("Parameter: " . json_encode([
                $id_pendaftaran,
                $no_ktp,
                $nama_pasien,
                $tanggal_lahir,
                $jenis_kelamin,
                $nomor_telepon,
                $alamat,
                $keluhan,
                $id_tempat_praktek,
                $id_dokter,
                $id_jadwal
            ]));

            $stmt = $conn->prepare($query);
            $stmt->execute([
                $id_pendaftaran,
                $no_ktp,
                $nama_pasien,
                $tanggal_lahir,
                $jenis_kelamin,
                $nomor_telepon,
                $alamat,
                $keluhan,
                $id_tempat_praktek,
                $id_dokter,
                $id_jadwal
            ]);

            error_log("Data pendaftaran berhasil disimpan");

            // Commit transaction
            $conn->commit();
            error_log("Transaction committed");

            // Set pesan sukses
            $success = true;
            if ($pasien_exists) {
                $_SESSION['success_message'] = "Pendaftaran berhasil dilakukan dengan ID: " . $id_pendaftaran . ". Data pasien telah diperbarui.";
            } else {
                $_SESSION['success_message'] = "Pendaftaran berhasil dilakukan dengan ID: " . $id_pendaftaran;
            }

            // Redirect ke halaman sukses
            header("Location: pendaftaran_sukses.php?id=" . $id_pendaftaran);
            exit;
        } catch (PDOException $e) {
            // Rollback transaction
            $conn->rollBack();
            error_log("Database Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $errors[] = "Terjadi kesalahan saat menyimpan data: " . $e->getMessage();
        }
    }
}

// Start output buffering
ob_start();
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Form Pendaftaran Pasien</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Terjadi Kesalahan</h5>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <hr>
                            <p class="mb-0">Silakan periksa kembali data yang Anda masukkan dan coba lagi. Jika masalah berlanjut, hubungi administrator.</p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="formPendaftaran" class="needs-validation" novalidate>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Data Pasien</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="no_ktp" class="form-label">NIK <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="no_ktp" name="no_ktp" maxlength="16" required>
                                    <div class="invalid-feedback">NIK harus diisi (16 digit)</div>
                                </div>
                                <div class="mb-3">
                                    <label for="nama_pasien" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_pasien" name="nama_pasien" required>
                                    <div class="invalid-feedback">Nama lengkap harus diisi</div>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                                    <div class="invalid-feedback">Tanggal lahir harus diisi</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="gender_male" value="L" required>
                                            <label class="form-check-label" for="gender_male">Laki-laki</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="gender_female" value="P" required>
                                            <label class="form-check-label" for="gender_female">Perempuan</label>
                                        </div>
                                        <div class="invalid-feedback">Jenis kelamin harus dipilih</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nomor_telepon" class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="nomor_telepon" name="nomor_telepon" required>
                                    <div class="invalid-feedback">Nomor telepon harus diisi</div>
                                </div>
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
                                    <div class="invalid-feedback">Alamat harus diisi</div>
                                </div>
                                <div class="mb-3">
                                    <label for="kd_kec" class="form-label">Wilayah <span class="text-danger">*</span></label>
                                    <select class="form-select" id="kd_kec" name="kd_kec" required>
                                        <option value="">Pilih Wilayah</option>
                                        <?php foreach ($kecamatan as $kec): ?>
                                            <option value="<?php echo htmlspecialchars($kec['kd_kec']); ?>">
                                                <?php echo htmlspecialchars($kec['nm_kec']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Wilayah harus dipilih</div>
                                </div>
                                <div class="mb-3">
                                    <label for="pekerjaan" class="form-label">Pekerjaan <span class="text-danger">*</span></label>
                                    <select class="form-select" id="pekerjaan" name="pekerjaan" required>
                                        <option value="">Pilih Pekerjaan</option>
                                        <option value="Tidak Bekerja">Tidak Bekerja</option>
                                        <option value="Ibu Rumah Tangga">Ibu Rumah Tangga</option>
                                        <option value="Guru/Dosen">Guru/Dosen</option>
                                        <option value="PNS">PNS</option>
                                        <option value="TNI/Polri">TNI/Polri</option>
                                        <option value="Pegawai Swasta">Pegawai Swasta</option>
                                        <option value="Wiraswasta/Pengusaha">Wiraswasta/Pengusaha</option>
                                        <option value="Tenaga Kesehatan">Tenaga Kesehatan</option>
                                        <option value="Petani/Nelayan">Petani/Nelayan</option>
                                        <option value="Buruh">Buruh</option>
                                        <option value="Pelajar/Mahasiswa">Pelajar/Mahasiswa</option>
                                        <option value="Pensiunan">Pensiunan</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <div class="invalid-feedback">Pekerjaan harus dipilih</div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Informasi Kunjungan</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_tempat_praktek" class="form-label">Tempat Praktek <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_tempat_praktek" name="id_tempat_praktek" required>
                                        <option value="">Pilih Tempat Praktek</option>
                                        <?php foreach ($tempat_praktek as $tp): ?>
                                            <option value="<?php echo htmlspecialchars($tp['ID_Tempat_Praktek']); ?>" <?php echo $id_tempat_praktek == $tp['ID_Tempat_Praktek'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tp['Nama_Tempat']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Tempat praktek harus dipilih</div>
                                </div>
                                <div class="mb-3">
                                    <label for="id_dokter" class="form-label">Dokter <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_dokter" name="id_dokter" required>
                                        <option value="">Pilih Dokter</option>
                                        <?php foreach ($dokter as $d): ?>
                                            <option value="<?php echo htmlspecialchars($d['ID_Dokter']); ?>" <?php echo $id_dokter == $d['ID_Dokter'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($d['Nama_Dokter']); ?> (<?php echo htmlspecialchars($d['Spesialisasi']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Dokter harus dipilih</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_jadwal" class="form-label">Jadwal <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_jadwal" name="id_jadwal" required>
                                        <option value="">Pilih Tempat dan Dokter terlebih dahulu</option>
                                    </select>
                                    <div class="invalid-feedback">Jadwal harus dipilih</div>
                                </div>
                                <div class="mb-3">
                                    <label for="keluhan" class="form-label">Keluhan</label>
                                    <textarea class="form-control" id="keluhan" name="keluhan" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                                    <button type="submit" class="btn btn-primary">Daftar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nikInput = document.getElementById('no_ktp');
        const formFields = {
            nama_pasien: document.getElementById('nama_pasien'),
            tanggal_lahir: document.getElementById('tanggal_lahir'),
            gender_male: document.getElementById('gender_male'),
            gender_female: document.getElementById('gender_female'),
            nomor_telepon: document.getElementById('nomor_telepon'),
            alamat: document.getElementById('alamat'),
            kd_kec: document.getElementById('kd_kec'),
            pekerjaan: document.getElementById('pekerjaan')
        };

        // Semua field form selain NIK
        const allFormFields = document.querySelectorAll('#formPendaftaran input:not(#no_ktp), #formPendaftaran select, #formPendaftaran textarea');

        // Nonaktifkan semua field form kecuali NIK saat halaman dimuat
        allFormFields.forEach(field => {
            field.disabled = true;
        });

        // Tambahkan pesan informasi di atas form
        const formContainer = document.querySelector('.card-body');
        const infoAlert = document.createElement('div');
        infoAlert.className = 'alert alert-info mb-3';
        infoAlert.innerHTML = '<strong>Petunjuk:</strong> Masukkan NIK (16 digit) terlebih dahulu untuk melanjutkan pendaftaran.';
        formContainer.insertBefore(infoAlert, formContainer.firstChild);

        // Fungsi untuk mencari data pasien berdasarkan NIK
        function searchPatient(nik) {
            // Tampilkan loading indicator
            infoAlert.className = 'alert alert-warning mb-3';
            infoAlert.innerHTML = '<strong>Sedang memproses:</strong> Mencari data pasien...';

            // Gunakan URL lengkap dengan HTTPS
            const baseUrl = window.location.protocol + '//' + window.location.host;
            let apiUrl;

            // Penanganan khusus untuk domain produksi
            if (window.location.host === 'praktekobgin.com' || window.location.host === 'www.praktekobgin.com') {
                apiUrl = `${baseUrl}/pendaftaran/check_patient.php?nik=${nik}`;
            } else {
                apiUrl = `${baseUrl}/antrian%20pasien/pendaftaran/check_patient.php?nik=${nik}`;
            }

            console.log('Mengakses URL:', apiUrl);

            fetch(apiUrl)
                .then(response => {
                    // Periksa apakah respons OK (status 200-299)
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    // Periksa content-type untuk memastikan respons adalah JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error(`Respons bukan JSON: ${contentType}`);
                    }

                    return response.json();
                })
                .then(data => {
                    if (data.found) {
                        // Isi form dengan data pasien
                        formFields.nama_pasien.value = data.patient.nm_pasien;
                        formFields.tanggal_lahir.value = data.patient.tgl_lahir;
                        if (data.patient.jk === 'L') {
                            formFields.gender_male.checked = true;
                        } else if (data.patient.jk === 'P') {
                            formFields.gender_female.checked = true;
                        }
                        formFields.nomor_telepon.value = data.patient.no_tlp;
                        formFields.alamat.value = data.patient.alamat;
                        formFields.kd_kec.value = data.patient.kd_kec;
                        formFields.pekerjaan.value = data.patient.pekerjaan;

                        // Aktifkan semua field agar bisa diedit
                        allFormFields.forEach(field => {
                            field.disabled = false;
                        });

                        // Update pesan informasi
                        infoAlert.className = 'alert alert-success mb-3';
                        infoAlert.innerHTML = '<strong>Data ditemukan:</strong> Data pasien telah ditemukan. Anda dapat memperbarui data jika diperlukan dan melanjutkan dengan memilih tempat praktek, dokter, dan jadwal.';
                    } else {
                        // Aktifkan semua field untuk pasien baru
                        allFormFields.forEach(field => {
                            field.disabled = false;
                        });

                        // Reset form fields
                        Object.values(formFields).forEach(field => {
                            if (field.type === 'radio') {
                                field.checked = false;
                            } else if (field !== nikInput) {
                                field.value = '';
                            }
                        });

                        // Update pesan informasi
                        infoAlert.className = 'alert alert-primary mb-3';
                        infoAlert.innerHTML = '<strong>Pasien Baru:</strong> Silakan lengkapi semua data untuk pendaftaran pasien baru.';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Update pesan informasi jika terjadi error
                    infoAlert.className = 'alert alert-danger mb-3';
                    infoAlert.innerHTML = '<strong>Error:</strong> Terjadi kesalahan saat mencari data pasien. Silakan coba lagi.';

                    // Log error lebih detail untuk debugging
                    console.log('Detail error:', error.message);

                    // Tampilkan informasi URL yang diakses untuk debugging
                    console.log('URL yang diakses:', apiUrl);

                    // Aktifkan semua field untuk memungkinkan input manual
                    allFormFields.forEach(field => {
                        field.disabled = false;
                    });
                });
        }

        // Event listener untuk input NIK
        let typingTimer;
        nikInput.addEventListener('input', function() {
            clearTimeout(typingTimer);

            // Reset dan nonaktifkan form jika NIK tidak lengkap
            if (this.value.length !== 16) {
                allFormFields.forEach(field => {
                    field.disabled = true;
                });

                // Update pesan informasi
                infoAlert.className = 'alert alert-info mb-3';
                infoAlert.innerHTML = '<strong>Petunjuk:</strong> Masukkan NIK (16 digit) terlebih dahulu untuk melanjutkan pendaftaran.';
                return;
            }

            // Cek NIK jika sudah 16 digit
            typingTimer = setTimeout(() => searchPatient(this.value), 500);
        });

        // Form validation
        const form = document.getElementById('formPendaftaran');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        // Load jadwal when tempat or dokter changes
        const tempatSelect = document.getElementById('id_tempat_praktek');
        const dokterSelect = document.getElementById('id_dokter');
        const jadwalSelect = document.getElementById('id_jadwal');

        function loadJadwal() {
            const tempat = tempatSelect.value;
            const dokter = dokterSelect.value;

            if (tempat && dokter) {
                jadwalSelect.innerHTML = '<option value="">Memuat jadwal...</option>';

                // Tambahkan timestamp untuk mencegah caching
                const timestamp = new Date().getTime();

                // Gunakan path yang benar ke get_jadwal.php
                const url = `get_jadwal.php?tempat=${tempat}&dokter=${dokter}&_=${timestamp}`;

                console.log(`Fetching jadwal from: ${url}`);

                fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        },
                        cache: 'no-store'
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response headers:',
                            Array.from(response.headers.entries())
                            .map(([key, value]) => `${key}: ${value}`)
                            .join(', ')
                        );

                        // Periksa status response terlebih dahulu
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        // Periksa content-type
                        const contentType = response.headers.get('content-type');
                        console.log('Content-Type:', contentType);

                        if (!contentType || !contentType.includes('application/json')) {
                            // Coba ambil teks respons untuk debugging
                            return response.text().then(text => {
                                console.error('Response is not JSON:', text.substring(0, 200) + '...');
                                throw new Error(`Respons bukan JSON valid (${contentType})`);
                            });
                        }

                        return response.json();
                    })
                    .then(data => {
                        console.log('Jadwal data received:', data);
                        jadwalSelect.innerHTML = '<option value="">Pilih Jadwal</option>';

                        if (data.error) {
                            console.error('Server error:', data.error);
                            jadwalSelect.innerHTML = `<option value="">Error: ${data.error}</option>`;
                            return;
                        }

                        if (!Array.isArray(data)) {
                            console.error('Data is not an array:', data);
                            jadwalSelect.innerHTML = '<option value="">Format data tidak valid</option>';
                            return;
                        }

                        if (data.length === 0) {
                            jadwalSelect.innerHTML = '<option value="">Tidak ada jadwal tersedia</option>';
                            return;
                        }

                        data.forEach(jadwal => {
                            const option = document.createElement('option');
                            option.value = jadwal.ID_Jadwal_Rutin;
                            const jadwalText = `${jadwal.Hari} - ${jadwal.Jam_Mulai}-${jadwal.Jam_Selesai} (${jadwal.Jenis_Layanan})`;
                            option.textContent = jadwalText;
                            jadwalSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading jadwal:', error);
                        jadwalSelect.innerHTML = `<option value="">Error memuat jadwal: ${error.message}</option>`;
                    });
            } else {
                jadwalSelect.innerHTML = '<option value="">Pilih tempat praktek dan dokter terlebih dahulu</option>';
            }
        }

        tempatSelect.addEventListener('change', loadJadwal);
        dokterSelect.addEventListener('change', loadJadwal);

        if (tempatSelect.value && dokterSelect.value) {
            loadJadwal();
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
    .form-label {
        font-weight: 500;
    }
    .border-bottom {
        border-bottom: 2px solid #dee2e6 !important;
        margin-bottom: 1rem;
    }
";

// Include template
include_once '../template/layout.php';
?>