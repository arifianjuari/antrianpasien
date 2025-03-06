<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Impor konfigurasi zona waktu
if (file_exists(__DIR__ . '/timezone.php')) {
    require_once __DIR__ . '/timezone.php';
}

// Database untuk aplikasi antrian pasien
$db2_host = 'auth-db1151.hstgr.io';
$db2_username = 'u609399718_admin_klinik';
$db2_password = 'Juari@2591';
$db2_database = 'u609399718_klinik_obgin';

// Base URL configuration
$base_url = 'http://localhost/antrian%20pasien';

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
        error_log("Database Connection Error in database.php: " . $e->getMessage());
        die("Koneksi database gagal: " . $e->getMessage());
    }
}

// Pastikan koneksi tersedia di scope lokal
$conn = $GLOBALS['conn'];

// Pastikan koneksi tersedia
if (!isset($conn) || !($conn instanceof PDO)) {
    error_log("Database Connection Not Available - conn variable check failed in database.php");
    die("Koneksi database tidak tersedia");
}
