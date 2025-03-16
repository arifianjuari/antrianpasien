<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

// Cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $today = date('Y-m-d');
    $response = [];

    // Query untuk statistik
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_antrian,
            SUM(CASE WHEN Status_Pendaftaran = 'selesai' THEN 1 ELSE 0 END) as sudah_dilayani,
            SUM(CASE WHEN Status_Pendaftaran = 'menunggu' THEN 1 ELSE 0 END) as sedang_menunggu
        FROM pendaftaran 
        WHERE DATE(Waktu_Perkiraan) = ?
    ");
    $stmt->execute([$today]);
    $response['statistik'] = $stmt->fetch(PDO::FETCH_ASSOC);

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
    $response['antrian_sekarang'] = $stmt->fetch(PDO::FETCH_ASSOC);

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
    $response['antrian_berikutnya'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query untuk pengumuman terkini
    $stmt = $pdo->query("
        SELECT * FROM pengumuman 
        WHERE status = 'active' 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $response['pengumuman'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set header JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
