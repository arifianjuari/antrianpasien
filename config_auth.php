<?php
// Database untuk aplikasi antrian pasien - db2
$db2_host = 'auth-db1151.hstgr.io';
$db2_username = 'u609399718_admin_klinik';
$db2_password = 'Juari@2591';
$db2_database = 'u609399718_klinik_obgin';

// Include konfigurasi base URL dari file config/config.php
require_once __DIR__ . '/config/config.php';

try {
    $conn_db2 = new PDO("mysql:host=$db2_host;dbname=$db2_database", $db2_username, $db2_password);
    $conn_db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tambahkan variabel $auth_conn yang merujuk ke koneksi yang sama
    $auth_conn = $conn_db2;
} catch (PDOException $e) {
    die("Connection to DB2 (Antrian) failed: " . $e->getMessage());
}
