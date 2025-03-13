<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

// Cek apakah user adalah admin
if (!isAdmin()) {
    header('Location: ' . $base_url . '/login.php');
    exit;
}

try {
    // Cek apakah tabel edukasi sudah ada
    $stmt = $conn->query("SHOW TABLES LIKE 'edukasi'");
    $tableExists = $stmt->rowCount() > 0;

    if (!$tableExists) {
        // Buat tabel edukasi jika belum ada
        $sql = "CREATE TABLE `edukasi` (
            `id_edukasi` char(36) NOT NULL,
            `judul` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `kategori` enum('Kesehatan Umum','Kehamilan','Persalinan','Pasca Melahirkan','Kesehatan Wanita','Tips dan Lifestyle') NOT NULL,
            `gambar` varchar(255) DEFAULT NULL,
            `konten` text NOT NULL,
            `ringkasan` text DEFAULT NULL,
            `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
            `created_by` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id_edukasi`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $conn->exec($sql);
        echo "Tabel edukasi berhasil dibuat!";
    } else {
        echo "Tabel edukasi sudah ada.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
