<?php
session_start();

// Dapatkan root directory project
$root_dir = dirname(dirname(dirname(__DIR__)));

// Include database configuration
require_once $root_dir . '/config/database.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Aktifkan error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Fungsi untuk logging error
function logError($message) {
    error_log("[" . date('Y-m-d H:i:s') . "] DELETE_PENDAFTARAN: " . $message);
}

// Log request
logError("Request diterima dengan method: " . $_SERVER['REQUEST_METHOD']);
logError("POST data: " . print_r($_POST, true));

// Cek apakah ada ID yang dikirim
if (!isset($_POST['id_pendaftaran']) || empty($_POST['id_pendaftaran'])) {
    logError("ID Pendaftaran tidak ditemukan atau kosong");
    echo json_encode(['success' => false, 'message' => 'ID Pendaftaran tidak ditemukan']);
    exit;
}

$id_pendaftaran = trim($_POST['id_pendaftaran']);
logError("Mencoba menghapus pendaftaran dengan ID: " . $id_pendaftaran);

try {
    // Mulai transaksi
    $conn->beginTransaction();
    logError("Transaksi dimulai");
    
    // Cek apakah data pendaftaran ada
    $check_query = "SELECT ID_Pendaftaran FROM pendaftaran WHERE ID_Pendaftaran = :id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bindParam(':id', $id_pendaftaran);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() == 0) {
        logError("Data pendaftaran tidak ditemukan");
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Data pendaftaran tidak ditemukan']);
        exit;
    }
    
    logError("Data pendaftaran ditemukan, melanjutkan proses");
    
    // Hapus data pendaftaran
    $query = "DELETE FROM pendaftaran WHERE ID_Pendaftaran = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id_pendaftaran);
    $stmt->execute();
    
    // Commit transaksi
    $conn->commit();
    logError("Data pendaftaran berhasil dihapus");
    
    echo json_encode(['success' => true, 'message' => 'Data pendaftaran berhasil dihapus']);
} catch (PDOException $e) {
    // Rollback jika terjadi error
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    logError("Database Error: " . $e->getMessage());
    
    // Coba pendekatan kedua: Ubah status menjadi Dibatalkan
    try {
        $conn->beginTransaction();
        
        $update_query = "UPDATE pendaftaran SET Status_Pendaftaran = 'Dibatalkan' WHERE ID_Pendaftaran = :id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':id', $id_pendaftaran);
        $update_stmt->execute();
        
        $conn->commit();
        logError("Status pendaftaran berhasil diubah menjadi Dibatalkan");
        
        echo json_encode(['success' => true, 'message' => 'Data pendaftaran berhasil dibatalkan']);
    } catch (PDOException $e2) {
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
        }
        
        logError("Error pada pendekatan kedua: " . $e2->getMessage());
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus data pendaftaran: ' . $e2->getMessage()]);
    }
}
