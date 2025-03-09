<?php
// Include error handler
require_once 'error_handler.php';

// Log semua request untuk debugging
error_log("Request received at index.php: " . $_SERVER['REQUEST_URI']);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', __DIR__);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once 'config/database.php';
require_once 'config/config.php';

// Log request information
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Query String: " . ($_SERVER['QUERY_STRING'] ?? ''));
error_log("Script Name: " . $_SERVER['SCRIPT_NAME']);
error_log("PHP_SELF: " . $_SERVER['PHP_SELF']);

// Parse request URI untuk routing
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
$path = '/';

// Hapus base path dari request URI jika ada
if (!empty($base_path) && strpos($request_uri, $base_path) === 0) {
    $path = substr($request_uri, strlen($base_path));
}

// Hapus query string jika ada
if (($pos = strpos($path, '?')) !== false) {
    $path = substr($path, 0, $pos);
}

// Normalize path
$path = '/' . trim($path, '/');
error_log("Normalized Path: " . $path);

// Jika mengakses root path
if ($path == '/' || $path == '/index.php') {
    // Jika belum login, tampilkan halaman login
    if (!isset($_SESSION['user_id'])) {
        error_log("Root access, user not logged in, showing login page");
        include 'login.php';
        exit;
    } else {
        // Jika sudah login, redirect ke halaman utama
        error_log("Root access, user logged in, redirecting to main page");
        header("Location: " . BASE_URL . "/index.php?module=rekam_medis&action=data_pasien");
        exit;
    }
}

// Cek koneksi database
if (!isset($conn) || !($conn instanceof PDO)) {
    error_log("Database connection not available in index.php");
    die("Koneksi database tidak tersedia. Silakan hubungi administrator.");
}

// Cek apakah user sudah login untuk halaman yang memerlukan login
if (!isset($_SESSION['user_id']) && $path != '/login.php') {
    error_log("User not logged in, redirecting to login");
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

// Ambil modul dan action dari parameter GET
$module = isset($_GET['module']) ? $_GET['module'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Log routing information
error_log("Module: " . $module);
error_log("Action: " . $action);

// Jika tidak ada modul yang ditentukan tapi ada path khusus, coba routing berdasarkan path
if (empty($module) && $path != '/') {
    // Hapus leading slash
    $path_parts = explode('/', trim($path, '/'));

    if (count($path_parts) >= 1) {
        $module = $path_parts[0];
        error_log("Module from path: " . $module);

        if (count($path_parts) >= 2) {
            $action = $path_parts[1];
            error_log("Action from path: " . $action);
        }
    }
}

try {
    // Jika modul adalah rekam_medis
    if ($module == 'rekam_medis') {
        // Load controller
        require_once 'modules/rekam_medis/controllers/RekamMedisController.php';

        // Inisialisasi controller dengan koneksi database
        $rekamMedisController = new RekamMedisController($conn);

        // Jika tidak ada action yang ditentukan untuk modul rekam_medis, arahkan ke data_pasien
        if (empty($action)) {
            header('Location: ' . BASE_URL . '/index.php?module=rekam_medis&action=data_pasien');
            exit;
        }

        // Start output buffering
        ob_start();

        // Routing untuk modul rekam medis
        if ($module == 'rekam_medis') {
            // Set page title
            $page_title = "Rekam Medis";

            // Routing berdasarkan action
            switch ($action) {
                case 'manajemen_antrian':
                    $rekamMedisController->manajemenAntrian();
                    break;
                case 'data_pasien':
                    $rekamMedisController->dataPasien();
                    break;
                case 'cari_pasien':
                    $rekamMedisController->cariPasien();
                    break;
                case 'tambah_pasien':
                    $rekamMedisController->tambahPasien();
                    break;
                case 'simpan_pasien':
                    $rekamMedisController->simpanPasien();
                    break;
                case 'cek_nik_pasien':
                    $rekamMedisController->cekNikPasien();
                    break;
                case 'detailPasien':
                    $rekamMedisController->detailPasien($_GET['no_rkm_medis']);
                    break;
                case 'detail_pasien':
                    $rekamMedisController->detailPasien($_GET['no_rkm_medis']);
                    break;
                case 'editPasien':
                    $rekamMedisController->editPasien();
                    break;
                case 'updatePasien':
                    $rekamMedisController->updatePasien();
                    break;
                case 'hapusPasien':
                    $rekamMedisController->hapusPasien();
                    break;
                case 'tambah_pemeriksaan':
                    error_log("Routing to tambah_pemeriksaan");
                    $rekamMedisController->tambah_pemeriksaan();
                    break;
                case 'simpan_pemeriksaan':
                    error_log("Routing to simpan_pemeriksaan");
                    $rekamMedisController->simpan_pemeriksaan();
                    break;
                case 'edit_pemeriksaan':
                    error_log("Routing to edit_pemeriksaan with id: " . ($_GET['id'] ?? 'no id'));
                    $rekamMedisController->edit_pemeriksaan();
                    break;
                case 'update_pemeriksaan':
                    $rekamMedisController->update_pemeriksaan();
                    break;
                case 'tambah_penilaian_medis':
                    $rekamMedisController->tambahPenilaianMedis();
                    break;
                case 'simpan_penilaian_medis':
                    $rekamMedisController->simpanPenilaianMedis();
                    break;
                case 'simpan_penilaian_medis_ralan_kandungan':
                    $rekamMedisController->simpan_penilaian_medis_ralan_kandungan();
                    break;
                case 'tambah_tindakan_medis':
                    $rekamMedisController->tambahTindakanMedis();
                    break;
                case 'simpan_tindakan_medis':
                    $rekamMedisController->simpanTindakanMedis();
                    break;
                case 'edit_tindakan_medis':
                    $rekamMedisController->editTindakanMedis($_GET['id']);
                    break;
                case 'update_tindakan_medis':
                    $rekamMedisController->updateTindakanMedis();
                    break;
                case 'hapus_tindakan_medis':
                    $rekamMedisController->hapusTindakanMedis($_GET['id']);
                    break;
                case 'detail_tindakan_medis':
                    $rekamMedisController->detailTindakanMedis($_GET['id']);
                    break;
                case 'form_penilaian_medis_ralan_kandungan':
                    $rekamMedisController->formPenilaianMedisRalanKandungan();
                    break;
                case 'edit_kunjungan':
                    $rekamMedisController->edit_kunjungan();
                    break;
                case 'update_kunjungan':
                    $rekamMedisController->update_kunjungan();
                    break;
                case 'hapus_kunjungan':
                    $rekamMedisController->hapus_kunjungan();
                    break;
                case 'update_status':
                    $rekamMedisController->update_status();
                    break;
                case 'tambah_status_obstetri':
                    $rekamMedisController->tambah_status_obstetri();
                    break;
                case 'simpan_status_obstetri':
                    $rekamMedisController->simpan_status_obstetri();
                    break;
                case 'edit_status_obstetri':
                    $rekamMedisController->edit_status_obstetri();
                    break;
                case 'update_status_obstetri':
                    $rekamMedisController->update_status_obstetri();
                    break;
                case 'hapus_status_obstetri':
                    $rekamMedisController->hapus_status_obstetri();
                    break;
                case 'tambah_riwayat_kehamilan':
                    $rekamMedisController->tambah_riwayat_kehamilan();
                    break;
                case 'simpan_riwayat_kehamilan':
                    $rekamMedisController->simpan_riwayat_kehamilan();
                    break;
                case 'edit_riwayat_kehamilan':
                    $rekamMedisController->edit_riwayat_kehamilan();
                    break;
                case 'update_riwayat_kehamilan':
                    $rekamMedisController->update_riwayat_kehamilan();
                    break;
                case 'hapus_riwayat_kehamilan':
                    $rekamMedisController->hapus_riwayat_kehamilan();
                    break;
                case 'tambah_status_ginekologi':
                    $rekamMedisController->tambah_status_ginekologi();
                    break;
                case 'simpan_status_ginekologi':
                    $rekamMedisController->simpan_status_ginekologi();
                    break;
                case 'edit_status_ginekologi':
                    $rekamMedisController->edit_status_ginekologi();
                    break;
                case 'update_status_ginekologi':
                    $rekamMedisController->update_status_ginekologi();
                    break;
                case 'generate_pdf':
                    error_log("Routing to generate_pdf");
                    $rekamMedisController->generate_pdf();
                    break;
                case 'generate_status_obstetri_pdf':
                    error_log("Routing to generate_status_obstetri_pdf");
                    $rekamMedisController->generate_status_obstetri_pdf();
                    break;
                default:
                    if (empty($action)) {
                        $rekamMedisController->index();
                    } else {
                        error_log("Invalid action requested: " . $action);
                        throw new Exception("Halaman tidak ditemukan");
                    }
                    break;
            }
        } else {
            // Jika modul tidak ditemukan, redirect ke home
            header("Location: " . BASE_URL . "/home.php");
            exit;
        }
    } else {
        // Jika modul tidak ditemukan, redirect ke home
        header("Location: " . BASE_URL . "/home.php");
        exit;
    }
} catch (Exception $e) {
    error_log("Error in routing: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header('Location: ' . BASE_URL . '/index.php?module=rekam_medis&action=data_pasien');
    exit;
}

// Get the buffered content
$content = ob_get_clean();

// Include the layout template
include 'template/layout.php';
