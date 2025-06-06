<?php
require_once 'config/koneksi.php';

try {
    $conn_db2 = getPDOConnection();
    $query = "SELECT * FROM menu_layanan ORDER BY nama_layanan";
    $stmt = $conn_db2->query($query);
    $layanan = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($layanan);
    echo "</pre>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
