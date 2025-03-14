const CACHE_NAME = 'antrian-pasien-v1';
const urlsToCache = [
    '/',
    '/index.php',
    '/login.php',
    '/register.php',
    '/dashboard.php',
    '/assets/pwa/manifest.json',
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
        })
    );
});

// Fetch Event
self.addEventListener('fetch', event => {
    console.log('Service Worker: Event fetch terdeteksi untuk:', event.request.url);
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

                return fetch(fetchRequest).then(
                    response => {
                        // Check if valid response
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            console.log('Response tidak valid untuk:', event.request.url);
                            return response;
                        }

                        // Clone the response
                        const responseToCache = response.clone();

                        caches.open(CACHE_NAME)
                            .then(cache => {
                                // Don't cache POST requests
                                if (event.request.method !== 'POST') {
                                    console.log('Menyimpan response ke cache:', event.request.url);
                                    cache.put(event.request, responseToCache);
                                }
                            });

                        return response;
                    }
                );
            })
            .catch(() => {
                // If both cache and network fail, show offline page
                if (event.request.mode === 'navigate') {
                    console.log('Menampilkan halaman offline');
                    return caches.match('/offline.html');
                }
            })
    );
}); 