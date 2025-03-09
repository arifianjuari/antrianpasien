<?php
// File: pendaftaran_sukses.php (root)
// Deskripsi: Redirect ke pendaftaran/pendaftaran_sukses.php

// Matikan pelaporan error untuk output
error_reporting(0);
ini_set('display_errors', 0);

// Aktifkan log error
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

// Log untuk debugging
error_log('Redirect dari root pendaftaran_sukses.php ke pendaftaran/pendaftaran_sukses.php');

// Ambil parameter dari URL
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Redirect ke file yang benar
header('Location: pendaftaran/pendaftaran_sukses.php' . ($id ? '?id=' . urlencode($id) : ''));
exit;
