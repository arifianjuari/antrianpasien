<?php
// Database RS (readonly) - db1
$db1_host = '103.76.149.29';
$db1_username = 'web_hasta';
$db1_password = '@Admin123/';
$db1_database = 'simsvbaru';

try {
    $conn_db1 = new PDO("mysql:host=$db1_host;dbname=$db1_database", $db1_username, $db1_password);
    $conn_db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection to DB1 (RS) failed: " . $e->getMessage();
    die();
}
