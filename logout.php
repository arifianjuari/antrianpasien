<?php
session_start();

// Simpan base_url sebelum menghapus session
require_once __DIR__ . '/config/config.php';

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Hapus cookie lain yang mungkin diset saat login
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// Hancurkan session
session_destroy();

// Start session baru khusus untuk pesan
session_start();
$_SESSION['message'] = array(
    'type' => 'success',
    'text' => 'Anda telah berhasil keluar dari sistem.'
);

// Redirect ke halaman form pendaftaran
header("Location: " . $base_url . "/pendaftaran/form_pendaftaran_pasien.php");
exit;
