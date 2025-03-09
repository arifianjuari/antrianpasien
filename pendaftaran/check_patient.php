<?php
// Pastikan tidak ada output sebelum header
error_reporting(E_ALL);
ini_set('display_errors', 0); // Matikan display error agar tidak mengganggu output JSON

// Fungsi untuk mengembalikan respons JSON dan keluar
function sendJsonResponse($data)
{
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

try {
    require_once '../config/database.php';

    $nik = isset($_GET['nik']) ? trim($_GET['nik']) : '';
    $response = ['found' => false];

    if (strlen($nik) === 16) {
        try {
            $query = "SELECT no_ktp, nm_pasien, tgl_lahir, jk, no_tlp, alamat, kd_kec, pekerjaan 
                    FROM pasien 
                    WHERE no_ktp = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$nik]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($patient) {
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
            }
        } catch (PDOException $e) {
            $response = [
                'found' => false,
                'error' => 'Terjadi kesalahan database: ' . $e->getMessage()
            ];
            error_log("Database Error in check_patient.php: " . $e->getMessage());
        }
    }

    sendJsonResponse($response);
} catch (Throwable $e) {
    // Tangkap semua error dan exception
    error_log("Fatal Error in check_patient.php: " . $e->getMessage());
    sendJsonResponse([
        'found' => false,
        'error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
    ]);
}
