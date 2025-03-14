// Mendaftarkan Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/assets/pwa/sw.js')
            .then(registration => {
                console.log('ServiceWorker berhasil didaftarkan dengan scope:', registration.scope);

                // Mendaftarkan untuk push notification jika didukung
                if ('PushManager' in window) {
                    console.log('Push notification didukung');

                    // Meminta izin notifikasi
                    Notification.requestPermission().then(permission => {
                        if (permission === 'granted') {
                            console.log('Izin notifikasi diberikan');
                        } else {
                            console.log('Izin notifikasi ditolak');
                        }
                    });
                }
            })
            .catch(error => {
                console.error('ServiceWorker gagal didaftarkan:', error);
            });
    });
}

// Mendeteksi apakah aplikasi dijalankan dalam mode standalone (PWA)
window.addEventListener('DOMContentLoaded', () => {
    // Cek apakah aplikasi dijalankan sebagai PWA
    const isInStandaloneMode = () =>
        (window.matchMedia('(display-mode: standalone)').matches) ||
        (window.navigator.standalone) ||
        document.referrer.includes('android-app://');

    if (isInStandaloneMode()) {
        console.log('Aplikasi dijalankan dalam mode PWA');
        // Tambahkan kelas ke body untuk styling khusus PWA jika diperlukan
        document.body.classList.add('pwa-mode');
    }
});

// Mendeteksi perubahan status koneksi
window.addEventListener('online', () => {
    console.log('Aplikasi online');
    // Tampilkan notifikasi atau perbarui UI
    if (document.getElementById('offline-indicator')) {
        document.getElementById('offline-indicator').style.display = 'none';
    }
});

window.addEventListener('offline', () => {
    console.log('Aplikasi offline');
    // Tampilkan notifikasi atau perbarui UI
    if (document.getElementById('offline-indicator')) {
        document.getElementById('offline-indicator').style.display = 'block';
    } else {
        // Buat indikator offline jika belum ada
        const offlineIndicator = document.createElement('div');
        offlineIndicator.id = 'offline-indicator';
        offlineIndicator.innerHTML = '<div style="position: fixed; bottom: 0; left: 0; right: 0; background-color: #dc3545; color: white; text-align: center; padding: 8px; z-index: 9999;">Anda sedang offline. Beberapa fitur mungkin tidak tersedia.</div>';
        document.body.appendChild(offlineIndicator);
    }
}); 