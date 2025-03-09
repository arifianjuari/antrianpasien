<?php
// Pastikan tidak ada output sebelum header
error_reporting(E_ALL);
ini_set('display_errors', 0); // Matikan display error agar tidak mengganggu output JSON

// Log untuk debugging di hosting
$log_file = dirname(__DIR__) . '/logs/api_errors.log';
function custom_error_log($message)
{
    global $log_file;
    $date = date('Y-m-d H:i:s');
    error_log("[$date] $message" . PHP_EOL, 3, $log_file);
}

// Buat direktori logs jika belum ada
if (!file_exists(dirname(__DIR__) . '/logs')) {
    mkdir(dirname(__DIR__) . '/logs', 0755, true);
}

// Fungsi untuk mengembalikan respons JSON dan keluar
function sendJsonResponse($data)
{
    // Pastikan tidak ada output sebelumnya
    if (ob_get_length()) ob_clean();

    // Set header dengan benar
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Tangkap semua output yang mungkin terjadi
ob_start();

try {
    // Log untuk debugging
    custom_error_log("check_patient.php dipanggil dengan NIK: " . (isset($_GET['nik']) ? $_GET['nik'] : 'tidak ada'));

    // Path absolut untuk file database.php
    $database_file = dirname(__DIR__) . '/config/database.php';
    custom_error_log("Mencoba memuat file database dari: " . $database_file);

    if (!file_exists($database_file)) {
        throw new Exception("File database.php tidak ditemukan di path: " . $database_file);
    }

    require_once $database_file;
    custom_error_log("File database.php berhasil dimuat");

    $nik = isset($_GET['nik']) ? trim($_GET['nik']) : '';
    $response = ['found' => false];

    if (strlen($nik) === 16) {
        try {
            // Pastikan koneksi database tersedia
            if (!isset($conn) || !($conn instanceof PDO)) {
                throw new Exception("Koneksi database tidak tersedia");
            }

            custom_error_log("Mencoba query database untuk NIK: " . $nik);

            $query = "SELECT no_ktp, nm_pasien, tgl_lahir, jk, no_tlp, alamat, kd_kec, pekerjaan 
                    FROM pasien 
                    WHERE no_ktp = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$nik]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($patient) {
                custom_error_log("Data pasien ditemukan untuk NIK: " . $nik);

                // Format tanggal lahir ke format Y-m-d untuk input date HTML
                if (isset($patient['tgl_lahir'])) {
                    $date = new DateTime($patient['tgl_lahir']);
                    $patient['tgl_lahir'] = $date->format('Y-m-d');
                }

                // Pastikan semua field yang dibutuhkan tersedia
                if (!isset($patient['pekerjaan'])) {
                    $patient['pekerjaan'] = '';
                }

                // Konversi kd_kec ke string jika perlu
                if (isset($patient['kd_kec'])) {
                    $patient['kd_kec'] = (string)$patient['kd_kec'];
                }

                $response = [
                    'found' => true,
                    'patient' => $patient
                ];
            } else {
                custom_error_log("Data pasien tidak ditemukan untuk NIK: " . $nik);
            }
        } catch (PDOException $e) {
            custom_error_log("Database Error: " . $e->getMessage());
            $response = [
                'found' => false,
                'error' => 'Terjadi kesalahan database: ' . $e->getMessage()
            ];
        }
    }

    // Bersihkan output buffer sebelum mengirim respons JSON
    ob_end_clean();
    sendJsonResponse($response);
} catch (Throwable $e) {
    // Tangkap semua error dan exception
    custom_error_log("Fatal Error: " . $e->getMessage());

    // Bersihkan output buffer sebelum mengirim respons JSON
    ob_end_clean();
    sendJsonResponse([
        'found' => false,
        'error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
    ]);
}
