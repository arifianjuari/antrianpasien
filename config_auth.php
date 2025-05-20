<?php
// Database untuk aplikasi antrian pasien - db2
$db2_host = 'auth-db1151.hstgr.io';
$db2_username = 'u609399718_adminpraktek';
$db2_password = 'Obgin@12345';
$db2_database = 'u609399718_praktekobgin';

// Include konfigurasi base URL dari file config/config.php
require_once __DIR__ . '/config/config.php';

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Use MySQLi for database connection (consistency with other modules)
$auth_conn = new mysqli($db2_host, $db2_username, $db2_password, $db2_database);
if ($auth_conn->connect_error) {
    die("Connection to DB2 (Antrian) failed: " . $auth_conn->connect_error);
}

