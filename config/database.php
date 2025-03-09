<?php
// Enable error reporting for logging, but disable display
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Log untuk debugging di hosting
$log_file = dirname(__DIR__) . '/logs/database_errors.log';
function db_error_log($message)
{
    global $log_file;
    $date = date('Y-m-d H:i:s');
    error_log("[$date] $message" . PHP_EOL, 3, $log_file);
}

// Buat direktori logs jika belum ada
if (!file_exists(dirname(__DIR__) . '/logs')) {
    mkdir(dirname(__DIR__) . '/logs', 0755, true);
}

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

// Pastikan koneksi hanya dibuat sekali
if (!isset($GLOBALS['conn'])) {
    try {
        // Log connection attempt
        db_error_log("Attempting to connect to database: $db2_host, $db2_database");

        // Buat koneksi PDO dengan opsi yang lebih sederhana
        $conn = new PDO(
            "mysql:host=$db2_host;dbname=$db2_database;charset=utf8mb4",
            $db2_username,
            $db2_password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Tambahkan opsi timeout yang lebih lama untuk hosting
                PDO::ATTR_TIMEOUT => 5, // 5 detik timeout
            ]
        );

        // Test koneksi dengan query sederhana
        $stmt = $conn->query("SELECT 1");

        // Log successful connection
        db_error_log("Database connection successful in database.php");

        // Set global connection variable
        $GLOBALS['conn'] = $conn;
    } catch (PDOException $e) {
        db_error_log("Database Connection Error in database.php: " . $e->getMessage());

        // Jangan hentikan eksekusi, biarkan script menangani error
        $conn = null;
        $GLOBALS['conn'] = null;
        $GLOBALS['db_error'] = $e->getMessage();
    }
}

// Pastikan koneksi tersedia di scope lokal
$conn = $GLOBALS['conn'];

// Log koneksi status
if (!isset($conn) || !($conn instanceof PDO)) {
    db_error_log("Database Connection Not Available - conn variable check failed in database.php");
}
