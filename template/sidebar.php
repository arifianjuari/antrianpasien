<?php
require_once __DIR__ . '/../config/config.php';
$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = isset($_SESSION['user_id']); // Akan digunakan nanti
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; // Akan digunakan nanti

// Fungsi untuk membersihkan base_url
function clean_url($url)
{
    return str_replace(' ', '%20', $url);
}
?>

<!-- Add Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div id="sidebar" class="sidebar bg-white border-end">
    <div class="d-flex flex-column flex-shrink-0 py-3">
        <?php if ($is_admin): ?>
            <div class="d-flex justify-content-end px-3 mb-2">
                <button id="toggleSidebar" class="btn btn-sm btn-light border">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>
        <?php endif; ?>
        <ul class="nav nav-pills flex-column mb-auto px-2">
            <?php if ($is_admin): ?>
                <!-- Menu untuk Admin -->
                <li class="nav-item mb-1">
                    <a href="<?php echo clean_url($base_url); ?>/dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : 'text-dark'; ?>">
                        <i class="bi bi-grid"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item has-submenu mb-1">
                    <a href="#" class="nav-link text-dark submenu-toggle">
                        <i class="bi bi-hospital-fill"></i>
                        <span class="menu-text">Rawat Inap</span>
                        <i class="bi bi-chevron-right ms-auto submenu-arrow"></i>
                    </a>
                    <ul class="submenu collapse">
                        <li>
                            <a href="<?php echo $base_url; ?>/daftar_ranap.php" class="nav-link <?php echo $current_page == 'daftar_ranap.php' ? 'active' : 'text-dark'; ?>">
                                <i class="bi bi-list-ul"></i>
                                <span class="menu-text">Daftar Pasien</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-submenu mb-1">
                    <a href="#" class="nav-link text-dark submenu-toggle">
                        <i class="bi bi-person-walking"></i>
                        <span class="menu-text">Rawat Jalan</span>
                        <i class="bi bi-chevron-right ms-auto submenu-arrow"></i>
                    </a>
                    <ul class="submenu collapse">
                        <li>
                            <a href="<?php echo $base_url; ?>/daftar_rajal_rs.php" class="nav-link <?php echo $current_page == 'daftar_rajal_rs.php' ? 'active' : 'text-dark'; ?>">
                                <i class="bi bi-list-check"></i>
                                <span class="menu-text">Daftar Rajal RS</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Menu Rekam Medis -->
                <li class="nav-item has-submenu mb-1">
                    <a href="#" class="nav-link text-dark submenu-toggle">
                        <i class="bi bi-journal-medical"></i>
                        <span class="menu-text">Rekam Medis</span>
                        <i class="bi bi-chevron-right ms-auto submenu-arrow"></i>
                    </a>
                    <ul class="submenu collapse">
                        <li>
                            <a href="<?php echo clean_url($base_url); ?>/index.php?module=rekam_medis&action=manajemen_antrian"
                                class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] == 'rekam_medis' && isset($_GET['action']) && $_GET['action'] == 'manajemen_antrian') ? 'active' : 'text-dark'; ?>">
                                <i class="bi bi-people"></i>
                                <span class="menu-text">Pasien Rawat Jalan</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo clean_url($base_url); ?>/index.php?module=rekam_medis&action=data_pasien"
                                class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] == 'rekam_medis' && isset($_GET['action']) && $_GET['action'] == 'data_pasien') ? 'active' : 'text-dark'; ?>">
                                <i class="bi bi-person-vcard"></i>
                                <span class="menu-text">Data Pasien</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-submenu mb-1">
                    <a href="#" class="nav-link text-dark submenu-toggle">
                        <i class="bi bi-gear"></i>
                        <span class="menu-text">Admin Praktek</span>
                        <i class="bi bi-chevron-right ms-auto submenu-arrow"></i>
                    </a>
                    <ul class="submenu collapse">
                        <li>
                            <a href="<?php echo $base_url; ?>/admin_praktek/data_pasien.php" class="nav-link text-dark">
                                <i class="bi bi-people"></i>
                                <span class="menu-text">Data Pasien</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>/admin_praktek/data_dokter.php" class="nav-link text-dark">
                                <i class="bi bi-person-vcard"></i>
                                <span class="menu-text">Data Dokter</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>/admin_praktek/tempat_praktek.php" class="nav-link text-dark">
                                <i class="bi bi-building"></i>
                                <span class="menu-text">Tempat Praktek</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>/admin_praktek/jadwal_rutin.php" class="nav-link <?php echo $current_page == 'jadwal_rutin.php' ? 'active' : 'text-dark'; ?>">
                                <i class="bi bi-calendar-week"></i>
                                <span class="menu-text">Jadwal Rutin</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>/admin_praktek/penjadwalan.php" class="nav-link <?php echo $current_page == 'penjadwalan.php' ? 'active' : 'text-dark'; ?>">
                                <i class="bi bi-calendar-check"></i>
                                <span class="menu-text">Penjadwalan</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>/admin_praktek/pengumuman.php" class="nav-link text-dark">
                                <i class="bi bi-megaphone"></i>
                                <span class="menu-text">Pesan / Pengumuman</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>/admin_praktek/manajemen_user.php" class="nav-link text-dark">
                                <i class="bi bi-people-gear"></i>
                                <span class="menu-text">Manajemen User</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>/admin_praktek/manajemen_antrian.php" class="nav-link <?php echo $current_page == 'manajemen_antrian.php' ? 'active' : '' ?>">
                                <i class="bi bi-list-check"></i>
                                <span>Manajemen Antrian</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Menu Pendaftaran - Selalu Tampil -->
            <li class="nav-item has-submenu mb-1">
                <a href="#" class="nav-link text-dark submenu-toggle">
                    <i class="bi bi-journal-plus"></i>
                    <span class="menu-text">Pendaftaran</span>
                    <i class="bi bi-chevron-right ms-auto submenu-arrow"></i>
                </a>
                <ul class="submenu collapse">
                    <li>
                        <a href="<?php echo clean_url($base_url); ?>/pendaftaran/form_pendaftaran_pasien.php" class="nav-link <?php echo $current_page == 'form_pendaftaran_pasien.php' ? 'active' : 'text-dark'; ?>">
                            <i class="bi bi-file-earmark-text"></i>
                            <span class="menu-text">Form Pendaftaran</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo clean_url($base_url); ?>/pendaftaran/antrian.php" class="nav-link <?php echo $current_page == 'antrian.php' ? 'active' : ''; ?>">
                            <i class="bi bi-list-ol"></i> Daftar Antrian
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo clean_url($base_url); ?>/pendaftaran/jadwal.php" class="nav-link <?php echo $current_page == 'jadwal.php' ? 'active' : 'text-dark'; ?>">
                            <i class="bi bi-calendar2-week"></i>
                            <span class="menu-text">Jadwal Praktek</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
        <hr class="mx-3 my-2">
        <div class="dropdown px-3">
            <?php if ($is_logged_in): ?>
                <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                    <span class="menu-text ms-2"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                </a>
                <ul class="dropdown-menu shadow" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
                </ul>
            <?php else: ?>
                <a href="<?= $base_url ?>/login.php" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span class="menu-text ms-2">Login</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .sidebar {
        width: 280px;
        min-height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        transition: all 0.3s ease;
        z-index: 1050;
        background-color: white;
    }

    /* Base styles */
    .nav-link {
        display: flex;
        align-items: center;
        padding: 0.625rem 1rem;
        color: #212529;
        text-decoration: none;
        gap: 0.75rem;
        border-radius: 0.375rem;
        white-space: nowrap;
    }

    .nav-link i {
        font-size: 1.1rem;
        min-width: 1.5rem;
        text-align: center;
    }

    .nav-link:hover {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .nav-link.active:not([href="<?php echo clean_url($base_url); ?>/dashboard.php"]) {
        background-color: #0d6efd;
        color: white !important;
    }

    .nav-link.active:not([href="<?php echo clean_url($base_url); ?>/dashboard.php"]) i {
        color: white;
    }

    /* Submenu styles */
    .submenu {
        padding-left: 2.25rem;
        list-style: none;
        margin: 0;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .submenu .nav-link {
        padding: 0.5rem 1rem;
        font-size: 0.9375rem;
    }

    .submenu-toggle {
        cursor: pointer;
    }

    .submenu-arrow {
        transition: transform 0.3s ease;
        font-size: 0.875rem;
    }

    .has-submenu.open .submenu-arrow {
        transform: rotate(90deg);
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

    .sidebar.minimized .has-submenu:hover .submenu {
        display: block;
        position: absolute;
        left: 100%;
        top: 0;
        width: 200px;
        padding: 0.5rem;
        background: white;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 0.375rem;
        z-index: 1060;
    }

    .sidebar.minimized .has-submenu:hover .submenu .nav-link {
        padding: 0.5rem 1rem;
    }

    .sidebar.minimized .has-submenu:hover .submenu .menu-text {
        display: inline;
    }

    /* Main content adjustment */
    .main-content {
        margin-left: 280px;
        transition: margin-left 0.3s ease;
        padding: 1rem;
    }

    .sidebar.minimized+.main-content {
        margin-left: 60px;
    }

    /* Mobile Responsive */
    @media (max-width: 991.98px) {
        .sidebar {
            width: 100%;
            height: auto;
            min-height: auto;
            position: relative;
            margin-bottom: 1rem;
        }

        .sidebar.minimized {
            width: 100%;
        }

        .main-content {
            margin-left: 0 !important;
        }

        .nav-link {
            padding: 0.75rem 1rem;
        }

        .submenu {
            padding-left: 1rem;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 0.375rem;
            margin: 0.5rem 0;
        }

        .submenu .nav-link {
            padding: 0.625rem 1rem;
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
        }

        /* Adjust submenu behavior for mobile */
        .sidebar.minimized .has-submenu:hover .submenu {
            position: static;
            width: 100%;
            box-shadow: none;
            padding-left: 1rem;
        }

        .sidebar.minimized .menu-text {
            display: inline;
        }

        .sidebar.minimized .submenu-arrow {
            display: block;
        }

        /* Stack menu items vertically on mobile */
        .nav-item {
            width: 100%;
        }

        /* Adjust spacing */
        .px-2,
        .px-3 {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        /* Make sure submenus are visible when open */
        .submenu.show {
            display: block !important;
            max-height: none;
        }
    }

    /* Small mobile devices */
    @media (max-width: 575.98px) {
        .nav-link {
            padding: 0.625rem 0.75rem;
        }

        .submenu {
            padding-left: 0.75rem;
        }

        .dropdown-menu {
            margin: 0;
            padding: 0.25rem 0;
        }
    }

    /* Fix submenu hover conflicts */
    .submenu.show {
        display: block !important;
    }

    .sidebar.minimized .submenu {
        display: none;
        background: white;
    }

    .sidebar.minimized .has-submenu:hover>.submenu {
        display: block;
    }

    /* Additional alignment fixes */
    .nav-item {
        margin: 0.125rem 0;
    }

    .submenu .nav-item:last-child {
        margin-bottom: 0.25rem;
    }

    .dropdown-toggle::after {
        margin-left: auto;
    }

    .dropdown-menu {
        min-width: 200px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        const isMobile = window.innerWidth < 992;

        // Toggle sidebar
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                if (!isMobile) {
                    sidebar.classList.toggle('minimized');
                }
            });
        }

        // Handle submenu toggles
        document.querySelectorAll('.submenu-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.closest('.has-submenu');
                const submenu = parent.querySelector('.submenu');

                if (isMobile || !sidebar.classList.contains('minimized')) {
                    parent.classList.toggle('open');
                    submenu.classList.toggle('show');
                }
            });
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
            if (newIsMobile !== isMobile) {
                location.reload();
            }
        });
    });
</script>