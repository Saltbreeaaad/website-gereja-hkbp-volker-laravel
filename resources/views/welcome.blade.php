<x-layout
    :title="config('gereja.nama')"
    :description="'Jadwal ibadah, renungan harian, warta jemaat, laporan kas, dan galeri kegiatan ' . config('gereja.nama') . ' di ' . config('gereja.alamat.kelurahan') . ', ' . config('gereja.alamat.kota') . '.'">

    {{-- HERO --}}
    <section id="beranda" class="relative bg-linear-to-br from-hkbp-900 via-hkbp-800 to-hkbp-600 text-white py-20 sm:py-28 flex items-center justify-center text-center">
        <div class="relative z-10 px-4 max-w-4xl">
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight text-balance">
                {{ config('gereja.denominasi') }} <br>
                <span class="text-transparent bg-clip-text bg-linear-to-r from-gold-400 via-amber-300 to-white">Persiapan Resort Volker</span>
            </h1>
            <p class="mt-5 italic font-serif text-lg sm:text-xl text-blue-100">&ldquo;{{ config('gereja.slogan') }}&rdquo;</p>

            <div class="mt-9 flex flex-wrap gap-3 justify-center">
                <a href="#jadwal" class="inline-flex items-center gap-2 bg-gold-500 hover:bg-gold-400 text-hkbp-950 text-sm font-bold px-6 py-3 rounded-xl transition-colors">
                    <i data-lucide="calendar-days" class="w-4 h-4" aria-hidden="true"></i> Lihat Jadwal Ibadah
                </a>
                <a href="{{ route('renungan') }}" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/30 text-white text-sm font-bold px-6 py-3 rounded-xl transition-colors backdrop-blur-sm">
                    {{-- book-open, bukan book-x: book-x adalah buku bertanda silang dan
                         dipakai di tempat lain untuk keadaan "belum ada renungan". --}}
                    <i data-lucide="book-open" class="w-4 h-4" aria-hidden="true"></i> Renungan Hari Ini
                </a>
            </div>
        </div>
    </section>

    {{-- PELAYAN (CAROUSEL) --}}
    <section id="pelayan" class="py-16 bg-white border-b border-slate-200 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading judul="Pelayan & Pengurus" class="mb-10" />

            <div class="swiper" data-swiper="pelayan" data-swiper-delay="2500" aria-label="Daftar pelayan dan pengurus">
                <div class="swiper-wrapper">
                    @forelse($parhalados as $person)
                        <div class="swiper-slide h-auto">
                            <article class="bg-slate-50 rounded-2xl p-6 border border-slate-200 shadow-sm text-center h-full flex flex-col justify-center items-center group hover:border-hkbp-800/30 transition-colors">
                                @if($person->foto)
                                    <img src="{{ Storage::disk('public')->url($person->foto) }}"
                                         alt="Foto {{ $person->nama }}"
                                         width="80" height="80" loading="lazy" decoding="async"
                                         class="w-20 h-20 rounded-full object-cover object-top border-4 border-white shadow-md mb-4 group-hover:scale-105 transition-transform">
                                @else
                                    <span aria-hidden="true" class="w-20 h-20 rounded-full bg-hkbp-900 text-white flex items-center justify-center font-bold text-xl border-4 border-white shadow-md mb-4 group-hover:scale-105 transition-transform">{{ $person->inisial() }}</span>
                                @endif
                                <span class="inline-block bg-blue-100 text-hkbp-800 text-[11px] font-bold px-2 py-1 rounded-md mb-2 uppercase tracking-wide">{{ $person->kategori }}</span>
                                <h3 class="font-bold text-base text-hkbp-900 leading-tight">{{ $person->nama }}</h3>
                                <p class="text-xs text-hkbp-700 font-semibold mt-1">{{ $person->jabatan }}</p>
                            </article>
                        </div>
                    @empty
                        <div class="swiper-slide"><p class="text-slate-500 italic text-center w-full">Belum ada data pelayan.</p></div>
                    @endforelse
                </div>
                <div class="text-center mt-4" data-swiper-pagination></div>
            </div>

            <x-swiper-kontrol label="daftar pelayan" />

            <p class="text-center mt-4">
                <a href="{{ route('pelayan') }}" class="inline-block py-2 text-sm font-bold text-hkbp-800 hover:text-hkbp-900 underline underline-offset-4 transition-colors">Lihat profil lengkap pelayan &rarr;</a>
            </p>
        </div>
    </section>

    {{-- TIMELINE RINGKAS --}}
    <section class="py-16 bg-white border-y border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading judul="Perjalanan Pelayanan Kami" />

            <ol class="relative grid md:grid-cols-3 gap-8">
                <li aria-hidden="true" class="hidden md:block absolute top-7 left-0 w-full border-t-4 border-slate-300 z-0"></li>

                @php
                    $tahapan = [
                        ['tahun' => '2020', 'ikon' => 'sparkles', 'label' => 'Awal Perintisan', 'bg' => 'bg-hkbp-900', 'teks' => 'text-gold-400'],
                        ['tahun' => '2023', 'ikon' => 'cross', 'label' => 'Ditetapkan Resort', 'bg' => 'bg-hkbp-800', 'teks' => 'text-gold-400'],
                        ['tahun' => '2026', 'ikon' => 'map-pin', 'label' => 'Peningkatan Pelayanan', 'bg' => 'bg-gold-500', 'teks' => 'text-hkbp-900'],
                    ];
                @endphp

                @foreach($tahapan as $tahap)
                    <li class="relative z-10 bg-white p-4 text-center">
                        <span class="w-14 h-14 {{ $tahap['bg'] }} rounded-full mx-auto flex items-center justify-center {{ $tahap['teks'] }} mb-4 border-4 border-white shadow-[0_0_15px_rgba(0,0,0,0.1)]">
                            <i data-lucide="{{ $tahap['ikon'] }}" class="w-6 h-6" aria-hidden="true"></i>
                        </span>
                        <h3 class="font-black text-hkbp-900 text-lg">{{ $tahap['tahun'] }}</h3>
                        <p class="text-sm font-semibold text-slate-600 mt-1">{{ $tahap['label'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- JADWAL IBADAH --}}
    <section id="jadwal" class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading judul="Jadwal Ibadah" deskripsi="Ibadah dan kegiatan yang akan berlangsung dalam waktu dekat." />

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($jadwal_ibadah as $jadwal)
                    <article class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-shadow border-l-4 border-l-gold-500">
                        <div class="flex justify-between items-start mb-4 gap-3">
                            <p class="bg-blue-50 text-hkbp-800 text-xs font-bold px-3 py-1.5 rounded-lg">
                                <time datetime="{{ $jadwal->tanggal->toDateString() }}">{{ $jadwal->tanggal->translatedFormat('d F Y') }}</time>
                            </p>
                            <p class="flex items-center text-slate-500 text-sm font-semibold gap-1 shrink-0">
                                <i data-lucide="clock" class="w-4 h-4" aria-hidden="true"></i> {{ $jadwal->waktu?->format('H:i') }} WIB
                            </p>
                        </div>
                        <h3 class="text-xl font-bold text-hkbp-900 mb-4 text-balance">{{ $jadwal->nama_ibadah }}</h3>
                        @if($jadwal->pelayan_firman)
                            <p class="flex items-center gap-2 text-sm text-slate-600">
                                <i data-lucide="user" class="w-4 h-4 text-hkbp-600 shrink-0" aria-hidden="true"></i>
                                <span>Pelayan: <strong class="font-semibold">{{ $jadwal->pelayan_firman }}</strong></span>
                            </p>
                        @endif
                    </article>
                @empty
                    <div class="col-span-full">
                        <x-empty-state ikon="calendar-x" pesan="Belum ada jadwal ibadah mendatang yang dipublikasikan." />
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- RENUNGAN TERBARU --}}
    <section id="renungan" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading judul="Renungan Terbaru" deskripsi="Santapan rohani untuk menguatkan langkah iman setiap hari." />

            <div class="grid md:grid-cols-3 gap-8">
                @forelse($renungans as $item)
                    <article class="bg-slate-50 rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col h-full hover:shadow-md transition-shadow">
                        <p class="flex items-center gap-2 text-xs font-bold text-slate-500 mb-3">
                            <i data-lucide="calendar" class="w-4 h-4" aria-hidden="true"></i>
                            <time datetime="{{ $item->tanggal->toDateString() }}">{{ $item->tanggal->translatedFormat('d M Y') }}</time>
                        </p>
                        <h3 class="text-xl font-bold text-hkbp-900 mb-3 line-clamp-2 text-balance">{{ $item->judul }}</h3>
                        <p class="text-slate-600 text-sm mb-6 line-clamp-3 leading-relaxed">{{ $item->ringkasan(200) }}</p>

                        {{-- Tautan menuju edisi tanggal ini, bukan selalu renungan hari ini. --}}
                        {{-- py-2: tautan setinggi 20px terlalu tipis untuk disentuh. Ini
                             ajakan bertindak yang berdiri sendiri, bukan tautan di tengah
                             kalimat, jadi pengecualian "inline" WCAG 2.5.8 tidak berlaku.
                             Sama seperti perlakuan pada tautan footer. --}}
                        <a href="{{ route('renungan', ['tanggal' => $item->tanggal->toDateString()]) }}"
                           class="mt-auto py-2 text-sm font-bold text-hkbp-700 hover:text-hkbp-900 inline-flex items-center gap-1 transition-colors">
                            Baca selengkapnya<span class="sr-only">: {{ $item->judul }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                    </article>
                @empty
                    <div class="md:col-span-3">
                        <x-empty-state ikon="book-x" pesan="Belum ada renungan yang dipublikasikan." />
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- WARTA & KEUANGAN --}}
    <section id="warta-keuangan" class="py-20 bg-slate-50 border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- min-w-0: grid item bawaannya `min-width:auto` sehingga menolak menyusut
                 di bawah lebar min-content-nya dan mendorong halaman melebar di layar
                 sempit (terukur meluber 27px pada lebar 360px). --}}
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-16">

                <div class="min-w-0">
                    <h2 class="text-3xl font-extrabold text-hkbp-900 mb-8">Warta Jemaat</h2>
                    <div class="space-y-4">
                        @forelse($warta as $item)
                            @php $unduhan = $item->urlUnduhan() @endphp
                            {{-- Bertumpuk di ponsel. Sebaris dengan label "Berkas belum
                                 tersedia" yang tidak bisa menyusut, judulnya terpotong jadi
                                 "Warta Jema…" pada layar 390px — tidak lagi terbaca. --}}
                            <article class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center gap-4 min-w-0">
                                    <span class="w-12 h-12 bg-blue-50 text-hkbp-800 rounded-lg flex items-center justify-center shrink-0">
                                        <i data-lucide="file-text" class="w-6 h-6" aria-hidden="true"></i>
                                    </span>
                                    <div class="min-w-0">
                                        {{-- Memotong dengan elipsis baru mulai sm: di ponsel
                                             judulnya punya lebar penuh dan boleh membungkus. --}}
                                        <h3 class="font-bold text-hkbp-900 sm:truncate">{{ $item->judul }}</h3>
                                        <p class="text-xs text-slate-500">
                                            <time datetime="{{ $item->tanggal->toDateString() }}">{{ $item->tanggal->translatedFormat('d F Y') }}</time>
                                        </p>
                                    </div>
                                </div>

                                {{-- Sebelumnya tautan tetap dirender walau berkasnya belum diunggah. --}}
                                @if($unduhan)
                                    <a href="{{ $unduhan }}" target="_blank" rel="noopener"
                                       class="self-start sm:self-auto shrink-0 inline-flex items-center min-h-11 bg-hkbp-100 hover:bg-hkbp-800 text-hkbp-800 hover:text-white px-4 rounded-lg text-sm font-bold transition-colors">
                                        Unduh<span class="sr-only"> warta {{ $item->judul }}</span>
                                    </a>
                                @else
                                    <span class="self-start sm:self-auto shrink-0 text-xs font-semibold text-slate-500 italic">Berkas belum tersedia</span>
                                @endif
                            </article>
                        @empty
                            <x-empty-state ikon="file-text" pesan="Belum ada warta jemaat yang diunggah." />
                        @endforelse
                    </div>

                    <p class="mt-6">
                        <a href="{{ route('warta') }}" class="inline-block py-2 text-sm font-bold text-hkbp-800 hover:text-hkbp-900 underline underline-offset-4 transition-colors">Lihat seluruh arsip warta &rarr;</a>
                    </p>
                </div>

                <div class="min-w-0">
                    <h2 class="text-3xl font-extrabold text-hkbp-900 mb-8">Kas Gereja</h2>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        @if($total_pemasukan === 0 && $total_pengeluaran === 0)
                            <p class="text-slate-500 italic text-sm text-center py-10">Data keuangan belum tersedia.</p>
                        @else
                            <div class="mb-6 flex justify-between items-center bg-hkbp-900 text-white p-4 rounded-xl gap-4">
                                <div>
                                    <p class="text-xs font-bold text-blue-200 uppercase tracking-wide">Saldo Kas</p>
                                    <p class="text-2xl font-black text-gold-400">Rp {{ number_format($saldo_akhir, 0, ',', '.') }}</p>
                                </div>
                                <i data-lucide="wallet" class="w-8 h-8 opacity-50 shrink-0" aria-hidden="true"></i>
                            </div>

                            {{-- Data grafik lewat atribut data-*, bukan Blade di dalam <script>. --}}
                            @php
                                $trenAktif = $tren_kas->contains(fn ($b) => $b['pemasukan'] > 0 || $b['pengeluaran'] > 0);
                            @endphp

                            @if($trenAktif)
                                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">Tren 12 bulan terakhir</h3>

                                <div class="h-64">
                                    <canvas id="kasChart"
                                            data-tren="{{ json_encode([
                                                'label' => $tren_kas->pluck('label'),
                                                'pemasukan' => $tren_kas->pluck('pemasukan'),
                                                'pengeluaran' => $tren_kas->pluck('pengeluaran'),
                                            ], JSON_UNESCAPED_UNICODE) }}"
                                            role="img"
                                            aria-label="Grafik batang pemasukan dan pengeluaran kas gereja per bulan selama 12 bulan terakhir. Rinciannya tersedia pada tabel di bawah grafik."></canvas>
                                </div>

                                {{-- Tabel rincian: satu-satunya bentuk yang benar-benar terbaca
                                     pembaca layar, dan tetap tampil bila JavaScript gagal. Grafik
                                     batang tidak bisa diwakili aria-label saja. --}}
                                <details class="mt-4 group">
                                    <summary class="inline-flex items-center gap-1.5 min-h-11 text-sm font-bold text-hkbp-800 hover:text-hkbp-900 cursor-pointer">
                                        <i data-lucide="file-text" class="w-4 h-4" aria-hidden="true"></i>
                                        Lihat rincian per bulan
                                    </summary>

                                    <div class="mt-3 overflow-x-auto">
                                        <table class="w-full text-sm border-collapse">
                                            <caption class="sr-only">Pemasukan dan pengeluaran kas gereja per bulan, 12 bulan terakhir.</caption>
                                            <thead>
                                                <tr class="text-left border-b border-slate-200">
                                                    <th scope="col" class="py-2 pr-3 font-bold text-slate-600">Bulan</th>
                                                    <th scope="col" class="py-2 px-3 font-bold text-emerald-700 text-right">Pemasukan</th>
                                                    <th scope="col" class="py-2 pl-3 font-bold text-red-700 text-right">Pengeluaran</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tren_kas as $bulan)
                                                    <tr class="border-b border-slate-100">
                                                        <th scope="row" class="py-2 pr-3 font-semibold text-slate-700 whitespace-nowrap">{{ $bulan['label'] }}</th>
                                                        <td class="py-2 px-3 text-right tabular-nums text-slate-700">{{ number_format($bulan['pemasukan'], 0, ',', '.') }}</td>
                                                        <td class="py-2 pl-3 text-right tabular-nums text-slate-700">{{ number_format($bulan['pengeluaran'], 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            @endif

                            {{-- Total sepanjang masa: tetap ditampilkan sebagai konteks saldo. --}}
                            <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
                                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3">
                                    <dt class="text-xs font-bold text-emerald-700 uppercase tracking-wide">Total Pemasukan</dt>
                                    <dd class="font-black text-emerald-900">Rp {{ number_format($total_pemasukan, 0, ',', '.') }}</dd>
                                </div>
                                <div class="bg-red-50 border border-red-100 rounded-xl p-3">
                                    <dt class="text-xs font-bold text-red-700 uppercase tracking-wide">Total Pengeluaran</dt>
                                    <dd class="font-black text-red-900">Rp {{ number_format($total_pengeluaran, 0, ',', '.') }}</dd>
                                </div>
                            </dl>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- GALERI (CAROUSEL) --}}
    <section id="galeri" class="py-20 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading judul="Galeri Kegiatan" class="mb-10" />

            <div class="swiper" data-swiper="galeri" data-swiper-delay="3000" aria-label="Galeri kegiatan gereja">
                <div class="swiper-wrapper">
                    @forelse($galeris as $item)
                        <div class="swiper-slide h-auto">
                            <figure class="group rounded-2xl overflow-hidden shadow-sm border border-slate-200 hover:shadow-md transition-shadow relative h-full flex flex-col">
                                <div class="w-full h-56 bg-slate-100 overflow-hidden relative">
                                    @if($item->foto)
                                        <img src="{{ Storage::disk('public')->url($item->foto) }}"
                                             alt="{{ $item->judul }}"
                                             width="400" height="224" loading="lazy" decoding="async"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <span aria-hidden="true" class="w-full h-full flex items-center justify-center">
                                            <i data-lucide="camera" class="w-8 h-8 text-slate-300"></i>
                                        </span>
                                    @endif
                                </div>
                                <figcaption class="p-4 flex-1 bg-white">
                                    <p class="text-gold-700 text-xs font-bold mb-1">
                                        <time datetime="{{ $item->tanggal->toDateString() }}">{{ $item->tanggal->translatedFormat('d M Y') }}</time>
                                    </p>
                                    <h3 class="text-hkbp-900 font-bold text-sm leading-tight">{{ $item->judul }}</h3>
                                </figcaption>
                            </figure>
                        </div>
                    @empty
                        <div class="swiper-slide"><p class="text-slate-500 italic text-center w-full">Belum ada foto galeri.</p></div>
                    @endforelse
                </div>
                <div class="text-center mt-4" data-swiper-pagination></div>
            </div>

            <x-swiper-kontrol label="galeri" />

            <p class="text-center mt-4">
                <a href="{{ route('galeri') }}" class="inline-block py-2 text-sm font-bold text-hkbp-800 hover:text-hkbp-900 underline underline-offset-4 transition-colors">Lihat seluruh dokumentasi &rarr;</a>
            </p>
        </div>
    </section>
</x-layout>
