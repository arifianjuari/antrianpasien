<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;

session_start();

function getDriveService()
{
    try {
        $client = new Client();
        $client->setApplicationName('Klinik Drive Integration');
        $client->setScopes([Drive::DRIVE_FILE]);

        // Load credentials dari file yang didownload
        $credentialsPath = __DIR__ . '/credentials.json';

        if (!file_exists($credentialsPath)) {
            throw new Exception('File credentials.json tidak ditemukan di folder config/');
        }

        $client->setAuthConfig($credentialsPath);
        $client->setAccessType('offline');
        $client->setRedirectUri('http://localhost/antrian%20pasien/oauth2callback.php');

        // Cek jika ada token di session
        if (isset($_SESSION['upload_token'])) {
            $client->setAccessToken($_SESSION['upload_token']);
        }

        // Jika token expired, refresh
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $_SESSION['upload_token'] = $client->getAccessToken();
            } else {
                // Simpan halaman saat ini untuk redirect kembali setelah auth
                $_SESSION['redirect_after_auth'] = $_SERVER['HTTP_REFERER'] ?? null;

                // Redirect ke oauth2callback.php dengan URL yang benar
                header('Location: http://localhost/antrian%20pasien/oauth2callback.php');
                exit;
            }
        }

        return new Drive($client);
    } catch (Exception $e) {
        error_log('Error in getDriveService: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        throw new Exception('Gagal menginisialisasi layanan Google Drive: ' . $e->getMessage());
    }
}

// ID folder di Google Drive
define('DRIVE_EDUKASI_FOLDER', '1JOKYjdpQSXSjjFMap9ohkjt7cAsU88P7');

// Fungsi untuk mendapatkan URL gambar dari file ID
function getDriveImageUrl($fileId)
{
    if (empty($fileId)) {
        error_log('getDriveImageUrl: fileId kosong');
        return '';
    }
    global $base_url;
    error_log('getDriveImageUrl: generating URL for fileId: ' . $fileId);
    // Menggunakan proxy lokal untuk mengambil gambar
    $url = $base_url . "/get_drive_image.php?id=" . $fileId;
    error_log('getDriveImageUrl: generated URL: ' . $url);
    return $url;
}

// Fungsi untuk upload file ke Google Drive
function uploadToDrive($file, $filename)
{
    try {
        // Validasi file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('File tidak valid');
        }

        // Validasi ukuran file (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('Ukuran file terlalu besar (maksimal 5MB)');
        }

        // Validasi tipe file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipe file tidak didukung (hanya JPG, PNG, dan GIF)');
        }

        $driveService = getDriveService();

        // File metadata
        $fileMetadata = new DriveFile([
            'name' => $filename,
            'parents' => [DRIVE_EDUKASI_FOLDER]
        ]);

        // Upload file
        $content = file_get_contents($file['tmp_name']);
        if ($content === false) {
            throw new Exception('Gagal membaca file');
        }

        $uploadedFile = $driveService->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $file['type'],
            'uploadType' => 'multipart',
            'fields' => 'id,webViewLink'
        ]);

        // Set permission agar bisa diakses public
        $permission = new Permission([
            'type' => 'anyone',
            'role' => 'reader'
        ]);

        $driveService->permissions->create($uploadedFile->id, $permission);

        return $uploadedFile->id;
    } catch (Exception $e) {
        error_log('Error in uploadToDrive: ' . $e->getMessage());
        throw new Exception('Gagal mengupload file: ' . $e->getMessage());
    }
}
