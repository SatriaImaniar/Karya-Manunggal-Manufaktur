self.addEventListener('install', (e) => {
    console.log('[Service Worker] Terinstall');
});

self.addEventListener('fetch', (e) => {
    // Basic fetch - browser hanya butuh tau event ini ada agar web bisa di-install
    e.respondWith(fetch(e.request).catch(() => {
        return new Response('Anda sedang offline. Cek koneksi internet Anda.');
    }));
});