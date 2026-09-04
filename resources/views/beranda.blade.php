<x-layout
    :title="config('gereja.nama')"
    :description="'Jadwal ibadah, renungan harian, warta jemaat, laporan kas, dan galeri kegiatan ' . config('gereja.nama') . ' di ' . config('gereja.alamat.kelurahan') . ', ' . config('gereja.alamat.kota') . '.'">

    @if($pengumuman->isNotEmpty())
        <section aria-label="Pengumuman penting" class="bg-gold-300 text-hkbp-950 border-b border-gold-400">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-start gap-3">
                <i data-lucide="megaphone" class="w-5 h-5 shrink-0 mt-0.5" aria-hidden="true"></i>
                <div class="min-w-0 text-sm leading-relaxed">
                    @foreach($pengumuman as $item)
                        <p @class(['mt-1' => ! $loop->first])><strong>{{ $item->judul }}.</strong> {{ $item->isi }}
                            @if($item->tautan)<a href="{{ $item->tautan }}" class="ml-1 font-black underline underline-offset-2">{{ $item->label_tautan ?: 'Selengkapnya' }}</a>@endif
                        </p>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- HERO --}}
    <section id="beranda" class="relative overflow-hidden bg-linear-to-br from-hkbp-950 via-hkbp-900 to-hkbp-700 text-white py-24 sm:py-32 flex items-center justify-center text-center">
        {{-- Hiasan dekoratif: aria-hidden, animasinya diredam otomatis oleh
             prefers-reduced-motion global di app.css. --}}
        <div aria-hidden="true" class="blob-hias w-[32rem] h-[32rem] -top-48 -right-32 bg-hkbp-600/40 animate-blob"></div>
        <div aria-hidden="true" class="blob-hias w-96 h-96 -bottom-40 -left-24 bg-gold-500/15 animate-blob [animation-delay:-7s]"></div>
        <div aria-hidden="true" class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.07)_1px,transparent_0)] [background-size:32px_32px]"></div>
        <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-32 bg-linear-to-t from-slate-50 to-transparent"></div>

        <div class="relative z-10 px-4 max-w-4xl animate-fade-up">
            <span class="inline-flex items-center gap-2 text-xs font-bold tracking-[0.2em] uppercase text-gold-300 mb-6 bg-white/5 ring-1 ring-inset ring-white/10 px-4 py-2 rounded-full backdrop-blur-sm">
                <i data-lucide="cross" class="w-3.5 h-3.5" aria-hidden="true"></i>
                Selamat Datang
            </span>
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.1] text-balance">
                {{ config('gereja.denominasi') }} <br>
                <span class="text-transparent bg-clip-text bg-linear-to-r from-gold-400 via-amber-300 to-gold-200">Persiapan Resort Volker</span>
            </h1>
            <p class="mt-6 italic font-serif text-lg sm:text-xl text-blue-100/90">&ldquo;{{ config('gereja.slogan') }}&rdquo;</p>

            <div class="mt-10 flex flex-wrap gap-3 justify-center">
                <a href="#jadwal" class="inline-flex items-center gap-2 bg-linear-to-b from-gold-400 to-gold-500 hover:from-gold-300 hover:to-gold-400 text-hkbp-950 text-sm font-bold px-6 py-3.5 rounded-xl shadow-gold transition-all duration-200 hover:-translate-y-0.5">
                    <i data-lucide="calendar-days" class="w-4 h-4" aria-hidden="true"></i> Lihat Jadwal Ibadah
                </a>
                <a href="{{ route('renungan') }}" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-sm font-bold px-6 py-3.5 rounded-xl transition-all duration-200 backdrop-blur-sm hover:-translate-y-0.5">
                    {{-- book-open, bukan book-x: book-x adalah buku bertanda silang dan
                         dipakai di tempat lain untuk keadaan "belum ada renungan". --}}
                    <i data-lucide="book-open" class="w-4 h-4" aria-hidden="true"></i> Renungan Hari Ini
                </a>
            </div>
        </div>
    </section>

    <section class="relative z-20 -mt-7 sm:-mt-9 px-4" aria-label="Akses cepat">
        <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-brand-sm">
            @foreach([
                ['route' => 'agenda', 'icon' => 'calendar-days', 'label' => 'Agenda'],
                ['route' => 'renungan', 'icon' => 'book-open', 'label' => 'Renungan'],
                ['route' => 'doa', 'icon' => 'heart-handshake', 'label' => 'Minta Doa'],
                ['route' => 'penggunaan-gereja', 'icon' => 'building-2', 'label' => 'Pakai Gedung'],
            ] as $aksi)
                <a href="{{ route($aksi['route']) }}" class="group flex items-center gap-3 p-4 sm:p-5 border-b md:border-b-0 even:border-l md:border-l border-slate-200 hover:bg-blue-50/70 transition-colors focus:z-10">
                    <span class="grid place-items-center w-10 h-10 rounded-xl bg-blue-50 text-hkbp-800 group-hover:bg-hkbp-800 group-hover:text-white transition-colors"><i data-lucide="{{ $aksi['icon'] }}" class="w-5 h-5" aria-hidden="true"></i></span>
                    <span class="text-sm font-black text-hkbp-900">{{ $aksi['label'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- PELAYAN (CAROUSEL) --}}
    <section id="pelayan" class="py-20 bg-white border-b border-slate-100 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Parhalado" judul="Pelayan & Pengurus" class="mb-10" />

            <div class="swiper" data-swiper="pelayan" data-swiper-delay="2500" aria-label="Daftar pelayan dan pengurus">
                <div class="swiper-wrapper">
                    @forelse($parhalados as $person)
                        <div class="swiper-slide h-auto">
                            <article class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-brand-sm text-center h-full flex flex-col justify-center items-center group hover:shadow-brand hover:-translate-y-1 transition-all duration-300">
                                @if($person->foto)
                                    <img src="{{ $person->urlFoto() }}"
                                         @if($srcset = $person->srcsetFoto()) srcset="{{ $srcset }}" sizes="80px" @endif
                                         alt="Foto {{ $person->nama }}"
                                         width="80" height="80" loading="lazy" decoding="async"
                                         class="w-20 h-20 rounded-full object-cover object-top ring-4 ring-white shadow-md mb-4 group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <span aria-hidden="true" class="w-20 h-20 rounded-full bg-linear-to-br from-hkbp-800 to-hkbp-950 text-white flex items-center justify-center font-bold text-xl ring-4 ring-white shadow-md mb-4 group-hover:scale-105 transition-transform duration-300">{{ $person->inisial() }}</span>
                                @endif
                                <span class="inline-block bg-blue-50 text-hkbp-800 text-[11px] font-bold px-2.5 py-1 rounded-md mb-2 uppercase tracking-wide ring-1 ring-inset ring-blue-100">{{ $person->kategori }}</span>
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
                <a href="{{ route('pelayan') }}" class="inline-flex items-center gap-1 py-2 text-sm font-bold text-hkbp-800 hover:text-hkbp-900 transition-colors group">
                    Lihat profil lengkap pelayan
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" aria-hidden="true"></i>
                </a>
            </p>
        </div>
    </section>

    {{-- TIMELINE RINGKAS --}}
    <section class="py-20 bg-slate-50 border-b border-slate-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Sejarah Singkat" judul="Perjalanan Pelayanan Kami" />

            <ol class="relative grid md:grid-cols-3 gap-8">
                <li aria-hidden="true" class="hidden md:block absolute top-7 left-0 w-full border-t-2 border-dashed border-slate-300 z-0"></li>

                @php
                    $tahapan = [
                        ['tahun' => '2020', 'ikon' => 'sparkles', 'label' => 'Awal Perintisan', 'bg' => 'from-hkbp-900 to-hkbp-950', 'teks' => 'text-gold-400'],
                        ['tahun' => '2023', 'ikon' => 'cross', 'label' => 'Ditetapkan Resort', 'bg' => 'from-hkbp-700 to-hkbp-800', 'teks' => 'text-gold-400'],
                        ['tahun' => '2026', 'ikon' => 'map-pin', 'label' => 'Peningkatan Pelayanan', 'bg' => 'from-gold-400 to-gold-500', 'teks' => 'text-hkbp-900'],
                    ];
                @endphp

                @foreach($tahapan as $tahap)
                    <li class="relative z-10 bg-slate-50 p-4 text-center">
                        <span class="w-14 h-14 bg-linear-to-br {{ $tahap['bg'] }} rounded-full mx-auto flex items-center justify-center {{ $tahap['teks'] }} mb-4 ring-4 ring-slate-50 shadow-brand">
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
    <section id="jadwal" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Agenda" judul="Jadwal Ibadah" deskripsi="Ibadah dan kegiatan yang akan berlangsung dalam waktu dekat." />

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($jadwal_ibadah as $jadwal)
                    <article class="relative bg-white rounded-2xl p-6 border border-slate-200/80 shadow-brand-sm hover:shadow-brand hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <span aria-hidden="true" class="absolute inset-y-0 left-0 w-1 bg-linear-to-b from-gold-400 to-gold-600"></span>
                        <div class="flex justify-between items-start mb-4 gap-3">
                            <p class="bg-blue-50 text-hkbp-800 text-xs font-bold px-3 py-1.5 rounded-lg ring-1 ring-inset ring-blue-100">
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
    <section id="renungan" class="py-20 bg-slate-50 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Santapan Rohani" judul="Renungan Terbaru" deskripsi="Santapan rohani untuk menguatkan langkah iman setiap hari." />

            <div class="grid md:grid-cols-3 gap-8">
                @forelse($renungans as $item)
                    <article class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-brand-sm flex flex-col h-full hover:shadow-brand hover:-translate-y-1 transition-all duration-300">
                        <p class="flex items-center gap-2 text-xs font-bold text-slate-500 mb-3">
                            <i data-lucide="calendar" class="w-4 h-4 text-gold-700" aria-hidden="true"></i>
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
                           class="mt-auto py-2 text-sm font-bold text-hkbp-700 hover:text-hkbp-900 inline-flex items-center gap-1 transition-colors group">
                            Baca selengkapnya<span class="sr-only">: {{ $item->judul }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" aria-hidden="true"></i>
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
    <section id="warta-keuangan" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- min-w-0: grid item bawaannya `min-width:auto` sehingga menolak menyusut
                 di bawah lebar min-content-nya dan mendorong halaman melebar di layar
                 sempit (terukur meluber 27px pada lebar 360px). --}}
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-16">

                <div class="min-w-0">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-hkbp-900 mb-8 flex items-center gap-3">
                        <span aria-hidden="true" class="w-9 h-9 rounded-lg bg-blue-50 text-hkbp-800 flex items-center justify-center ring-1 ring-inset ring-blue-100">
                            <i data-lucide="file-text" class="w-4.5 h-4.5"></i>
                        </span>
                        Warta Jemaat
                    </h2>
                    <div class="space-y-4">
                        @forelse($warta as $item)
                            @php $unduhan = $item->urlUnduhan() @endphp
                            {{-- Bertumpuk di ponsel. Sebaris dengan label "Berkas belum
                                 tersedia" yang tidak bisa menyusut, judulnya terpotong jadi
                                 "Warta Jema…" pada layar 390px — tidak lagi terbaca. --}}
                            <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-brand-sm flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4 hover:shadow-brand hover:border-hkbp-200 transition-all duration-300">
                                <div class="flex items-center gap-4 min-w-0">
                                    <span class="w-12 h-12 bg-blue-50 text-hkbp-800 rounded-lg flex items-center justify-center shrink-0 ring-1 ring-inset ring-blue-100">
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
                                       class="self-start sm:self-auto shrink-0 inline-flex items-center min-h-11 bg-hkbp-50 hover:bg-hkbp-800 text-hkbp-800 hover:text-white px-4 rounded-lg text-sm font-bold transition-colors duration-200">
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
                        <a href="{{ route('warta') }}" class="inline-flex items-center gap-1 py-2 text-sm font-bold text-hkbp-800 hover:text-hkbp-900 transition-colors group">
                            Lihat seluruh arsip warta
                            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" aria-hidden="true"></i>
                        </a>
                    </p>
                </div>

                <div class="min-w-0">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-hkbp-900 mb-8 flex items-center gap-3">
                        <span aria-hidden="true" class="w-9 h-9 rounded-lg bg-amber-50 text-gold-700 flex items-center justify-center ring-1 ring-inset ring-amber-100">
                            <i data-lucide="wallet" class="w-4.5 h-4.5"></i>
                        </span>
                        Kas Gereja
                    </h2>
                    <div class="bg-white p-6 rounded-2xl shadow-brand-sm border border-slate-200/80">
                        @if($total_pemasukan === 0 && $total_pengeluaran === 0)
                            <p class="text-slate-500 italic text-sm text-center py-10">Data keuangan belum tersedia.</p>
                        @else
                            <div class="relative mb-6 flex justify-between items-center bg-linear-to-br from-hkbp-900 to-hkbp-950 text-white p-5 rounded-xl gap-4 overflow-hidden">
                                <div aria-hidden="true" class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-white/5"></div>
                                <div class="relative">
                                    <p class="text-xs font-bold text-blue-200/90 uppercase tracking-wide">Saldo Kas</p>
                                    <p class="text-2xl font-black text-gold-400">Rp {{ number_format($saldo_akhir, 0, ',', '.') }}</p>
                                </div>
                                <i data-lucide="wallet" class="relative w-8 h-8 opacity-50 shrink-0" aria-hidden="true"></i>
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
    <section id="galeri" class="py-20 bg-slate-50 border-t border-slate-100 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Dokumentasi" judul="Galeri Kegiatan" class="mb-10" />

            <div class="swiper" data-swiper="galeri" data-swiper-delay="3000" aria-label="Galeri kegiatan gereja">
                <div class="swiper-wrapper">
                    @forelse($galeris as $item)
                        <div class="swiper-slide h-auto">
                            <figure class="group rounded-2xl overflow-hidden shadow-brand-sm border border-slate-200/80 hover:shadow-brand-lg transition-all duration-300 relative h-full flex flex-col bg-white">
                                <div class="w-full h-56 bg-slate-100 overflow-hidden relative">
                                    @if($item->foto)
                                        <img src="{{ $item->urlFoto() }}"
                                             @if($srcset = $item->srcsetFoto()) srcset="{{ $srcset }}" sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw" @endif
                                             alt="{{ $item->judul }}"
                                             width="400" height="224" loading="lazy" decoding="async"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <span aria-hidden="true" class="w-full h-full flex items-center justify-center">
                                            <i data-lucide="camera" class="w-8 h-8 text-slate-300"></i>
                                        </span>
                                    @endif
                                    <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-1/2 bg-linear-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
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
                <a href="{{ route('galeri') }}" class="inline-flex items-center gap-1 py-2 text-sm font-bold text-hkbp-800 hover:text-hkbp-900 transition-colors group">
                    Lihat seluruh dokumentasi
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" aria-hidden="true"></i>
                </a>
            </p>
        </div>
    </section>
</x-layout>
