// Nomor rilis ikut ke nama cache, dan `activate` menghapus setiap cache yang
// namanya bukan ini. Tanpa itu nama `hkbp-volker-v1` tidak pernah berganti:
// aset yang sudah tersimpan tetap dilayani cache-first walau situsnya sudah
// naik versi, dan satu-satunya cara membersihkannya adalah menyuruh jemaat
// menghapus data situs sendiri.
//
// Harus dinaikkan bersama versi di CHANGELOG.md — dijaga PwaLuringTest.
const VERSI = '0.9.0';
const CACHE = `hkbp-volker-v${VERSI}`;

const SHELL = ['/', '/profil', '/pelayan', '/renungan', '/warta', '/galeri', '/agenda', '/penggunaan-gereja', '/offline', '/favicon.svg'];

// Halaman yang sengaja tidak pernah disimpan sebagai HTML: isinya bergantung
// pada sesi (pesan sukses setelah kirim) atau pada kode yang unik per pemohon,
// jadi versi tersimpannya menyesatkan begitu dibuka lagi.
const JANGAN_SIMPAN = ['/doa', '/penggunaan-gereja/lacak'];

// Jenis permintaan non-navigasi yang layak disimpan.
//
// Sebelumnya permintaan semacam ini dilayani cache-first dari cache yang tidak
// pernah ditulis apa pun selain HTML: halaman tersimpan terbuka saat luring,
// lalu tampil TANPA CSS sama sekali karena berkas bundel Vite-nya tidak ikut
// tersimpan. Kerangka HTML tanpa gayanya bukan halaman luring yang berguna.
//
// Nama berkas hasil build memuat hash isinya, jadi menyimpannya cache-first
// aman: isi yang berubah selalu datang dengan nama baru, dan yang lama ikut
// terbuang saat VERSI naik.
const ASET = ['style', 'script', 'font', 'image'];

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

// Simpan salinan respons ke cache, tanpa menahan respons yang sedang dikirim
// ke halaman. Hanya status 200 yang boleh masuk: cache.put menolak respons
// parsial 206, yang muncul sendiri pada permintaan rentang untuk media.
function simpan(event, request, response) {
    if (response.status !== 200) return;

    const salinan = response.clone();

    event.waitUntil(caches.open(CACHE).then((cache) => cache.put(request, salinan)));
}

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Lintas-asal tidak ikut disimpan — termasuk Google Fonts. Berkas fontnya
    // tetap butuh jaringan, dan halaman luring jatuh ke tumpukan font sistem
    // yang sudah disiapkan di layout.
    if (request.method !== 'GET' || url.origin !== self.location.origin || url.pathname.startsWith('/admin')) return;

    if (request.mode === 'navigate') {
        const bolehSimpan = !url.search && !JANGAN_SIMPAN.includes(url.pathname);

        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok && bolehSimpan) {
                        simpan(event, request, response);
                    }

                    return response;
                })
                .catch(() => caches.match(request).then((cached) => cached || caches.match('/offline'))),
        );
        return;
    }

    // Sisanya — berkas .ics, sitemap, unduhan — dibiarkan ke jaringan apa
    // adanya. Menyimpannya hanya membuat salinan basi tanpa ada yang terbantu.
    if (!ASET.includes(request.destination)) return;

    event.respondWith(
        caches.match(request).then((cached) => cached || fetch(request).then((response) => {
            simpan(event, request, response);

            return response;
        })),
    );
});
