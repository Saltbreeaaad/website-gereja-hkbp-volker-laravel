import {
    createIcons,
    ArrowRight, BookOpen, BookX, Building2, Calendar, CalendarCheck, CalendarDays, CalendarPlus, CalendarX, Camera,
    ChevronLeft, ChevronRight, CircleCheck, Clock, Clock3, Cross, Eye, FileText, Heart, HeartHandshake, Home, Hourglass,
    Inbox, MapPin, Megaphone, Menu, Pause, Phone, Play, Search, Send, Share2, ShieldCheck, Sparkles, Target, User, UserRoundX,
    Wallet, WifiOff, X, ZoomIn,
} from 'lucide';

/**
 * Swiper dan Chart.js hanya dipakai di beranda, tetapi bundel tunggal memaksa
 * setiap halaman mengunduh keduanya (~240 kB). Keduanya kini diimpor dinamis,
 * jadi halaman tanpa carousel atau grafik tidak membayarnya sama sekali.
 */

/** Pengguna yang meminta animasi diminimalkan: matikan autoplay dan animasi grafik. */
const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ------------------------------------------------------------------ *
 * Ikon
 * ------------------------------------------------------------------ */

const icons = {
    ArrowRight, BookOpen, BookX, Building2, Calendar, CalendarCheck, CalendarDays, CalendarPlus, CalendarX, Camera,
    ChevronLeft, ChevronRight, CircleCheck, Clock, Clock3, Cross, Eye, FileText, Heart, HeartHandshake, Home, Hourglass,
    Inbox, MapPin, Megaphone, Menu, Pause, Phone, Play, Search, Send, Share2, ShieldCheck, Sparkles, Target, User, UserRoundX,
    Wallet, WifiOff, X, ZoomIn,
};

/* ------------------------------------------------------------------ *
 * Menu navigasi mobile
 * ------------------------------------------------------------------ */

function initMenuMobile() {
    const tombol = document.querySelector('[data-menu-toggle]');
    if (!tombol) return;

    const panel = document.getElementById(tombol.getAttribute('aria-controls'));
    if (!panel) return;

    const ikonBuka = tombol.querySelector('[data-menu-icon="open"]');
    const ikonTutup = tombol.querySelector('[data-menu-icon="close"]');
    const label = tombol.querySelector('.sr-only');

    const setTerbuka = (terbuka) => {
        tombol.setAttribute('aria-expanded', String(terbuka));
        panel.hidden = !terbuka;
        ikonBuka?.classList.toggle('hidden', terbuka);
        ikonTutup?.classList.toggle('hidden', !terbuka);
        if (label) label.textContent = terbuka ? 'Tutup menu navigasi' : 'Buka menu navigasi';
    };

    tombol.addEventListener('click', () => {
        setTerbuka(tombol.getAttribute('aria-expanded') !== 'true');
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && tombol.getAttribute('aria-expanded') === 'true') {
            setTerbuka(false);
            tombol.focus();
        }
    });

    // Kembali ke layar lebar saat menu terbuka: tutup supaya state tidak nyangkut.
    //
    // Ambangnya harus sama dengan tempat menu desktop muncul (xl:, 80rem di
    // layout). Sebelumnya 48rem: menu mobile dipaksa tertutup pada 768px
    // padahal tombol hamburger masih satu-satunya navigasi sampai jauh di
    // atasnya, jadi pengguna yang memperlebar jendela kehilangan menunya tanpa
    // mendapat penggantinya.
    window.matchMedia('(min-width: 80rem)').addEventListener('change', (e) => {
        if (e.matches) setTerbuka(false);
    });
}

/* ------------------------------------------------------------------ *
 * Input yang mengirim formulirnya sendiri saat nilainya berubah
 * ------------------------------------------------------------------ */

function initAutoSubmit() {
    document.querySelectorAll('[data-auto-submit]').forEach((input) => {
        input.addEventListener('change', () => input.form?.requestSubmit());
    });
}

/* ------------------------------------------------------------------ *
 * Fokus ke ringkasan galat formulir
 * ------------------------------------------------------------------ */

/**
 * Setelah pengiriman gagal, halaman dimuat ulang di posisi paling atas sehingga
 * pesan galat mudah terlewat — terutama di layar kecil. Pindahkan fokus ke
 * ringkasan galat supaya pembaca layar mengumumkannya dan halaman tergulir ke sana.
 */
function initFokusGalat() {
    const ringkasan = document.querySelector('[data-error-summary]');
    if (!ringkasan) return;

    ringkasan.focus();
    ringkasan.scrollIntoView({ block: 'center', behavior: kurangiGerak ? 'auto' : 'smooth' });
}

function initBagikan() {
    document.querySelectorAll('[data-share-url]').forEach((tombol) => {
        tombol.addEventListener('click', async () => {
            const data = { title: tombol.dataset.shareTitle || document.title, url: tombol.dataset.shareUrl };

            try {
                if (navigator.share) {
                    await navigator.share(data);
                } else {
                    await navigator.clipboard.writeText(data.url);
                    const label = tombol.querySelector('[data-share-label]');
                    if (label) {
                        label.textContent = 'Tautan disalin';
                        window.setTimeout(() => { label.textContent = 'Bagikan renungan'; }, 2000);
                    }
                }
            } catch (error) {
                if (error?.name !== 'AbortError') window.location.href = data.url;
            }
        });
    });
}

function initCetak() {
    document.querySelectorAll('[data-print]').forEach((tombol) => {
        tombol.addEventListener('click', () => window.print());
    });
}

function initMuatUlang() {
    document.querySelectorAll('[data-reload]').forEach((tombol) => {
        tombol.addEventListener('click', () => location.reload());
    });
}

/* ------------------------------------------------------------------ *
 * Lightbox galeri
 * ------------------------------------------------------------------ */

/**
 * Foto galeri sebelumnya tidak bisa diperbesar sama sekali. Memakai <dialog>
 * bawaan browser: penjebakan fokus, tutup dengan Escape, dan latar modal sudah
 * ditangani platform — tanpa pustaka tambahan.
 */
function initLightbox() {
    const dialog = document.getElementById('lightbox');
    const pemicu = document.querySelectorAll('[data-lightbox]');
    if (!dialog || pemicu.length === 0) return;

    const gambar = dialog.querySelector('[data-lightbox-image]');
    const judul = dialog.querySelector('[data-lightbox-caption]');

    pemicu.forEach((tombol) => {
        tombol.addEventListener('click', () => {
            gambar.src = tombol.dataset.lightbox;
            gambar.alt = tombol.dataset.lightboxCaption || '';
            judul.textContent = tombol.dataset.lightboxCaption || '';
            dialog.showModal();
        });
    });

    // Klik di area gelap di luar gambar menutup dialog.
    dialog.addEventListener('click', (e) => {
        if (e.target === dialog) dialog.close();
    });

    dialog.addEventListener('close', () => {
        gambar.removeAttribute('src');
    });
}

/* ------------------------------------------------------------------ *
 * Carousel — dimuat hanya bila ada di halaman
 * ------------------------------------------------------------------ */

async function initCarousels() {
    const wadah = document.querySelectorAll('[data-swiper]');
    if (wadah.length === 0) return;

    const [{ Swiper }, { Autoplay, A11y, Keyboard, Navigation, Pagination }] = await Promise.all([
        import('swiper'),
        import('swiper/modules'),
    ]);

    Swiper.use([Autoplay, A11y, Keyboard, Navigation, Pagination]);

    wadah.forEach((el) => {
        const jeda = Number(el.dataset.swiperDelay || 3000);
        const jumlahSlide = el.querySelectorAll('.swiper-slide').length;

        // Tombol geser dicari di induk yang sama dengan carousel-nya, pola yang
        // sama dengan tombol jeda. Tiap carousel berada di wadah section
        // sendiri, jadi tidak ada yang saling mengambil tombol milik tetangga.
        const tombolPrev = el.parentElement?.querySelector('[data-swiper-prev]');
        const tombolNext = el.parentElement?.querySelector('[data-swiper-next]');

        const swiper = new Swiper(el, {
            slidesPerView: 1,
            spaceBetween: 20,
            // Loop hanya aman bila slide lebih banyak dari jumlah kolom terlebar.
            loop: jumlahSlide > 4,
            keyboard: { enabled: true },
            a11y: {
                prevSlideMessage: 'Slide sebelumnya',
                nextSlideMessage: 'Slide berikutnya',
            },
            autoplay: kurangiGerak
                ? false
                : { delay: jeda, disableOnInteraction: false, pauseOnMouseEnter: true },
            pagination: { el: el.querySelector('[data-swiper-pagination]'), clickable: true },
            navigation: { prevEl: tombolPrev, nextEl: tombolNext },
            breakpoints: {
                640: { slidesPerView: 2 },
                768: { slidesPerView: 3 },
                1024: { slidesPerView: 4 },
            },
        });

        // Baru ditampilkan setelah Swiper benar-benar terpasang: sebelum itu
        // tombolnya tidak melakukan apa pun.
        if (tombolPrev) tombolPrev.hidden = false;
        if (tombolNext) tombolNext.hidden = false;

        pasangTombolJeda(el, swiper);
    });
}

/**
 * WCAG 2.2.2: konten yang bergerak otomatis lebih dari 5 detik harus punya cara
 * menghentikannya. pauseOnMouseEnter tidak menolong pengguna layar sentuh dan
 * pengguna keyboard, jadi sediakan tombol yang sesungguhnya.
 */
function pasangTombolJeda(el, swiper) {
    const tombol = el.parentElement?.querySelector('[data-swiper-toggle]');
    if (!tombol || !swiper.autoplay) return;

    tombol.hidden = false;

    const setBerjalan = (berjalan) => {
        tombol.setAttribute('aria-pressed', String(!berjalan));
        tombol.querySelector('[data-icon="pause"]')?.classList.toggle('hidden', !berjalan);
        tombol.querySelector('[data-icon="play"]')?.classList.toggle('hidden', berjalan);
        tombol.querySelector('[data-label]').textContent = berjalan ? 'Jeda' : 'Putar';
    };

    // Niat pengguna disimpan sendiri, tidak dibaca ulang dari Swiper tiap klik.
    //
    // `swiper.autoplay.paused` ikut menyala karena hal-hal di luar kehendak
    // pengguna: tab berpindah ke latar belakang, atau kursor sedang berada di
    // atas carousel (pauseOnMouseEnter). Versi sebelumnya menyimpulkan "sedang
    // berjalan" dari `running && !paused`, sehingga pada keadaan itu tombolnya
    // terbalik — ditekan untuk menjeda, yang terjadi malah memulai ulang.
    // Terlihat saat pengujian: menekan Jeda tidak menghentikan apa pun.
    let diputar = true;

    tombol.addEventListener('click', () => {
        diputar = !diputar;

        if (diputar) {
            swiper.autoplay.start();
        } else {
            swiper.autoplay.stop();
        }

        setBerjalan(diputar);
    });

    setBerjalan(true);
}

/* ------------------------------------------------------------------ *
 * Grafik tren kas gereja — dimuat hanya bila ada di halaman
 * ------------------------------------------------------------------ */

async function initGrafikKas() {
    const canvas = document.getElementById('kasChart');
    if (!canvas) return;

    let tren;
    try {
        tren = JSON.parse(canvas.dataset.tren || '{}');
    } catch {
        return;
    }

    if (!Array.isArray(tren.label) || tren.label.length === 0) return;

    const { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend } =
        await import('chart.js');
    Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

    const rupiah = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    });

    // Sumbu nominal memakai notasi ringkas ("Rp 2,5 jt"): label rupiah penuh
    // pada dua belas bulan saling bertindih dan sumbunya jadi tidak terbaca.
    const ringkas = new Intl.NumberFormat('id-ID', {
        notation: 'compact',
        maximumFractionDigits: 1,
    });

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: tren.label,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: tren.pemasukan,
                    backgroundColor: '#0f766e',
                    borderRadius: 4,
                },
                {
                    label: 'Pengeluaran',
                    data: tren.pengeluaran,
                    backgroundColor: '#b91c1c',
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: kurangiGerak ? false : undefined,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Plus Jakarta Sans', size: 10 } },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { family: 'Plus Jakarta Sans', size: 10 },
                        callback: (nilai) => 'Rp ' + ringkas.format(nilai),
                    },
                },
            },
            plugins: {
                legend: { position: 'bottom', labels: { font: { family: 'Plus Jakarta Sans' } } },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${rupiah.format(ctx.parsed.y)}`,
                    },
                },
            },
        },
    });
}

/* ------------------------------------------------------------------ */

function init() {
    createIcons({ icons });
    initMenuMobile();
    initAutoSubmit();
    initFokusGalat();
    initBagikan();
    initCetak();
    initMuatUlang();
    initLightbox();
    initCarousels();
    initGrafikKas();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

if ('serviceWorker' in navigator && (window.isSecureContext || location.hostname === 'localhost')) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}
