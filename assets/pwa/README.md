# Progressive Web App (PWA) untuk Aplikasi Antrian Pasien

Folder ini berisi file-file yang diperlukan untuk mengubah website Antrian Pasien menjadi Progressive Web App (PWA). PWA memungkinkan pengguna untuk menginstal aplikasi web di perangkat mereka dan mengaksesnya seperti aplikasi native.

## Struktur Folder

- `manifest.json` - File konfigurasi yang menjelaskan aplikasi web Anda
- `sw.js` - Service Worker untuk caching dan pengalaman offline
- `pwa.js` - Script untuk mendaftarkan service worker dan menangani instalasi PWA
- `icons/` - Folder berisi ikon dengan berbagai ukuran untuk PWA

## Cara Kerja PWA

1. **Service Worker**: Berjalan di latar belakang dan menangani caching serta pengalaman offline
2. **Manifest**: Memberikan informasi tentang aplikasi (nama, ikon, warna tema, dll.)
3. **Instalasi**: Memungkinkan pengguna untuk menginstal aplikasi di perangkat mereka

## Cara Menggunakan

Untuk menggunakan PWA, pengguna perlu:

1. Mengunjungi website menggunakan browser modern (Chrome, Firefox, Safari, Edge)
2. Pada perangkat mobile, mereka akan melihat banner "Tambahkan ke Layar Utama"
3. Pada desktop, mereka akan melihat ikon instalasi di bilah alamat atau tombol "Instal Aplikasi" di halaman

## Keuntungan PWA

- **Dapat Diinstal**: Pengguna dapat menginstal aplikasi tanpa melalui app store
- **Pengalaman Offline**: Aplikasi dapat berfungsi bahkan tanpa koneksi internet
- **Ukuran Kecil**: Tidak memerlukan unduhan besar seperti aplikasi native
- **Selalu Terbaru**: Pengguna selalu mendapatkan versi terbaru saat online
- **Lintas Platform**: Berfungsi di berbagai perangkat dan sistem operasi

## Pengujian PWA

Untuk menguji PWA Anda:

1. Buka Chrome DevTools (F12 atau Klik Kanan > Inspect)
2. Buka tab "Lighthouse"
3. Pilih kategori "Progressive Web App"
4. Klik "Generate report"

Ini akan memberikan skor dan saran untuk meningkatkan PWA Anda.

## Pemecahan Masalah

Jika PWA tidak berfungsi dengan baik:

1. Pastikan semua ikon telah dibuat dan tersedia
2. Periksa apakah manifest.json dapat diakses
3. Periksa apakah service worker terdaftar dengan benar
4. Pastikan website berjalan di HTTPS (diperlukan untuk PWA)

## Referensi

- [Web.dev PWA Guide](https://web.dev/progressive-web-apps/)
- [MDN Progressive Web Apps](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
- [PWA Builder](https://www.pwabuilder.com/) 