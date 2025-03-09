<?php
// Enable error reporting for logging, but disable display
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Impor konfigurasi zona waktu
if (file_exists(__DIR__ . '/timezone.php')) {
    require_once __DIR__ . '/timezone.php';
}

// Impor konfigurasi base URL
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

// Database untuk aplikasi antrian pasien
$db2_host = 'auth-db1151.hstgr.io';
$db2_username = 'u609399718_adminpraktek';
$db2_password = 'Obgin@12345';
$db2_database = 'u609399718_praktekobgin';

// Fungsi untuk menangani error database tanpa output HTML
function handleDatabaseError($message, $exception = null)
{
    // Log error
    if ($exception) {
        error_log("Database Error: " . $exception->getMessage());
    } else {
        error_log("Database Error: " . $message);
    }

    // Jika ini adalah request AJAX/API (biasanya mengharapkan JSON)
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ||
        strpos($_SERVER['REQUEST_URI'], 'get_') !== false ||
        isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    ) {

        // Bersihkan output buffer jika ada
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Kirim respons JSON
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $message]);
        exit;
    }

    // Untuk request normal, tampilkan pesan error yang ramah
    return false;
}

// Pastikan koneksi hanya dibuat sekali
if (!isset($GLOBALS['conn'])) {
    try {
        // Log connection attempt
        error_log("Attempting to connect to database: $db2_host, $db2_database");

        // Buat koneksi PDO dengan opsi yang lebih sederhana
        $conn = new PDO(
            "mysql:host=$db2_host;dbname=$db2_database;charset=utf8mb4",
            $db2_username,
            $db2_password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        // Test koneksi dengan query sederhana
        $stmt = $conn->query("SELECT 1");

        // Log successful connection
        error_log("Database connection successful in database.php");

        // Set global connection variable
        $GLOBALS['conn'] = $conn;
    } catch (PDOException $e) {
        // Tangani error tanpa output HTML
        if (handleDatabaseError("Koneksi database gagal", $e) === false) {
            // Untuk request non-AJAX, set variabel error yang dapat digunakan template
            $db_connection_error = "Koneksi database gagal. Silakan coba lagi nanti.";
        }
    }
}

// Pastikan koneksi tersedia di scope lokal
$conn = isset($GLOBALS['conn']) ? $GLOBALS['conn'] : null;

// Pastikan koneksi tersedia
if (!isset($conn) || !($conn instanceof PDO)) {
    error_log("Database Connection Not Available - conn variable check failed in database.php");

    // Tangani error tanpa output HTML
    if (handleDatabaseError("Koneksi database tidak tersedia") === false) {
        // Untuk request non-AJAX, set variabel error yang dapat digunakan template
        $db_connection_error = "Koneksi database tidak tersedia. Silakan coba lagi nanti.";
    }
}
