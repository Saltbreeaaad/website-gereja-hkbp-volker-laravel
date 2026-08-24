<x-layout title="HKBP Persiapan Resort Volker">

    <!-- HERO -->
    <section id="beranda" class="relative bg-linear-to-br from-hkbp-900 via-hkbp-800 to-hkbp-600 text-white min-h-100 flex items-center justify-center text-center">
        <div class="relative z-10 px-4">
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight text-white">
                Huria Kristen Batak Protestan <br>
                <span class="text-transparent bg-clip-text bg-linear-to-r from-gold-400 via-amber-300 to-white">Persiapan Resort Volker</span>
            </h1>
            <p class="mt-4 italic font-serif">"Menjadi Gereja yang Inklusif, Dialogis, dan Menjadi Berkat"</p>
        </div>
    </section>

    <!-- PELAYAN (CAROUSEL) -->
    <section id="pelayan" class="py-16 bg-white border-b border-slate-200 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-10">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-hkbp-900">Pelayan & Pengurus</h2>
            </div>
            <div class="swiper pelayanSwiper pb-12">
                <div class="swiper-wrapper">
                    @forelse($parhalados as $person)
                    <div class="swiper-slide h-auto">
                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 shadow-sm text-center h-full flex flex-col justify-center items-center group hover:border-hkbp-800/30 transition-all">
                            @if($person->foto)
                                <img src="{{ asset('storage/' . $person->foto) }}" alt="{{ $person->nama }}" class="w-20 h-20 mx-auto rounded-full object-cover border-4 border-white shadow-md mb-4 group-hover:scale-105 transition-transform">
                            @else
                                <div class="w-20 h-20 mx-auto rounded-full bg-hkbp-900 text-white flex items-center justify-center font-bold text-xl border-4 border-white shadow-md mb-4 group-hover:scale-105 transition-transform uppercase">{{ substr($person->nama, 0, 2) }}</div>
                            @endif
                            <span class="inline-block bg-blue-100 text-hkbp-800 text-[10px] font-bold px-2 py-1 rounded-md mb-2 uppercase">{{ $person->kategori }}</span>
                            <h4 class="font-bold text-base text-hkbp-900 leading-tight">{{ $person->nama }}</h4>
                            <p class="text-xs text-hkbp-700 font-semibold mt-1">{{ $person->jabatan }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="swiper-slide"><p class="text-slate-500 italic text-center w-full">Belum ada data pelayan.</p></div>
                    @endforelse
                </div>
                <div class="swiper-pagination-pelayan text-center mt-4"></div>
            </div>
            <div class="text-center mt-4">
                <a href="/pelayan" class="text-sm font-bold text-hkbp-800 hover:text-hkbp-900 underline underline-offset-4 transition">Lihat Profil Lengkap Pelayan &rarr;</a>
            </div>
        </div>
    </section>

    <!-- TIMELINE RINGKAS (High Contrast) -->
    <section class="py-16 bg-white border-y border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl font-bold text-hkbp-900">Perjalanan Pelayanan Kami</h2>
            </div>
            
            <div class="relative grid md:grid-cols-3 gap-8">
                <!-- Garis Penghubung (Lebih Tebal & Gelap) -->
                <div class="hidden md:block absolute top-7 left-0 w-full border-t-4 border-slate-300 z-0"></div>

                <!-- Item 1 -->
                <div class="relative z-10 bg-white p-4 text-center">
                    <div class="w-14 h-14 bg-hkbp-900 rounded-full mx-auto flex items-center justify-center text-gold-400 mb-4 border-4 border-white shadow-[0_0_15px_rgba(0,0,0,0.1)]">
                        <i data-lucide="sparkles" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-black text-hkbp-900 text-lg">2020</h4>
                    <p class="text-sm font-semibold text-slate-600 mt-1">Awal Perintisan</p>
                </div>

                <!-- Item 2 -->
                <div class="relative z-10 bg-white p-4 text-center">
                    <div class="w-14 h-14 bg-hkbp-800 rounded-full mx-auto flex items-center justify-center text-gold-400 mb-4 border-4 border-white shadow-[0_0_15px_rgba(0,0,0,0.1)]">
                        <i data-lucide="cross" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-black text-hkbp-900 text-lg">2023</h4>
                    <p class="text-sm font-semibold text-slate-600 mt-1">Ditetapkan Resort</p>
                </div>

                <!-- Item 3 -->
                <div class="relative z-10 bg-white p-4 text-center">
                    <div class="w-14 h-14 bg-gold-500 rounded-full mx-auto flex items-center justify-center text-hkbp-900 mb-4 border-4 border-white shadow-[0_0_15px_rgba(0,0,0,0.1)]">
                        <i data-lucide="map-pin" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-black text-hkbp-900 text-lg">2026</h4>
                    <p class="text-sm font-semibold text-slate-600 mt-1">Peningkatan Pelayanan</p>
                </div>
            </div>
        </div>
    </section>

    <!-- JADWAL IBADAH -->
    <section id="jadwal" class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-hkbp-900">Jadwal Ibadah</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($jadwal_ibadah as $jadwal)
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-all border-l-4 border-l-gold-500">
                    <div class="flex justify-between items-start mb-4">
                        <div class="bg-blue-50 text-hkbp-800 text-xs font-bold px-3 py-1.5 rounded-lg">{{ date('d F Y', strtotime($jadwal->tanggal)) }}</div>
                        <div class="flex items-center text-slate-500 text-sm font-semibold gap-1"><i data-lucide="clock" class="w-4 h-4"></i> {{ date('H:i', strtotime($jadwal->waktu)) }} WIB</div>
                    </div>
                    <h3 class="text-xl font-bold text-hkbp-900 mb-4">{{ $jadwal->nama_ibadah }}</h3>
                    <div class="space-y-2">
                        @if($jadwal->pelayan_firman)
                        <div class="flex items-center gap-2 text-sm text-slate-600"><i data-lucide="user" class="w-4 h-4 text-hkbp-600"></i><span>Pelayan: <strong>{{ $jadwal->pelayan_firman }}</strong></span></div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center text-slate-500 py-10">Belum ada jadwal ibadah yang tersedia saat ini.</div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- RENUNGAN TERBARU -->
    <section id="renungan" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-hkbp-900">Renungan Terbaru</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                @forelse($renungans as $item)
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col h-full hover:shadow-md transition">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 mb-3">
                        <i data-lucide="calendar" class="w-4 h-4"></i> {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                    </div>
                    <h3 class="text-xl font-bold text-hkbp-900 mb-3 line-clamp-2">{{ $item->judul }}</h3>
                    <p class="text-slate-600 text-sm mb-6 line-clamp-3 leading-relaxed">{{ $item->isi }}</p>
                    <a href="/renungan" class="mt-auto text-sm font-bold text-hkbp-700 hover:text-hkbp-900 flex items-center gap-1 transition">
                        Baca selengkapnya <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
                @empty
                <div class="col-span-full text-center text-slate-500 py-10">Belum ada renungan.</div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- WARTA & KEUANGAN -->
    <section id="warta-keuangan" class="py-20 bg-slate-50 border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16">
                
                <!-- Warta Jemaat -->
                <div>
                    <h2 class="text-3xl font-extrabold text-hkbp-900 mb-8">Warta Jemaat</h2>
                    <div class="space-y-4">
                        @forelse($warta as $item)
                        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-center justify-between group hover:shadow-md transition">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-blue-50 text-hkbp-800 rounded-lg flex items-center justify-center"><i data-lucide="file-text" class="w-6 h-6"></i></div>
                                <div>
                                    <h3 class="font-bold text-hkbp-900">{{ $item->judul }}</h3>
                                    <p class="text-xs text-slate-500">{{ date('d F Y', strtotime($item->tanggal)) }}</p>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $item->file_warta) }}" target="_blank" class="bg-hkbp-100 hover:bg-hkbp-800 text-hkbp-800 hover:text-white px-4 py-2 rounded-lg text-sm font-bold transition">Unduh</a>
                        </div>
                        @empty
                        <p class="text-slate-500 italic">Belum ada warta jemaat.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Keuangan -->
                <div>
                    <h2 class="text-3xl font-extrabold text-hkbp-900 mb-8">Kas Gereja</h2>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        @if($total_pemasukan == 0 && $total_pengeluaran == 0)
                            <p class="text-slate-500 italic text-sm text-center py-10">Data keuangan belum tersedia.</p>
                        @else
                            <div class="mb-6 flex justify-between items-center bg-hkbp-900 text-white p-4 rounded-xl">
                                <div><p class="text-xs font-bold text-blue-200 uppercase">Saldo Kas</p><h3 class="text-2xl font-black text-gold-400">Rp {{ number_format($saldo_akhir, 0, ',', '.') }}</h3></div>
                                <i data-lucide="wallet" class="w-8 h-8 opacity-50"></i>
                            </div>
                            <canvas id="kasChart" class="w-full max-h-62.5"></canvas>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- GALERI (CAROUSEL) -->
    <section id="galeri" class="py-20 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-10">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-hkbp-900">Galeri Kegiatan</h2>
            </div>
            
            <div class="swiper galeriSwiper pb-12">
                <div class="swiper-wrapper">
                    @forelse($galeris as $item)
                    <div class="swiper-slide h-auto">
                        <div class="group rounded-2xl overflow-hidden shadow-sm border border-slate-200 hover:shadow-md transition-all duration-300 relative h-full flex flex-col">
                            <div class="w-full h-56 bg-slate-100 overflow-hidden relative">
                                @if($item->foto)
                                    <img src="{{ asset('storage/' . $item->foto) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center"><i data-lucide="camera" class="w-8 h-8 text-slate-300"></i></div>
                                @endif
                            </div>
                            <div class="p-4 flex-1 bg-white">
                                <p class="text-gold-500 text-[10px] font-bold mb-1">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}</p>
                                <h3 class="text-hkbp-900 font-bold text-sm leading-tight">{{ $item->judul }}</h3>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="swiper-slide"><p class="text-slate-500 italic text-center w-full">Belum ada foto galeri.</p></div>
                    @endforelse
                </div>
                <div class="swiper-pagination-galeri text-center mt-4"></div>
            </div>
            
            <div class="text-center mt-4">
                <a href="/galeri" class="text-sm font-bold text-hkbp-800 hover:text-hkbp-900 underline underline-offset-4 transition">Lihat Seluruh Dokumentasi &rarr;</a>
            </div>
        </div>
    </section>

    <!-- SCRIPTS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('.pelayanSwiper')) {
                new Swiper('.pelayanSwiper', {
                    slidesPerView: 1, spaceBetween: 20, loop: true,
                    autoplay: { delay: 2500, disableOnInteraction: false },
                    pagination: { el: '.swiper-pagination-pelayan', clickable: true },
                    breakpoints: { 640: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 4 } }
                });
            }

            if (document.querySelector('.galeriSwiper')) {
                new Swiper('.galeriSwiper', {
                    slidesPerView: 1, spaceBetween: 20, loop: true,
                    autoplay: { delay: 3000, disableOnInteraction: false },
                    pagination: { el: '.swiper-pagination-galeri', clickable: true },
                    breakpoints: { 640: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 4 } }
                });
            }

            if({{ $total_pemasukan }} > 0 || {{ $total_pengeluaran }} > 0) {
                new Chart(document.getElementById('kasChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Pemasukan', 'Pengeluaran'],
                        datasets: [{ data: [{{ $total_pemasukan }}, {{ $total_pengeluaran }}], backgroundColor: ['#10b981', '#ef4444'], borderWidth: 0 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
                });
            }
        });
    </script>
</x-layout>