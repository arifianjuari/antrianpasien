// Mendaftarkan Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/assets/pwa/sw.js')
            .then(registration => {
                console.log('Service Worker berhasil didaftarkan dengan scope:', registration.scope);
            })
            .catch(error => {
                console.error('Pendaftaran Service Worker gagal:', error);
            });
    });
}

// Menampilkan prompt instalasi PWA
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    // Mencegah Chrome 67 dan yang lebih baru untuk menampilkan prompt otomatis
    e.preventDefault();
    // Simpan event agar dapat dipanggil nanti
    deferredPrompt = e;

    // Tampilkan UI untuk menunjukkan bahwa aplikasi dapat diinstal
    const installButton = document.getElementById('install-button');
    if (installButton) {
        installButton.style.display = 'block';

        installButton.addEventListener('click', () => {
            // Sembunyikan tombol install
            installButton.style.display = 'none';

            // Tampilkan prompt instalasi
            deferredPrompt.prompt();

            // Tunggu pengguna merespons prompt
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('Pengguna menerima prompt instalasi');
                } else {
                    console.log('Pengguna menolak prompt instalasi');
                }
                deferredPrompt = null;
            });
        });
    }
}); 