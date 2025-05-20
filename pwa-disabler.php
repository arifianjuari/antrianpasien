<?php
/**
 * PWA Disabler untuk Development Mode
 * 
 * File ini menonaktifkan fitur PWA saat di localhost untuk menghindari masalah
 * cache dan redirect ke offline.html selama pengembangan
 */

// Cek apakah mode development
if (!defined('IS_DEV_ENV')) {
    // Fallback jika dev-mode.php belum di-include
    $is_localhost = isset($_SERVER['HTTP_HOST']) && 
                   (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
                    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
    define('IS_DEV_ENV', $is_localhost);
}

// Tambahkan script untuk menghapus semua pendaftaran Service Worker
// dan mencegah offline behavior saat di localhost
if (IS_DEV_ENV):
?>
<script>
// Identifikasi localhost/development mode di client side
const isLocalhost = Boolean(
    window.location.hostname === 'localhost' ||
    window.location.hostname === '127.0.0.1' ||
    window.location.hostname.match(/^192\.168\./)
);

// Hanya di development mode (localhost)
if (isLocalhost) {
    console.log('Development mode detected - Disabling PWA features');
    
    // Hapus semua Service Worker yang terdaftar
    if ('serviceWorker' in navigator) {
        console.log('Unregistering all Service Workers...');
        navigator.serviceWorker.getRegistrations().then(registrations => {
            for (let registration of registrations) {
                console.log('Unregistering Service Worker:', registration);
                registration.unregister();
            }
        });
    }
    
    // Hapus semua cache yang mungkin dibuat sebelumnya
    if ('caches' in window) {
        caches.keys().then(cacheNames => {
            cacheNames.forEach(cacheName => {
                console.log('Deleting cache:', cacheName);
                caches.delete(cacheName);
            });
        });
    }
    
    // Disable navigator.onLine check dan cegah redirect ke offline.html
    // dengan override navigator.onLine
    Object.defineProperty(navigator, 'onLine', {
        get: function() {
            console.log('navigator.onLine diakses - mengembalikan true di localhost');
            return true;
        }
    });
    
    // Debugging: tambahkan listener untuk online/offline events
    window.addEventListener('online', () => {
        console.log('Browser event: Online');
    });
    
    window.addEventListener('offline', () => {
        console.log('Browser event: Offline - DIJAMIN MASIH ONLINE DI LOCALHOST');
        // Force set back to online
        setTimeout(() => {
            // Dispatch custom online event
            window.dispatchEvent(new Event('online'));
        }, 100);
    });
}
</script>
<?php endif; ?>
