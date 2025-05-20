/**
 * Service Worker Blocker untuk Localhost
 * 
 * File ini akan digunakan sebagai pengganti sw.js di localhost
 * untuk mencegah caching dan fungsi PWA yang mengganggu development
 */

self.addEventListener('install', (event) => {
  console.log('[Service Worker Blocker] Install - Tidak melakukan caching di development mode');
  // Langsung aktifkan tanpa menunggu
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  console.log('[Service Worker Blocker] Activate - Membersihkan cache lama');
  // Mengambil kontrol halaman tanpa reload
  self.clients.claim();
  // This ensures no offline.html redirects
});

// Jangan cache apapun, biarkan semua request langsung ke server
self.addEventListener('fetch', (event) => {
  event.respondWith(fetch(event.request));
});
