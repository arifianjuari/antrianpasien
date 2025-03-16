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
            SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as sudah_dilayani,
            SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as sedang_menunggu
        FROM antrian 
        WHERE DATE(tanggal) = ?
    ");
    $stmt->execute([$today]);
    $response['statistik'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query untuk antrian yang sedang dilayani
    $stmt = $pdo->prepare("
        SELECT a.*, p.nama 
        FROM antrian a 
        JOIN pasien p ON a.id_pasien = p.id 
        WHERE a.status = 'dilayani' 
        AND DATE(a.tanggal) = ? 
        ORDER BY a.updated_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$today]);
    $response['antrian_sekarang'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query untuk antrian berikutnya
    $stmt = $pdo->prepare("
        SELECT a.*, p.nama 
        FROM antrian a 
        JOIN pasien p ON a.id_pasien = p.id 
        WHERE a.status = 'menunggu' 
        AND DATE(a.tanggal) = ? 
        ORDER BY a.no_antrian ASC 
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
