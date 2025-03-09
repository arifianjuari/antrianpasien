<?php
// Script untuk mengupdate status_aktif pengumuman menjadi 0 jika sudah melewati tanggal_berakhir
// File ini dapat dijalankan melalui cron job setiap hari pada tengah malam

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

// Tanggal hari ini
$current_date = date('Y-m-d');

// Update status_aktif menjadi 0 untuk pengumuman yang sudah melewati tanggal_berakhir
$query = "UPDATE pengumuman 
          SET status_aktif = 0 
          WHERE status_aktif = 1 
          AND tanggal_berakhir IS NOT NULL 
          AND tanggal_berakhir < ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $current_date);
$stmt->execute();

// Hitung jumlah baris yang diupdate
$affected_rows = $stmt->affected_rows;

// Log hasil
$log_message = date('Y-m-d H:i:s') . " - " . $affected_rows . " pengumuman dinonaktifkan karena sudah melewati tanggal_berakhir.\n";
file_put_contents(__DIR__ . '/../logs/pengumuman_update.log', $log_message, FILE_APPEND);

// Output hasil jika dijalankan dari browser
echo $affected_rows . " pengumuman dinonaktifkan karena sudah melewati tanggal_berakhir.";

// Tutup koneksi
$stmt->close();
$conn->close();
