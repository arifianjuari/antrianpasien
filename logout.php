<?php
session_start();

// Simpan pesan sukses sebelum menghapus session
$_SESSION['logout_message'] = "Anda telah berhasil keluar dari sistem.";

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Hancurkan session
session_destroy();

// Start session baru untuk pesan
session_start();
$_SESSION['success_message'] = "Anda telah berhasil keluar dari sistem.";

// Redirect ke home.php
header("Location: pendaftaran/form_pendaftaran_pasien.php");
exit;
