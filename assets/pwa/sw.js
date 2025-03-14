const CACHE_NAME = 'praktek-obgin-v1';
const urlsToCache = [
    '/',
    '/index.php',
    '/login.php',
    '/register.php',
    '/dashboard.php',
    '/offline.html',
    '/assets/pwa/manifest.json',
    '/assets/pwa/icons/praktekobgin_icon72x72.png',
    '/assets/pwa/icons/praktekobgin_icon96x96.png',
    '/assets/pwa/icons/praktekobgin_icon128.png',
    '/assets/pwa/icons/praktekobgin_icon144.png',
    '/assets/pwa/icons/praktekobgin_icon192.png',
    '/assets/pwa/icons/praktekobgin_icon384.png',
    '/assets/pwa/icons/praktekobgin_icon512.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://code.jquery.com/jquery-3.6.0.min.js'
];

console.log('Service Worker dimuat');

// Install Service Worker
self.addEventListener('install', event => {
    console.log('Service Worker: Event install terdeteksi');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Cache dibuka:', CACHE_NAME);
                return cache.addAll(urlsToCache)
                    .then(() => {
                        console.log('Semua URL berhasil di-cache');
                    })
                    .catch(error => {
                        console.error('Error saat caching:', error);
                    });
            })
    );
    // Force the waiting service worker to become the active service worker
    self.skipWaiting();
});

// Activate Service Worker
self.addEventListener('activate', event => {
    console.log('Service Worker: Event activate terdeteksi');
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            console.log('Cache yang ada:', cacheNames);
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        console.log('Menghapus cache lama:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            // Mengambil kontrol dari semua klien tanpa reload
            return self.clients.claim();
        })
    );
});

// Fetch Event
self.addEventListener('fetch', event => {
    console.log('Service Worker: Event fetch terdeteksi untuk:', event.request.url);

    // Jangan mencoba cache untuk request yang bukan GET
    if (event.request.method !== 'GET') {
        return;
    }

    // Jangan mencoba cache untuk URL yang mengandung API atau admin
    if (event.request.url.includes('/api/') || event.request.url.includes('/admin/')) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Cache hit - return response
                if (response) {
                    console.log('Cache hit untuk:', event.request.url);
                    return response;
                }

                console.log('Cache miss untuk:', event.request.url);
                // Clone the request
                const fetchRequest = event.request.clone();

                return fetch(fetchRequest)
                    .then(response => {
                        // Check if valid response
                        if (!response || response.status !== 200) {
                            console.log('Response tidak valid untuk:', event.request.url, 'Status:', response.status);

                            // Jika status 500, tampilkan halaman offline
                            if (response.status === 500 && event.request.mode === 'navigate') {
                                console.log('Server error 500, menampilkan halaman offline');
                                return caches.match('/offline.html');
                            }

                            return response;
                        }

                        // Clone the response
                        const responseToCache = response.clone();

                        caches.open(CACHE_NAME)
                            .then(cache => {
                                console.log('Menyimpan response ke cache:', event.request.url);
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    })
                    .catch(error => {
                        console.log('Fetch error:', error);
                        // If both cache and network fail, show offline page
                        if (event.request.mode === 'navigate') {
                            console.log('Menampilkan halaman offline');
                            return caches.match('/offline.html');
                        }

                        // Untuk request gambar, tampilkan placeholder
                        if (event.request.destination === 'image') {
                            return new Response(
                                '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><rect width="200" height="200" fill="#f0f0f0"/><text x="50%" y="50%" font-family="Arial" font-size="20" text-anchor="middle" fill="#999">Image Offline</text></svg>',
                                { headers: { 'Content-Type': 'image/svg+xml' } }
                            );
                        }
                    });
            })
    );
});

// Push Notification Event
self.addEventListener('push', event => {
    console.log('Push notification diterima:', event);

    const title = 'Praktek Obgin';
    const options = {
        body: event.data ? event.data.text() : 'Notifikasi baru',
        icon: '/assets/pwa/icons/praktekobgin_icon192.png',
        badge: '/assets/pwa/icons/praktekobgin_icon72x72.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Lihat Aplikasi',
                icon: '/assets/pwa/icons/praktekobgin_icon72x72.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification Click Event
self.addEventListener('notificationclick', event => {
    console.log('Notifikasi diklik:', event);

    event.notification.close();

    event.waitUntil(
        clients.openWindow('/')
    );
}); 