// Mendaftarkan Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('./assets/pwa/sw.js', { scope: './' })
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
                console.error('Detail error:', error.message);
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
    } else {
        // Tampilkan banner instalasi untuk Android jika belum diinstal
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            // Mencegah Chrome 67 dan yang lebih baru untuk menampilkan prompt otomatis
            e.preventDefault();
            // Simpan event agar dapat dipicu nanti
            deferredPrompt = e;

            // Buat banner instalasi jika belum ada
            if (!document.getElementById('pwa-install-banner')) {
                const banner = document.createElement('div');
                banner.id = 'pwa-install-banner';
                banner.style.position = 'fixed';
                banner.style.bottom = '0';
                banner.style.left = '0';
                banner.style.right = '0';
                banner.style.backgroundColor = '#198754';
                banner.style.color = 'white';
                banner.style.padding = '12px';
                banner.style.display = 'flex';
                banner.style.justifyContent = 'space-between';
                banner.style.alignItems = 'center';
                banner.style.zIndex = '9999';
                banner.innerHTML = `
                    <div>
                        <strong>Instal Praktek Obgin</strong>
                        <p style="margin: 0;">Tambahkan aplikasi ini ke layar utama Anda</p>
                    </div>
                    <button id="pwa-install-btn" style="background-color: white; color: #198754; border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold;">Instal</button>
                    <button id="pwa-close-btn" style="background: none; border: none; color: white; font-size: 20px; margin-left: 10px;">&times;</button>
                `;
                document.body.appendChild(banner);

                // Tambahkan event listener untuk tombol instal
                document.getElementById('pwa-install-btn').addEventListener('click', () => {
                    // Tampilkan prompt instalasi
                    deferredPrompt.prompt();
                    // Tunggu pengguna merespons prompt
                    deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('Pengguna menerima prompt instalasi');
                            // Sembunyikan banner
                            banner.style.display = 'none';
                        } else {
                            console.log('Pengguna menolak prompt instalasi');
                        }
                        // Clear the saved prompt since it can't be used again
                        deferredPrompt = null;
                    });
                });

                // Tambahkan event listener untuk tombol tutup
                document.getElementById('pwa-close-btn').addEventListener('click', () => {
                    banner.style.display = 'none';
                });
            }
        });
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