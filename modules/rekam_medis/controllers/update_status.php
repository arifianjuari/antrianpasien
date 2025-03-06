<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['id_pendaftaran']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$id_pendaftaran = $_POST['id_pendaftaran'];
$status = $_POST['status'];

try {
    $stmt = $conn->prepare("UPDATE pendaftaran SET Status_Pendaftaran = ? WHERE ID_Pendaftaran = ?");
    $stmt->execute([$status, $id_pendaftaran]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Status berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tidak ada data yang diupdate']);
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan database']);
}
