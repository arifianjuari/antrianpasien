<?php
require_once 'config/config.php';
require_once 'config/google_drive.php';

// Ambil file ID dari parameter
$fileId = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($fileId)) {
    header("HTTP/1.0 404 Not Found");
    exit('File ID tidak ditemukan');
}

try {
    // Dapatkan service Google Drive
    $driveService = getDriveService();

    // Ambil file metadata
    $file = $driveService->files->get($fileId);

    // Set content type berdasarkan MIME type file
    header('Content-Type: ' . $file->getMimeType());
    header('Cache-Control: public, max-age=86400'); // Cache selama 24 jam

    // Ambil URL download langsung
    $downloadUrl = "https://drive.google.com/uc?export=download&id=" . $fileId;

    // Download dan output file content
    readfile($downloadUrl);
} catch (Exception $e) {
    error_log('Error getting image: ' . $e->getMessage());
    header("HTTP/1.0 404 Not Found");
    exit('Gambar tidak ditemukan');
}
