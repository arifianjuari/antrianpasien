<?php
// Include error handler
require_once 'error_handler.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', __DIR__);

// Start session if not already started - kompatibel dengan berbagai versi PHP
if (function_exists('session_status')) {
    // PHP 5.4.0 atau lebih baru
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
} else {
    // PHP versi lama
    if (!headers_sent()) {
        @session_start();
    }
}

// Include database configuration
require_once 'config/database.php';
require_once 'config/config.php';

// Log request information
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Query String: " . ($_SERVER['QUERY_STRING'] ?? ''));

// Cek koneksi database
if (!isset($conn) || !($conn instanceof PDO)) {
    error_log("Database connection not available in index.php");
    die("Koneksi database tidak tersedia. Silakan hubungi administrator.");
}

// Load controller
require_once 'modules/rekam_medis/controllers/RekamMedisController.php';

try {
    // Inisialisasi controller dengan koneksi database
    $rekamMedisController = new RekamMedisController($conn);

    // Ambil modul dari parameter GET
    $module = isset($_GET['module']) ? $_GET['module'] : '';
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    // Log routing information
    error_log("Module: " . $module);
    error_log("Action: " . $action);

    // Cek apakah user sudah login
    if (!isset($_SESSION['user_id'])) {
        // Redirect ke login page jika mencoba mengakses halaman yang memerlukan login
        if (!empty($module) || !empty($action)) {
            header("Location: " . BASE_URL . "/login.php");
            exit;
        }
        // Jika mengakses root, tampilkan halaman login
        include 'login.php';
        exit;
    }

    // Jika tidak ada action yang ditentukan untuk modul rekam_medis, arahkan ke data_pasien
    if ($module == 'rekam_medis' && empty($action)) {
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
            case 'daftar_atensi':
                $rekamMedisController->daftarAtensi();
                break;
            case 'template_tatalaksana':
                $rekamMedisController->template_tatalaksana();
                break;
            case 'simpan_template_tatalaksana':
                $rekamMedisController->simpan_template_tatalaksana();
                break;
            case 'edit_template_form':
                $rekamMedisController->edit_template_form();
                break;
            case 'update_template_tatalaksana':
                $rekamMedisController->update_template_tatalaksana();
                break;
            case 'hapus_template_tatalaksana':
                $rekamMedisController->hapus_template_tatalaksana();
                break;
            case 'get_template_tatalaksana':
                $rekamMedisController->get_template_tatalaksana();
                break;
            case 'template_usg':
                $rekamMedisController->template_usg();
                break;
            case 'simpan_template_usg':
                $rekamMedisController->simpan_template_usg();
                break;
            case 'edit_template_usg_form':
                $rekamMedisController->edit_template_usg_form();
                break;
            case 'update_template_usg':
                $rekamMedisController->update_template_usg();
                break;
            case 'hapus_template_usg':
                $rekamMedisController->hapus_template_usg();
                break;
            case 'get_template_usg':
                $rekamMedisController->get_template_usg();
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
            case 'hapus_status_ginekologi':
                $rekamMedisController->hapus_status_ginekologi();
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
            case 'hapus_status_ginekologi':
                $rekamMedisController->hapus_status_ginekologi();
                break;
            case 'generate_pdf':
                error_log("Routing to generate_pdf");
                $rekamMedisController->generate_pdf();
                break;
            case 'generate_status_obstetri_pdf':
                error_log("Routing to generate_status_obstetri_pdf");
                $rekamMedisController->generate_status_obstetri_pdf();
                break;
            case 'generate_status_ginekologi_pdf':
                error_log("Routing to generate_status_ginekologi_pdf");
                $rekamMedisController->generate_status_ginekologi_pdf();
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
