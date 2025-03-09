<?php
// File: get_jadwal.php
// Deskripsi: API untuk mendapatkan jadwal praktek dokter berdasarkan tempat praktek, dokter, tanggal, dan hari

// Matikan pelaporan error PHP untuk mencegah output HTML
error_reporting(0);
ini_set('display_errors', 0);

// Pastikan tidak ada output sebelum header
ob_start();

// Fungsi untuk mengirim respons JSON dan keluar
function sendJsonResponse($data, $statusCode = 200)
{
    // Bersihkan output buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // Set header JSON
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    http_response_code($statusCode);

    // Kembalikan hasil
    echo json_encode($data);
    exit;
}

// Tangkap semua error PHP
try {
    // Periksa apakah file database.php ada
    if (!file_exists('../config/database.php')) {
        sendJsonResponse(['error' => 'File konfigurasi database tidak ditemukan'], 500);
    }

    require_once '../config/database.php';

    // Periksa koneksi database
    if (!isset($conn) || !$conn) {
        sendJsonResponse(['error' => 'Koneksi database tidak tersedia'], 500);
    }

    // Ambil parameter
    $id_tempat_praktek = isset($_GET['tempat']) ? $_GET['tempat'] : '';
    $id_dokter = isset($_GET['dokter']) ? $_GET['dokter'] : '';

    // Validasi parameter
    if (empty($id_tempat_praktek) || empty($id_dokter)) {
        sendJsonResponse(['error' => 'Parameter tempat dan dokter diperlukan'], 400);
    }

    $query = "
        SELECT 
            jr.ID_Jadwal_Rutin,
            jr.Hari,
            jr.Jam_Mulai,
            jr.Jam_Selesai,
            jr.Kuota_Pasien as Kuota,
            jr.Jenis_Layanan,
            jr.Status_Aktif,
            d.Nama_Dokter,
            d.Spesialisasi,
            tp.Nama_Tempat
        FROM 
            jadwal_rutin jr
        LEFT JOIN 
            dokter d ON jr.ID_Dokter = d.ID_Dokter
        LEFT JOIN 
            tempat_praktek tp ON jr.ID_Tempat_Praktek = tp.ID_Tempat_Praktek
        WHERE 
            jr.Status_Aktif = 1
            AND jr.ID_Tempat_Praktek = :id_tempat_praktek
            AND jr.ID_Dokter = :id_dokter
        ORDER BY 
            CASE jr.Hari
                WHEN 'Senin' THEN 1
                WHEN 'Selasa' THEN 2
                WHEN 'Rabu' THEN 3
                WHEN 'Kamis' THEN 4
                WHEN 'Jumat' THEN 5
                WHEN 'Sabtu' THEN 6
                WHEN 'Minggu' THEN 7
            END ASC,
            jr.Jam_Mulai ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_tempat_praktek', $id_tempat_praktek);
    $stmt->bindParam(':id_dokter', $id_dokter);
    $stmt->execute();

    $jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format jam untuk tampilan
    foreach ($jadwal as &$j) {
        $j['Jam_Mulai'] = date('H:i', strtotime($j['Jam_Mulai']));
        $j['Jam_Selesai'] = date('H:i', strtotime($j['Jam_Selesai']));
    }

    // Kirim respons JSON
    sendJsonResponse($jadwal);
} catch (PDOException $e) {
    // Log error
    error_log("Database Error in get_jadwal.php: " . $e->getMessage());

    // Kirim respons error
    sendJsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    // Log error
    error_log("Error in get_jadwal.php: " . $e->getMessage());

    // Kirim respons error
    sendJsonResponse(['error' => $e->getMessage()], 400);
}
