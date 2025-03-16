<?php
require_once __DIR__ . '/../config/config.php';
$current_page = basename($_SERVER['PHP_SELF']);
$current_path = $_SERVER['PHP_SELF'];
$is_logged_in = isset($_SESSION['user_id']); // Akan digunakan nanti
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; // Akan digunakan nanti

// Fungsi untuk membersihkan base_url
function clean_url($url)
{
    return str_replace(' ', '%20', $url);
}

// Fungsi untuk memeriksa apakah halaman saat ini adalah halaman yang ditentukan
function is_current_page($page_path)
{
    return strpos($_SERVER['PHP_SELF'], $page_path) !== false;
}

// Fungsi untuk memeriksa apakah halaman saat ini adalah halaman dengan parameter GET tertentu
function is_current_module($module, $action = null)
{
    if (!isset($_GET['module']) || $_GET['module'] != $module) {
        return false;
    }

    if ($action !== null) {
        return isset($_GET['action']) && $_GET['action'] == $action;
    }

    return true;
}
?>

<!-- Add Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* Variabel warna untuk konsistensi */
    :root {
        --primary-color: rgb(255, 65, 132);
        --primary-light: #e8f5f3;
        --primary-dark: rgb(188, 34, 96);
        --text-dark: #333;
        --text-muted: #666;
        --bg-light: #f5f5f7;
        --border-light: rgba(0, 0, 0, 0.05);
        --hover-bg: rgba(0, 0, 0, 0.05);

        /* Warna untuk tombol aksi */
        --add-color: #28a745;
        --add-hover: #218838;
        --edit-color: #ffc107;
        --edit-hover: #e0a800;
        --delete-color: #dc3545;
        --delete-hover: #c82333;
        --download-color: #0d6efd;
        --download-hover: #0a58ca;
    }

    body {
        overflow-x: hidden;
        transition: padding-left 0.3s ease;
    }

    body.sidebar-open {
        overflow: hidden;
    }

    .sidebar {
        width: 240px;
        min-height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1050;
        background-color: var(--bg-light);
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }

    /* Base styles */
    .nav-link {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
        color: var(--text-dark);
        text-decoration: none;
        gap: 0.5rem;
        border-radius: 0;
        white-space: nowrap;
        transition: all 0.2s ease;
        margin: 0;
        font-weight: 400;
        border-left: 3px solid transparent;
    }

    .nav-link i {
        font-size: 1.1rem;
        min-width: 1.5rem;
        text-align: center;
        transition: all 0.2s ease;
        color: var(--text-muted);
    }

    .nav-link:hover {
        background-color: var(--hover-bg);
        color: var(--text-dark);
        transform: none;
        border-left: 3px solid var(--primary-color);
    }

    .nav-link:hover i {
        color: var(--primary-color);
    }

    .nav-link.active {
        background-color: var(--primary-light);
        color: var(--primary-color) !important;
        box-shadow: none;
        border-left: 3px solid var(--primary-color);
        font-weight: 500;
    }

    .nav-link.active i {
        color: var(--primary-color);
    }

    /* Submenu styles */
    .submenu {
        padding-left: 1.5rem;
        list-style: none;
        margin: 0;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background-color: transparent;
    }

    .submenu .nav-link {
        padding: 0.4rem 1rem;
        font-size: 0.875rem;
        margin: 0;
        color: var(--text-muted);
    }

    .submenu .nav-link.active {
        background-color: var(--primary-light);
        color: var(--primary-color) !important;
        border-left: 3px solid var(--primary-color);
        font-weight: 500;
    }

    .submenu .nav-link.active i {
        color: var(--primary-color);
    }

    /* Highlight parent menu when submenu is active */
    .has-submenu.open>.nav-link {
        color: var(--primary-color);
        font-weight: 500;
    }

    .has-submenu.open>.nav-link i {
        color: var(--primary-color);
    }

    .has-submenu.open .submenu-arrow {
        transform: rotate(90deg);
        color: var(--primary-color);
    }

    .submenu-toggle {
        cursor: pointer;
        position: relative;
    }

    .submenu-arrow {
        transition: transform 0.3s ease;
        font-size: 0.75rem;
        position: absolute;
        right: 0.75rem;
        color: var(--text-muted);
    }

    /* Minimized state */
    .sidebar.minimized {
        width: 60px;
    }

    .sidebar.minimized .menu-text,
    .sidebar.minimized .submenu-arrow,
    .sidebar.minimized hr {
        display: none;
    }

    /* Search box styling */
    .search-container {
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .search-input {
        position: relative;
        width: 100%;
    }

    .search-input input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border-radius: 0.5rem;
        border: 1px solid rgba(0, 0, 0, 0.1);
        background-color: #fff;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .search-input input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(232, 62, 140, 0.1);
    }

    .search-input i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 1rem;
    }

    /* Styling untuk search box saat minimized */
    .sidebar.minimized .search-container {
        padding: 0.75rem 0.5rem;
    }

    .sidebar.minimized .search-input input {
        width: 40px;
        height: 40px;
        padding: 0.5rem;
        border-radius: 50%;
        text-indent: -9999px;
        cursor: pointer;
        background-color: #f0f0f5;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .sidebar.minimized .search-input i {
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1rem;
        color: var(--text-muted);
    }

    /* Hover effect untuk search box */
    .sidebar.minimized .search-input:hover input {
        background-color: var(--primary-light);
        border-color: var(--primary-color);
    }

    .sidebar.minimized .search-input:hover i {
        color: var(--primary-color);
    }

    /* Styling untuk search box saat active/focus */
    .sidebar.minimized .search-input input:focus {
        width: 180px;
        border-radius: 20px;
        padding: 0.5rem 1rem 0.5rem 2.5rem;
        text-indent: 0;
        position: absolute;
        left: 60px;
        background-color: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1070;
    }

    .sidebar.minimized .search-input input:focus+i {
        left: 70px;
        transform: translateY(-50%);
        color: var(--primary-color);
    }

    /* Submenu styling */
    .sidebar.minimized .has-submenu:hover .submenu {
        display: block;
        position: absolute;
        left: 60px;
        top: 0;
        width: 200px;
        padding: 0.5rem;
        background: #fff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 0 0.5rem 0.5rem 0;
        z-index: 1060;
        margin-left: 0;
        border-left: 1px solid rgba(0, 0, 0, 0.05);
    }

    .sidebar.minimized .has-submenu {
        position: relative;
    }

    .sidebar.minimized .has-submenu:hover .submenu .nav-link {
        padding: 0.5rem 1rem;
        margin: 0.25rem 0;
        border-radius: 0.25rem;
    }

    .sidebar.minimized .has-submenu:hover .submenu .menu-text {
        display: inline;
    }

    /* Tambahkan panah kecil di sebelah kiri submenu */
    .sidebar.minimized .has-submenu:hover::after {
        content: '';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        border-top: 6px solid transparent;
        border-bottom: 6px solid transparent;
        border-right: 6px solid #fff;
        z-index: 1061;
    }

    /* Styling untuk submenu item saat hover */
    .sidebar.minimized .has-submenu:hover .submenu .nav-link:hover {
        background-color: var(--primary-light);
        color: var(--primary-color);
        border-left: 3px solid var(--primary-color);
    }

    /* Styling untuk submenu item yang aktif */
    .sidebar.minimized .has-submenu:hover .submenu .nav-link.active {
        background-color: var(--primary-light);
        color: var(--primary-color) !important;
        border-left: 3px solid var(--primary-color);
    }

    /* Main content adjustment */
    .main-content {
        margin-left: 240px;
        transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 1rem;
    }

    .sidebar.minimized+.main-content {
        margin-left: 60px;
    }

    /* Header styling */
    .sidebar .d-flex.justify-content-between {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    /* Category headers */
    .category-header {
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        padding: 0.75rem 1rem 0.25rem;
        letter-spacing: 0.5px;
    }

    /* Mobile Responsive */
    @media (max-width: 991.98px) {
        body {
            padding-left: 0 !important;
        }

        .sidebar {
            width: 240px;
            height: 100vh;
            min-height: 100vh;
            position: fixed;
            margin-bottom: 0;
            transform: translateX(0);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            max-height: 100vh;
            top: 0;
            left: 0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .sidebar.mobile-collapsed {
            transform: translateX(-100%);
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
            transition: opacity 0.3s ease;
            opacity: 0;
            backdrop-filter: blur(4px);
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        .main-content {
            margin-left: 0 !important;
            width: 100%;
            transition: margin-left 0.3s ease;
        }

        /* Mobile toggle button that stays fixed */
        .mobile-toggle-container {
            position: fixed;
            bottom: 1rem;
            left: 1rem;
            z-index: 1030;
            display: none;
            transition: all 0.3s ease;
        }

        .mobile-toggle-container.show {
            display: block;
        }

        .mobile-toggle-container .btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            background-color: var(--primary-color);
            border: none;
            color: white;
        }

        .mobile-toggle-container .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .nav-link {
            padding: 0.5rem 1rem;
            margin: 0;
        }

        .submenu {
            padding-left: 1.5rem;
            background: transparent;
            border-radius: 0;
            margin: 0;
        }

        .submenu .nav-link {
            padding: 0.4rem 1rem;
            margin: 0;
        }

        /* Hide minimize button on mobile */
        #toggleSidebar {
            display: none;
        }

        /* Adjust dropdown positioning */
        .dropdown-menu {
            position: static !important;
            float: none;
            width: auto;
            margin-top: 0.5rem;
            background-color: transparent;
            border: none;
            box-shadow: none;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            color: var(--text-muted);
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: var(--hover-bg);
            color: var(--primary-color);
        }
    }

    /* Small mobile devices */
    @media (max-width: 575.98px) {
        .nav-link {
            padding: 0.4rem 0.75rem;
        }

        .submenu {
            padding-left: 1.25rem;
        }
    }

    /* Fix submenu hover conflicts */
    .submenu.show {
        display: block !important;
    }

    .sidebar.minimized .submenu {
        display: none;
        background: var(--bg-light);
    }

    .sidebar.minimized .has-submenu:hover>.submenu {
        display: block;
    }

    /* Additional alignment fixes */
    .nav-item {
        margin: 0;
    }

    .dropdown-toggle::after {
        margin-left: auto;
    }

    .dropdown-menu {
        min-width: 200px;
    }

    /* Custom scrollbar */
    .sidebar::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: var(--text-muted);
        border-radius: 2px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: var(--text-dark);
    }

    /* User profile section */
    .user-section {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: var(--bg-light);
        border-top: 1px solid var(--border-light);
        padding: 0.75rem 0;
        z-index: 1051;
    }

    .sidebar.minimized .user-section {
        padding: 0.5rem 0;
    }

    .sidebar.minimized .user-section .menu-text,
    .sidebar.minimized .user-section .dropdown-toggle::after {
        display: none;
    }

    .sidebar.minimized .user-section .dropdown-menu {
        position: absolute !important;
        left: 60px !important;
        bottom: 60px;
        width: 200px;
        background-color: white;
        border: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 0.5rem;
        padding: 0.5rem 0;
    }

    .user-section .dropdown {
        padding: 0;
    }

    .user-section .dropdown-toggle {
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-dark);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .user-section .dropdown-toggle:hover {
        background-color: var(--hover-bg);
    }

    .user-section .dropdown-toggle i {
        font-size: 1.25rem;
        color: var(--text-muted);
    }

    .user-section .dropdown-toggle:hover i {
        color: var(--primary-color);
    }

    .user-section .dropdown-menu {
        margin-bottom: 0.5rem;
    }

    .user-section .dropdown-item {
        padding: 0.5rem 1rem;
        color: var(--text-muted);
        transition: all 0.2s ease;
    }

    .user-section .dropdown-item:hover {
        background-color: var(--hover-bg);
        color: var(--primary-color);
    }

    /* Adjust main content to not overlap with user section */
    .nav-pills {
        margin-bottom: 60px;
    }

    /* Mobile adjustments for user section */
    @media (max-width: 991.98px) {
        .user-section {
            position: relative;
            margin-top: auto;
        }

        .nav-pills {
            margin-bottom: 0;
        }
    }

    /* Change button color in login section */
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    /* Specific style for active menu with blue background */
    .nav-link.active-blue {
        background-color: var(--primary-color);
        color: white !important;
        border-left: 3px solid var(--primary-color);
    }

    .nav-link.active-blue i {
        color: white;
    }

    /* Override any bootstrap active classes */
    .nav-pills .nav-link.active,
    .nav-pills .show>.nav-link {
        background-color: var(--primary-light);
        color: var(--primary-color) !important;
        border-left: 3px solid var(--primary-color);
    }

    /* Override for any remaining blue colors */
    .text-primary,
    .text-info,
    .text-primary i,
    .text-info i {
        color: var(--primary-color) !important;
    }

    .bg-primary,
    .bg-info {
        background-color: var(--primary-color) !important;
    }

    .border-primary,
    .border-info {
        border-color: var(--primary-color) !important;
    }

    /* Ensure all buttons use the blue-green color */
    .btn-primary,
    .btn-info {
        background-color: #2a9d8f !important;
        border-color: #2a9d8f !important;
    }

    .btn-outline-primary,
    .btn-outline-info {
        color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }

    .btn-outline-primary:hover,
    .btn-outline-info:hover {
        background-color: var(--primary-color) !important;
        color: white !important;
    }

    /* Styling untuk tombol aksi */
    .btn-add {
        background-color: var(--add-color);
        border-color: var(--add-color);
        color: white;
    }

    .btn-add:hover {
        background-color: var(--add-hover);
        border-color: var(--add-hover);
        color: white;
    }

    .btn-edit {
        background-color: var(--edit-color);
        border-color: var(--edit-color);
        color: #000;
    }

    .btn-edit:hover {
        background-color: var(--edit-hover);
        border-color: var(--edit-hover);
        color: #000;
    }

    .btn-delete {
        background-color: var(--delete-color);
        border-color: var(--delete-color);
        color: white;
    }

    .btn-delete:hover {
        background-color: var(--delete-hover);
        border-color: var(--delete-hover);
        color: white;
    }

    .btn-download {
        background-color: var(--download-color);
        border-color: var(--download-color);
        color: white;
    }

    .btn-download:hover {
        background-color: var(--download-hover);
        border-color: var(--download-hover);
        color: white;
    }

    /* Outline versions */
    .btn-outline-add {
        color: var(--add-color);
        border-color: var(--add-color);
        background-color: transparent;
    }

    .btn-outline-add:hover {
        color: white;
        background-color: var(--add-color);
        border-color: var(--add-color);
    }

    .btn-outline-edit {
        color: var(--edit-color);
        border-color: var(--edit-color);
        background-color: transparent;
    }

    .btn-outline-edit:hover {
        color: #000;
        background-color: var(--edit-color);
        border-color: var(--edit-color);
    }

    .btn-outline-delete {
        color: var(--delete-color);
        border-color: var(--delete-color);
        background-color: transparent;
    }

    .btn-outline-delete:hover {
        color: white;
        background-color: var(--delete-color);
        border-color: var(--delete-color);
    }

    .btn-outline-download {
        color: var(--download-color);
        border-color: var(--download-color);
        background-color: transparent;
    }

    .btn-outline-download:hover {
        color: white;
        background-color: var(--download-color);
        border-color: var(--download-color);
    }

    /* Icon colors */
    .text-add {
        color: var(--add-color) !important;
    }

    .text-edit {
        color: var(--edit-color) !important;
    }

    .text-delete {
        color: var(--delete-color) !important;
    }

    .text-download {
        color: var(--download-color) !important;
    }

    /* Small size buttons */
    .btn-sm.btn-add,
    .btn-sm.btn-edit,
    .btn-sm.btn-delete,
    .btn-sm.btn-download {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }

    /* Button groups */
    .btn-group .btn-add:not(:first-child),
    .btn-group .btn-edit:not(:first-child),
    .btn-group .btn-delete:not(:first-child),
    .btn-group .btn-download:not(:first-child) {
        margin-left: -1px;
    }

    /* Disabled state */
    .btn-add:disabled,
    .btn-edit:disabled,
    .btn-delete:disabled,
    .btn-download:disabled {
        opacity: 0.65;
        pointer-events: none;
    }
</style>

<div id="sidebar" class="sidebar">
    <div class="d-flex flex-column flex-shrink-0 h-100">
        <div class="d-flex justify-content-between align-items-center py-3 px-3">
            <a href="<?php echo clean_url($base_url); ?>" class="d-flex align-items-center text-decoration-none">
                <span class="fs-5 fw-semibold text-dark menu-text">Praktek Obgin</span>
            </a>
            <?php if ($is_admin): ?>
                <button id="toggleSidebar" class="btn btn-sm btn-light border d-none d-lg-block">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button id="toggleMobileSidebar" class="btn btn-sm btn-light border d-lg-none">
                    <i class="bi bi-chevron-left"></i>
                </button>
            <?php endif; ?>
        </div>

        <div class="search-container">
            <div class="search-input">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Search..." class="form-control">
            </div>
        </div>

        <ul class="nav nav-pills flex-column">
            <?php if ($is_admin): ?>
                <!-- Menu untuk Admin -->
                <li class="nav-item">
                    <a href="<?php echo clean_url($base_url); ?>/dashboard.php" class="nav-link <?php echo is_current_page('/dashboard.php') ? 'active' : ''; ?>">
                        <i class="bi bi-grid"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <i class="bi bi-hospital-fill"></i>
                        <span class="menu-text">Rawat Inap</span>
                        <i class="bi bi-chevron-right ms-auto submenu-arrow"></i>
                    </a>
                    <ul class="submenu collapse">
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/daftar_ranap.php" class="nav-link <?php echo is_current_page('/daftar_ranap.php') ? 'active' : ''; ?>">
                                <i class="bi bi-list-ul"></i>
                                <span class="menu-text">Daftar Pasien</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <i class="bi bi-person-walking"></i>
                        <span class="menu-text">Rawat Jalan</span>
                        <i class="bi bi-chevron-right ms-auto submenu-arrow"></i>
                    </a>
                    <ul class="submenu collapse">
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/daftar_rajal_rs.php" class="nav-link <?php echo is_current_page('/daftar_rajal_rs.php') ? 'active' : ''; ?>">
                                <i class="bi bi-list-check"></i>
                                <span class="menu-text">Daftar Rajal RS</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Menu Rekam Medis -->
                <li class="nav-item has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <i class="bi bi-journal-medical"></i>
                        <span class="menu-text">Rekam Medis</span>
                        <i class="bi bi-chevron-right ms-auto submenu-arrow"></i>
                    </a>
                    <ul class="submenu collapse">
                        <li class="nav-item">
                            <a href="<?php echo clean_url($base_url); ?>/index.php?module=rekam_medis&action=manajemen_antrian"
                                class="nav-link <?php echo is_current_module('rekam_medis', 'manajemen_antrian') ? 'active' : ''; ?>">
                                <i class="bi bi-people"></i>
                                <span class="menu-text">Pasien Rawat Jalan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo clean_url($base_url); ?>/index.php?module=rekam_medis&action=data_pasien"
                                class="nav-link <?php echo is_current_module('rekam_medis', 'data_pasien') ? 'active' : ''; ?>">
                                <i class="bi bi-person-vcard"></i>
                                <span class="menu-text">Data Pasien</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo clean_url($base_url); ?>/index.php?module=rekam_medis&action=daftar_atensi"
                                class="nav-link <?php echo is_current_module('rekam_medis', 'daftar_atensi') ? 'active' : ''; ?>">
                                <i class="bi bi-exclamation-circle"></i>
                                <span class="menu-text">Daftar Atensi</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo clean_url($base_url); ?>/index.php?module=rekam_medis&action=template_tatalaksana"
                                class="nav-link <?php echo is_current_module('rekam_medis', 'template_tatalaksana') ? 'active' : ''; ?>">
                                <i class="bi bi-file-text"></i>
                                <span class="menu-text">Template Tatalaksana</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo clean_url($base_url); ?>/index.php?module=rekam_medis&action=template_usg"
                                class="nav-link <?php echo is_current_module('rekam_medis', 'template_usg') ? 'active' : ''; ?>">
                                <i class="bi bi-image"></i>
                                <span class="menu-text">Template USG</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <i class="bi bi-gear"></i>
                        <span class="menu-text">Admin Praktek</span>
                        <i class="bi bi-chevron-right ms-auto submenu-arrow"></i>
                    </a>
                    <ul class="submenu collapse">
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/admin_praktek/data_dokter.php" class="nav-link <?php echo is_current_page('/admin_praktek/data_dokter.php') ? 'active' : ''; ?>">
                                <i class="bi bi-person-vcard"></i>
                                <span class="menu-text">Data Dokter</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/admin_praktek/tempat_praktek.php" class="nav-link <?php echo is_current_page('/admin_praktek/tempat_praktek.php') ? 'active' : ''; ?>">
                                <i class="bi bi-building"></i>
                                <span class="menu-text">Tempat Praktek</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/admin_praktek/jadwal_rutin.php" class="nav-link <?php echo is_current_page('/admin_praktek/jadwal_rutin.php') ? 'active' : ''; ?>">
                                <i class="bi bi-calendar-week"></i>
                                <span class="menu-text">Jadwal Rutin</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/admin_praktek/pengumuman.php" class="nav-link <?php echo is_current_page('/admin_praktek/pengumuman.php') ? 'active' : ''; ?>">
                                <i class="bi bi-megaphone"></i>
                                <span class="menu-text">Pesan / Pengumuman</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/admin_praktek/manajemen_user.php" class="nav-link <?php echo is_current_page('/admin_praktek/manajemen_user.php') ? 'active' : ''; ?>">
                                <i class="bi bi-person-gear"></i>
                                <span class="menu-text">Manajemen User</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/admin_praktek/manajemen_antrian.php" class="nav-link <?php echo is_current_page('/admin_praktek/manajemen_antrian.php') ? 'active' : ''; ?>">
                                <i class="bi bi-list-check"></i>
                                <span>Manajemen Antrian</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/admin_praktek/data_rujukan.php" class="nav-link <?php echo is_current_page('/admin_praktek/data_rujukan.php') ? 'active' : ''; ?>">
                                <i class="bi bi-file-earmark-medical"></i>
                                <span class="menu-text">Data Rujukan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/admin_praktek/manajemen_layanan.php" class="nav-link <?php echo is_current_page('/admin_praktek/manajemen_layanan.php') ? 'active' : ''; ?>">
                                <i class="bi bi-gear-wide-connected"></i>
                                <span class="menu-text">Manajemen Layanan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/admin_praktek/formularium.php" class="nav-link <?php echo is_current_page('/admin_praktek/formularium.php') ? 'active' : ''; ?>">
                                <i class="bi bi-capsule"></i>
                                <span class="menu-text">Formularium</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/admin_praktek/manajemen_edukasi.php" class="nav-link <?php echo is_current_page('/admin_praktek/manajemen_edukasi.php') ? 'active' : ''; ?>">
                                <i class="bi bi-journal-text"></i>
                                <span class="menu-text">Manajemen Edukasi</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>/admin_praktek/dashboard_antrian.php" class="nav-link <?php echo is_current_page('/admin_praktek/dashboard_antrian.php') ? 'active' : ''; ?>">
                                <i class="bi bi-display"></i>
                                <span class="menu-text">Dashboard Antrian</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Menu Pendaftaran - Selalu Tampil -->
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link submenu-toggle">
                    <i class="bi bi-journal-plus"></i>
                    <span class="menu-text">Pendaftaran</span>
                    <i class="bi bi-chevron-right ms-auto submenu-arrow"></i>
                </a>
                <ul class="submenu collapse">
                    <li class="nav-item">
                        <a href="<?php echo clean_url($base_url); ?>/pendaftaran/form_pendaftaran_pasien.php" class="nav-link <?php echo is_current_page('/pendaftaran/form_pendaftaran_pasien.php') ? 'active' : ''; ?>">
                            <i class="bi bi-file-earmark-text"></i>
                            <span class="menu-text">Form Pendaftaran</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo clean_url($base_url); ?>/pendaftaran/antrian.php" class="nav-link <?php echo is_current_page('/pendaftaran/antrian.php') ? 'active' : ''; ?>">
                            <i class="bi bi-list-ol"></i>
                            <span class="menu-text">Daftar Antrian</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo clean_url($base_url); ?>/pendaftaran/jadwal.php" class="nav-link <?php echo is_current_page('/pendaftaran/jadwal.php') ? 'active' : ''; ?>">
                            <i class="bi bi-calendar2-week"></i>
                            <span class="menu-text">Jadwal Praktek</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Menu Pengumuman - Selalu Tampil -->
            <li class="nav-item">
                <a href="<?php echo $base_url; ?>/pengumuman.php" class="nav-link <?php echo is_current_page('/pengumuman.php') ? 'active' : ''; ?>">
                    <i class="bi bi-megaphone"></i>
                    <span class="menu-text">Pengumuman</span>
                </a>
            </li>

            <!-- Menu Layanan - Selalu Tampil -->
            <li class="nav-item">
                <a href="<?php echo clean_url($base_url); ?>/layanan.php" class="nav-link <?php echo is_current_page('/layanan.php') ? 'active' : ''; ?>">
                    <i class="bi bi-heart-pulse"></i>
                    <span class="menu-text">Layanan</span>
                </a>
            </li>

            <!-- Menu Edukasi - Selalu Tampil -->
            <li class="nav-item">
                <a href="<?php echo clean_url($base_url); ?>/edukasi.php" class="nav-link <?php echo is_current_page('/edukasi.php') ? 'active' : ''; ?>">
                    <i class="bi bi-journal-text"></i>
                    <span class="menu-text">Edukasi</span>
                </a>
            </li>
        </ul>

        <!-- User section at bottom -->
        <div class="user-section">
            <?php if ($is_logged_in): ?>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <span class="menu-text"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                    </a>
                    <ul class="dropdown-menu shadow" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?= $base_url ?>/login.php" class="btn btn-primary w-100 mx-3">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span class="menu-text ms-2">Login</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        const toggleMobileBtn = document.getElementById('toggleMobileSidebar');
        const isMobile = window.innerWidth < 992;

        // Fungsi untuk menyimpan status menu di localStorage
        function saveMenuState() {
            const openMenus = [];
            document.querySelectorAll('.has-submenu.open').forEach(menu => {
                openMenus.push(menu.querySelector('.submenu-toggle').textContent.trim());
            });
            localStorage.setItem('openMenus', JSON.stringify(openMenus));
        }

        // Fungsi untuk memulihkan status menu dari localStorage
        function restoreMenuState() {
            try {
                // Pertama, buka submenu yang memiliki item aktif
                const activeMenuItems = document.querySelectorAll('.submenu .nav-link.active');
                activeMenuItems.forEach(activeItem => {
                    const parentSubmenu = activeItem.closest('.submenu');
                    if (parentSubmenu) {
                        const parentItem = parentSubmenu.closest('.has-submenu');
                        if (parentItem) {
                            parentItem.classList.add('open');
                            parentSubmenu.classList.add('show');
                        }
                    }
                });

                // Kemudian, pulihkan menu yang sebelumnya terbuka dari localStorage
                const openMenus = JSON.parse(localStorage.getItem('openMenus')) || [];
                if (openMenus.length > 0) {
                    document.querySelectorAll('.submenu-toggle').forEach(toggle => {
                        const menuText = toggle.textContent.trim();
                        if (openMenus.includes(menuText)) {
                            const parent = toggle.closest('.has-submenu');
                            const submenu = parent.querySelector('.submenu');
                            parent.classList.add('open');
                            submenu.classList.add('show');
                        }
                    });
                }
            } catch (e) {
                console.error('Error restoring menu state:', e);
            }
        }

        // Fungsi untuk menambahkan class pada body saat sidebar terbuka
        function updateBodyClass() {
            if (sidebar.classList.contains('mobile-collapsed')) {
                document.body.classList.remove('sidebar-open');
            } else {
                document.body.classList.add('sidebar-open');
            }
        }

        // Create overlay for mobile
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);

        // Create mobile toggle button container
        const mobileToggleContainer = document.createElement('div');
        mobileToggleContainer.className = 'mobile-toggle-container';
        const mobileToggleBtn = document.createElement('button');
        mobileToggleBtn.className = 'btn btn-primary';
        mobileToggleBtn.innerHTML = '<i class="bi bi-grid-fill"></i>';
        mobileToggleContainer.appendChild(mobileToggleBtn);
        document.body.appendChild(mobileToggleContainer);

        // Auto collapse on mobile
        if (isMobile) {
            sidebar.classList.add('mobile-collapsed');
            mobileToggleContainer.classList.add('show');
            updateBodyClass();
        }

        // Restore menu state
        restoreMenuState();

        // Toggle sidebar on desktop
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                if (!isMobile) {
                    sidebar.classList.toggle('minimized');
                    // Simpan status minimized di localStorage
                    localStorage.setItem('sidebarMinimized', sidebar.classList.contains('minimized'));
                }
            });
        }

        // Restore minimized state
        if (!isMobile && localStorage.getItem('sidebarMinimized') === 'true') {
            sidebar.classList.add('minimized');
        }

        // Toggle sidebar on mobile
        if (toggleMobileBtn) {
            toggleMobileBtn.addEventListener('click', function() {
                if (isMobile) {
                    sidebar.classList.toggle('mobile-collapsed');
                    overlay.classList.toggle('show');
                    mobileToggleContainer.classList.toggle('show');
                    updateBodyClass();
                }
            });
        }

        // Mobile toggle button in fixed position
        mobileToggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-collapsed');
            overlay.classList.toggle('show');
            mobileToggleContainer.classList.toggle('show');
            updateBodyClass();
        });

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            // Pada mobile, kita perlu menutup sidebar saat overlay diklik
            if (isMobile) {
                sidebar.classList.add('mobile-collapsed');
                updateBodyClass();
            }
            overlay.classList.remove('show');
            mobileToggleContainer.classList.add('show');
        });

        // Jangan collapse sidebar saat menu item diklik di mobile
        if (isMobile) {
            const menuLinks = sidebar.querySelectorAll('a.nav-link:not(.submenu-toggle):not([href="#"])');
            menuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Pada mobile, kita tidak perlu menutup sidebar saat menu diklik
                    // Ini memungkinkan pengguna untuk melihat menu yang aktif
                });
            });
        }

        // Handle submenu toggles
        document.querySelectorAll('.submenu-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.closest('.has-submenu');
                const submenu = parent.querySelector('.submenu');

                // Jangan tutup submenu yang sudah terbuka
                if (isMobile || !sidebar.classList.contains('minimized')) {
                    parent.classList.toggle('open');
                    submenu.classList.toggle('show');

                    // Simpan status menu
                    saveMenuState();
                }
            });
        });

        // Buka submenu yang memiliki item aktif saat halaman dimuat
        document.querySelectorAll('.submenu .nav-link.active').forEach(activeLink => {
            const parentSubmenu = activeLink.closest('.submenu');
            if (parentSubmenu) {
                parentSubmenu.classList.add('show');
                const parentItem = parentSubmenu.closest('.has-submenu');
                if (parentItem) {
                    parentItem.classList.add('open');
                }
            }
        });

        // Handle hover states for minimized mode
        let currentOpenSubmenu = null;

        document.querySelectorAll('.has-submenu').forEach(item => {
            item.addEventListener('mouseenter', () => {
                if (!isMobile && sidebar.classList.contains('minimized')) {
                    if (currentOpenSubmenu && currentOpenSubmenu !== item) {
                        currentOpenSubmenu.querySelector('.submenu').classList.remove('show');
                    }
                    const submenu = item.querySelector('.submenu');
                    submenu.classList.add('show');
                    currentOpenSubmenu = item;
                }
            });

            item.addEventListener('mouseleave', () => {
                if (!isMobile && sidebar.classList.contains('minimized')) {
                    const submenu = item.querySelector('.submenu');
                    setTimeout(() => {
                        if (!item.matches(':hover')) {
                            submenu.classList.remove('show');
                            if (currentOpenSubmenu === item) {
                                currentOpenSubmenu = null;
                            }
                        }
                    }, 100);
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const newIsMobile = window.innerWidth < 992;

            // Hanya reload jika berubah dari mobile ke desktop atau sebaliknya
            if (newIsMobile !== isMobile) {
                location.reload();
            }

            // Jika dalam mode mobile dan sidebar terbuka, tutup sidebar
            if (newIsMobile && !sidebar.classList.contains('mobile-collapsed')) {
                sidebar.classList.add('mobile-collapsed');
                overlay.classList.remove('show');
                mobileToggleContainer.classList.add('show');
                updateBodyClass();
            }
        });

        // Fungsi pencarian menu
        const searchInput = document.querySelector('.search-input input');
        const menuItems = document.querySelectorAll('.nav-link');
        const menuParents = document.querySelectorAll('.has-submenu');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();

            // Reset semua menu dan submenu
            menuItems.forEach(item => {
                item.style.display = 'flex';
                const parent = item.closest('.nav-item');
                if (parent) {
                    parent.style.display = 'block';
                }
            });

            menuParents.forEach(parent => {
                parent.style.display = 'block';
                const submenu = parent.querySelector('.submenu');
                if (submenu) {
                    submenu.classList.remove('show');
                    parent.classList.remove('open');
                }
            });

            if (searchTerm !== '') {
                // Sembunyikan semua menu terlebih dahulu
                menuItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    const isSubmenuToggle = item.classList.contains('submenu-toggle');
                    const parent = item.closest('.nav-item');

                    if (!text.includes(searchTerm)) {
                        if (!isSubmenuToggle) {
                            if (parent) {
                                parent.style.display = 'none';
                            }
                        }
                    } else {
                        // Jika menu item ditemukan, tampilkan parent dan buka submenu jika ada
                        if (parent) {
                            parent.style.display = 'block';
                            const parentSubmenu = parent.closest('.submenu');
                            if (parentSubmenu) {
                                parentSubmenu.classList.add('show');
                                const parentItem = parentSubmenu.closest('.has-submenu');
                                if (parentItem) {
                                    parentItem.classList.add('open');
                                    parentItem.style.display = 'block';
                                }
                            }
                        }
                    }
                });

                // Periksa submenu yang memiliki item yang cocok
                menuParents.forEach(parent => {
                    const submenu = parent.querySelector('.submenu');
                    if (submenu) {
                        const hasVisibleChild = Array.from(submenu.querySelectorAll('.nav-item')).some(
                            item => item.style.display !== 'none'
                        );

                        if (hasVisibleChild) {
                            parent.style.display = 'block';
                            submenu.classList.add('show');
                            parent.classList.add('open');
                        } else {
                            const toggleText = parent.querySelector('.submenu-toggle').textContent.toLowerCase();
                            if (!toggleText.includes(searchTerm)) {
                                parent.style.display = 'none';
                            }
                        }
                    }
                });
            }
        });

        // Reset pencarian saat input dikosongkan
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                this.dispatchEvent(new Event('input'));
            }
        });
    });
</script>