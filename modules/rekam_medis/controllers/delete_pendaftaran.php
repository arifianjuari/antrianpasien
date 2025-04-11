<?php
// Izinkan akses dari origin manapun untuk menangani CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Jika request adalah OPTIONS, selesaikan request di sini
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit;
}

require_once '../../../config/database.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Fungsi untuk logging error
function logError($message)
{
    error_log("[" . date('Y-m-d H:i:s') . "] DELETE_PENDAFTARAN: " . $message);
}

// Log semua permintaan terlepas dari metode
logError("Request diterima dengan method: " . $_SERVER['REQUEST_METHOD']);
logError("Request URI: " . $_SERVER['REQUEST_URI']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed: ' . $_SERVER['REQUEST_METHOD']]);
    exit;
}

// Log data yang diterima
logError("POST data: " . print_r($_POST, true));

if (!isset($_POST['id_pendaftaran'])) {
    echo json_encode(['success' => false, 'message' => 'ID Pendaftaran tidak ditemukan']);
    exit;
}

$id_pendaftaran = $_POST['id_pendaftaran'];
logError("Mencoba menghapus pendaftaran dengan ID: " . $id_pendaftaran);

// Cek apakah koneksi database tersedia
if (!isset($conn) || !($conn instanceof PDO)) {
    logError("Koneksi database tidak tersedia");
    echo json_encode(['success' => false, 'message' => 'Koneksi database tidak tersedia']);
    exit;
}

try {
    // Mulai transaksi
    $conn->beginTransaction();
    logError("Transaksi dimulai");

    // Cek apakah data pendaftaran ada sebelum dihapus
    $check_query = "SELECT ID_Pendaftaran FROM pendaftaran WHERE ID_Pendaftaran = :id";
    try {
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':id', $id_pendaftaran);
        $check_stmt->execute();

        if ($check_stmt->rowCount() == 0) {
            // Data tidak ditemukan
            logError("Data pendaftaran dengan ID " . $id_pendaftaran . " tidak ditemukan");
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Data pendaftaran tidak ditemukan']);
            exit;
        }
        logError("Data pendaftaran ditemukan, melanjutkan proses");
    } catch (PDOException $e) {
        logError("Error saat memeriksa data pendaftaran: " . $e->getMessage());
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error saat memeriksa data: ' . $e->getMessage()]);
        exit;
    }

    // Langsung coba hapus tanpa memeriksa foreign key - lebih sederhana
    try {
        $query = "DELETE FROM pendaftaran WHERE ID_Pendaftaran = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id_pendaftaran);
        $stmt->execute();

        // Jika berhasil dihapus
        if ($stmt->rowCount() > 0) {
            $conn->commit();
            logError("Data pendaftaran berhasil dihapus");
            echo json_encode([
                'success' => true,
                'message' => 'Data pendaftaran berhasil dihapus',
                'action' => 'deleted'
            ]);
            exit;
        } else {
            // Ini seharusnya tidak terjadi karena kita sudah cek sebelumnya
            logError("Data ditemukan tapi tidak ada yang terhapus (aneh)");
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data pendaftaran']);
            exit;
        }
    } catch (PDOException $e) {
        // Jika gagal karena constraint, coba ubah status saja
        logError("Error saat menghapus: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");

        // Lakukan rollback
        $conn->rollBack();

        // Cek jenis error
        if (
            strpos($e->getMessage(), 'foreign key constraint') !== false ||
            strpos($e->getMessage(), 'Integrity constraint violation') !== false ||
            $e->getCode() == 23000
        ) {

            logError("Error constraint terdeteksi, mencoba update status menjadi Dibatalkan");

            // Coba transaksi baru untuk update status
            try {
                $conn->beginTransaction();
                $query = "UPDATE pendaftaran SET Status_Pendaftaran = 'Dibatalkan' WHERE ID_Pendaftaran = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $id_pendaftaran);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $conn->commit();
                    logError("Status berhasil diubah menjadi Dibatalkan");
                    echo json_encode([
                        'success' => true,
                        'message' => 'Data pendaftaran tidak dapat dihapus karena terkait dengan data lain. Status telah diubah menjadi Dibatalkan.',
                        'action' => 'updated'
                    ]);
                } else {
                    $conn->rollBack();
                    logError("Gagal mengubah status menjadi Dibatalkan");
                    echo json_encode(['success' => false, 'message' => 'Gagal mengubah status pendaftaran']);
                }
            } catch (PDOException $e2) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                logError("Error saat update status: " . $e2->getMessage());
                echo json_encode(['success' => false, 'message' => 'Gagal mengubah status pendaftaran: ' . $e2->getMessage()]);
            }
        } else {
            // Error lainnya
            logError("Error lain terdeteksi: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data pendaftaran: ' . $e->getMessage()]);
        }
    }
} catch (Exception $e) {
    // Error umum
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    logError("Error umum: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
