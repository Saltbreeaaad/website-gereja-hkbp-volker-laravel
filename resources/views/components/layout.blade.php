@props([
    'title' => null,
    'description' => null,
    'image' => null,
])

@php
    $gereja = config('gereja');
    $pageTitle = $title ?: $gereja['nama'];
    $metaDescription = \Illuminate\Support\Str::limit(
        strip_tags($description ?: $gereja['deskripsi']), 160
    );
    $canonical = url()->current();
    // og:image wajib absolut — perayap media sosial tidak punya konteks halaman
    // untuk menyelesaikan URL relatif. Foto dari disk `public` sengaja relatif
    // (lihat config/filesystems.php), jadi dijadikan absolut di sini. `url()`
    // membiarkan URL yang sudah absolut apa adanya.
    $ogImage = $image ? url($image) : asset('favicon.svg');

    // Dirakit di sini, bukan inline di direktif @json: kompilator Blade memotong
    // argumen array multi-baris yang bersarang.
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Church',
        'name' => $gereja['nama'],
        'alternateName' => $gereja['nama_pendek'],
        'description' => $gereja['deskripsi'],
        'url' => url('/'),
        'telephone' => $gereja['telepon'],
        'slogan' => $gereja['slogan'],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $gereja['alamat']['jalan'],
            'addressLocality' => $gereja['alamat']['kelurahan'],
            'addressRegion' => $gereja['alamat']['provinsi'],
            'postalCode' => $gereja['alamat']['kode_pos'],
            'addressCountry' => $gereja['alamat']['negara'],
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => $gereja['koordinat']['lat'],
            'longitude' => $gereja['koordinat']['lng'],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="theme-color" content="#00255c">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="robots" content="index, follow">

    {{-- Open Graph / Twitter --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $gereja['nama'] }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=Playfair+Display:ital@1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Structured data: bantu mesin pencari menampilkan identitas & lokasi gereja.
         nonce wajib: Content-Security-Policy tidak mengizinkan skrip sebaris tanpa
         itu (lihat App\Http\Middleware\SecurityHeaders). --}}
    <script type="application/ld+json" nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-hkbp-800 selection:text-white pb-20 xl:pb-0">

    <a href="#konten-utama"
       class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-100 focus:bg-hkbp-900 focus:text-white focus:px-5 focus:py-3 focus:rounded-xl focus:font-bold focus:shadow-lg">
        Lompat ke konten utama
    </a>

    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-200/70 shadow-[0_1px_0_rgba(0,0,0,0.02)]">
        <nav aria-label="Navigasi utama" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-18 lg:h-20">

                <a href="{{ route('home') }}" class="flex items-center gap-3 group rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-hkbp-800 focus-visible:ring-offset-2">
                    <span class="w-11 h-11 lg:w-12 lg:h-12 bg-linear-to-br from-hkbp-800 to-hkbp-950 rounded-xl flex items-center justify-center text-gold-400 shadow-brand-sm group-hover:shadow-brand transition-shadow duration-300 ring-1 ring-inset ring-white/10">
                        <i data-lucide="cross" class="w-6 h-6 lg:w-7 lg:h-7" aria-hidden="true"></i>
                    </span>
                    <span>
                        <span class="block font-black text-lg lg:text-xl text-hkbp-900 leading-tight tracking-tight">{{ $gereja['nama_pendek'] }}</span>
                        <span class="block text-[10px] lg:text-[11px] font-bold text-hkbp-600/80 tracking-[0.15em]">PERSIAPAN RESORT VOLKER</span>
                    </span>
                </a>

                {{-- Menu desktop. Ambangnya xl:, bukan lg:.
                     Dengan tujuh butir barisnya sudah meluber di 768px, jadi ambangnya
                     dinaikkan ke lg:. Penambahan "Agenda" dan "Doa" membuatnya sembilan
                     butir: diukur di peramban, baris ini menuntut 1003px sementara wadah
                     di lebar 1024px hanya menyediakan 960px. Selisihnya tidak memunculkan
                     scrollbar — flexbox menutupinya dengan memampatkan, sehingga subjudul
                     logo DAN "Penggunaan Gereja" sama-sama patah menjadi dua baris dan
                     header terlihat berantakan. Di bawah 1280px dipakai panel menu mobile. --}}
                <ul class="hidden xl:flex items-center gap-1 text-sm font-semibold">
                    @foreach($gereja['menu'] as $item)
                        @php $aktif = request()->routeIs($item['route']) @endphp
                        <li>
                            <a href="{{ route($item['route']) }}"
                               @if($aktif) aria-current="page" @endif
                               class="relative block px-3.5 py-2.5 rounded-lg transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-hkbp-800 {{ $aktif ? 'text-hkbp-900' : 'text-slate-600 hover:text-hkbp-900 hover:bg-slate-100/80' }}">
                                {{ $item['label'] }}
                                @if($aktif)
                                    <span aria-hidden="true" class="absolute left-3.5 right-3.5 -bottom-0.5 h-0.5 rounded-full bg-linear-to-r from-gold-500 to-gold-400"></span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Tombol menu mobile --}}
                <button type="button"
                        data-menu-toggle
                        aria-controls="menu-mobile"
                        aria-expanded="false"
                        class="xl:hidden inline-flex items-center justify-center w-11 h-11 rounded-xl text-hkbp-900 hover:bg-slate-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-hkbp-800">
                    <span class="sr-only">Buka menu navigasi</span>
                    <i data-lucide="menu" class="w-6 h-6" data-menu-icon="open" aria-hidden="true"></i>
                    <i data-lucide="x" class="w-6 h-6 hidden" data-menu-icon="close" aria-hidden="true"></i>
                </button>
            </div>

            {{-- Panel menu mobile --}}
            <div id="menu-mobile" hidden class="xl:hidden border-t border-slate-200/70 py-3">
                <ul class="space-y-1 text-sm font-semibold">
                    @foreach($gereja['menu'] as $item)
                        @php $aktif = request()->routeIs($item['route']) @endphp
                        <li>
                            <a href="{{ route($item['route']) }}"
                               @if($aktif) aria-current="page" @endif
                               class="flex items-center gap-2 px-4 py-3 rounded-xl transition-colors {{ $aktif ? 'text-hkbp-900 bg-blue-50' : 'text-slate-700 hover:bg-slate-100' }}">
                                @if($aktif)
                                    <span aria-hidden="true" class="w-1.5 h-1.5 rounded-full bg-gold-500"></span>
                                @endif
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </nav>
    </header>

    <main id="konten-utama" tabindex="-1" class="focus:outline-none">
        {{ $slot }}
    </main>

    {{-- Navigasi bawah membuat lima tujuan utama mudah dijangkau ibu jari di
         ponsel. Menu lengkap tetap tersedia lewat tombol di header. --}}
    <nav aria-label="Akses cepat mobile" data-no-print class="xl:hidden fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white/95 backdrop-blur-xl px-2 pb-[max(0.35rem,env(safe-area-inset-bottom))] pt-2 shadow-[0_-8px_30px_rgba(0,37,92,0.10)]">
        <ul class="grid grid-cols-5 max-w-lg mx-auto">
            @foreach([
                ['route' => 'home', 'label' => 'Beranda', 'icon' => 'house'],
                ['route' => 'agenda', 'label' => 'Agenda', 'icon' => 'calendar-days'],
                ['route' => 'renungan', 'label' => 'Renungan', 'icon' => 'book-open'],
                ['route' => 'doa', 'label' => 'Doa', 'icon' => 'heart-handshake'],
                ['route' => 'penggunaan-gereja', 'label' => 'Gedung', 'icon' => 'building-2'],
            ] as $item)
                @php $aktifBawah = request()->routeIs($item['route']) @endphp
                <li>
                    <a href="{{ route($item['route']) }}" @if($aktifBawah) aria-current="page" @endif class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl text-[10px] font-bold {{ $aktifBawah ? 'text-hkbp-900 bg-blue-50' : 'text-slate-500 hover:text-hkbp-800' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5" aria-hidden="true"></i>{{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <footer id="kontak" class="relative bg-linear-to-b from-hkbp-950 to-[#001033] text-blue-50 pt-16 pb-8 border-t border-hkbp-900 mt-20 overflow-hidden">
        {{-- Hiasan murni dekoratif, disembunyikan dari pembohong layar & bebas
             animasi (lihat .blob-hias di app.css). --}}
        <div aria-hidden="true" class="blob-hias w-96 h-96 -top-32 -right-32 bg-hkbp-700/30"></div>
        <div aria-hidden="true" class="blob-hias w-72 h-72 -bottom-24 -left-24 bg-gold-500/10"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-10 mb-12">

                <div class="space-y-4">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-10 h-10 bg-linear-to-br from-hkbp-800 to-hkbp-950 ring-1 ring-inset ring-white/10 rounded-lg flex items-center justify-center text-gold-400">
                            <i data-lucide="cross" class="w-6 h-6" aria-hidden="true"></i>
                        </span>
                        <h2 class="text-xl font-bold text-white leading-tight">{{ $gereja['nama_pendek'] }}</h2>
                    </div>
                    <p class="text-sm text-blue-200/90 leading-relaxed">{{ $gereja['slogan'] }} bagi seluruh ciptaan.</p>

                    <address class="pt-4 space-y-3 not-italic">
                        <span class="flex items-start gap-3 text-sm text-blue-200/90">
                            <i data-lucide="map-pin" class="w-5 h-5 text-gold-400 shrink-0" aria-hidden="true"></i>
                            <span>{{ $gereja['alamat']['jalan'] }}, {{ $gereja['alamat']['kelurahan'] }}, {{ $gereja['alamat']['kota'] }}</span>
                        </span>
                        <span class="flex items-center gap-3 text-sm text-blue-200/90">
                            <i data-lucide="phone" class="w-5 h-5 text-gold-400 shrink-0" aria-hidden="true"></i>
                            {{-- inline-block + py-2: tautan telepon setinggi 20px terlalu tipis
                                 untuk disentuh dengan nyaman di ponsel. --}}
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $gereja['telepon']) }}" class="inline-block py-2 hover:text-white transition-colors">{{ $gereja['telepon'] }}</a>
                        </span>
                    </address>

                    <nav aria-label="Navigasi footer" class="pt-4">
                        {{-- Tautan footer semula hanya setinggi 18px. inline-block + py-2
                             menaikkannya ke ~34px, di atas ambang WCAG 2.5.8 (24px). --}}
                        <ul class="flex flex-wrap gap-x-4 text-sm text-blue-200/90">
                            @foreach($gereja['menu'] as $item)
                                <li><a href="{{ route($item['route']) }}" class="inline-block py-2 hover:text-gold-400 transition-colors">{{ $item['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </nav>
                </div>

                <div class="md:col-span-2 rounded-2xl overflow-hidden h-64 border border-hkbp-800/80 shadow-brand ring-1 ring-white/5">
                    {{-- Gaya sebaris `border:0` diganti kelas: satu-satunya atribut
                         style di seluruh markup, dan menghapusnya membuat CSP bisa
                         melarang gaya sebaris sepenuhnya. Preflight Tailwind sudah
                         menihilkan border, jadi kelasnya hanya menegaskan maksud. --}}
                    <iframe src="{{ $gereja['peta_embed'] }}"
                            title="Peta lokasi {{ $gereja['nama'] }}"
                            class="w-full h-full border-0"
                            allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>

            </div>

            <p class="text-center text-xs text-blue-400/50 border-t border-hkbp-900 pt-8">
                &copy; {{ now()->year }} {{ $gereja['nama'] }}. All rights reserved.
            </p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
