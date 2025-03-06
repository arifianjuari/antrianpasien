<?php
// File: get_jadwal.php
// Deskripsi: API untuk mendapatkan jadwal praktek dokter berdasarkan tempat praktek, dokter, tanggal, dan hari

header('Content-Type: application/json');
require_once '../config/database.php';

// Ambil parameter
$id_tempat_praktek = isset($_GET['tempat']) ? $_GET['tempat'] : '';
$id_dokter = isset($_GET['dokter']) ? $_GET['dokter'] : '';
$hari = isset($_GET['hari']) ? $_GET['hari'] : '';

try {
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

    // Debug: Log parameters dan hasil
    error_log("Parameters - Tempat: $id_tempat_praktek, Dokter: $id_dokter");
    error_log("Result: " . json_encode($jadwal));

    echo json_encode($jadwal);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
