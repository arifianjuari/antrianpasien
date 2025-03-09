<?php
// Kredensial database
$db_host = 'auth-db1151.hstgr.io';
$db_username = 'u609399718_adminpraktek';
$db_password = 'Obgin@12345';
$db_database = 'u609399718_praktekobgin';

// Buat koneksi
$conn = new mysqli($db_host, $db_username, $db_password, $db_database);

// Cek koneksi database
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Baca file SQL
$sql_file = file_get_contents(__DIR__ . '/create_table_pengumuman.sql');

// Jalankan query SQL
if ($conn->multi_query($sql_file)) {
    echo "Tabel pengumuman berhasil dibuat!";

    // Bersihkan hasil query
    while ($conn->more_results() && $conn->next_result()) {
        // Kosongkan buffer hasil
        if ($result = $conn->store_result()) {
            $result->free();
        }
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
