const CACHE = 'hkbp-volker-v1';
const SHELL = ['/', '/profil', '/pelayan', '/renungan', '/warta', '/galeri', '/agenda', '/penggunaan-gereja', '/offline', '/favicon.svg'];

// Halaman yang sengaja tidak pernah disimpan sebagai HTML: isinya bergantung
// pada sesi (pesan sukses setelah kirim) atau pada kode yang unik per pemohon,
// jadi versi tersimpannya menyesatkan begitu dibuka lagi.
const JANGAN_SIMPAN = ['/doa', '/penggunaan-gereja/lacak'];

// addAll bersifat semua-atau-tidak-sama-sekali: satu URL yang gagal — halaman
// error, tunnel presentasi yang belum siap — membatalkan seluruh instalasi dan
// service worker tidak pernah aktif. Tiap berkas karena itu disimpan sendiri
// dan kegagalannya diabaikan; kerangka yang tersimpan sebagian tetap berguna.
async function isiKerangka() {
    const cache = await caches.open(CACHE);

    await Promise.all(SHELL.map((url) => cache.add(url).catch(() => {})));
}

self.addEventListener('install', (event) => {
    // skipWaiting di dalam waitUntil: di luar sana ia bisa berjalan sebelum
    // cache terisi, sehingga versi baru mengambil alih dengan cache kosong.
    event.waitUntil(isiKerangka().then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin || url.pathname.startsWith('/admin')) return;

    if (request.mode === 'navigate') {
        const bolehSimpan = !url.search && !JANGAN_SIMPAN.includes(url.pathname);

        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok && bolehSimpan) {
                        const salinan = response.clone();
                        event.waitUntil(caches.open(CACHE).then((cache) => cache.put(request, salinan)));
                    }

                    return response;
                })
                .catch(() => caches.match(request).then((cached) => cached || caches.match('/offline'))),
        );
        return;
    }

    event.respondWith(caches.match(request).then((cached) => cached || fetch(request)));
});
