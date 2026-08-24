<x-layout title="Renungan Harian - HKBP Persiapan Resort Volker">

    <!-- HERO -->
    <section class="relative bg-hkbp-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h1 class="text-3xl sm:text-4xl font-black mb-3">Renungan Harian</h1>
            <p class="text-blue-200 text-sm sm:text-base max-w-xl mx-auto">Santapan rohani dan firman Tuhan untuk menguatkan langkah iman setiap hari.</p>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="py-12 bg-slate-50 min-h-[70vh]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-3 gap-8 items-start">
                
                <!-- KOLOM KIRI: ISI RENUNGAN (2 Kolom) -->
                <div class="lg:col-span-2">
                    @if($renungan)
                        <article class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            @if($renungan->foto)
                                <img src="{{ asset('storage/' . $renungan->foto) }}" alt="{{ $renungan->judul }}" class="w-full h-72 sm:h-96 object-cover">
                            @endif

                            <div class="p-6 sm:p-10">
                                <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm font-bold mb-4">
                                    <span class="flex items-center gap-1.5 bg-blue-50 text-hkbp-800 px-3 py-1.5 rounded-lg border border-blue-100">
                                        <i data-lucide="calendar" class="w-4 h-4"></i>
                                        {{ \Carbon\Carbon::parse($renungan->tanggal)->translatedFormat('l, d F Y') }}
                                    </span>
                                    <span class="flex items-center gap-1.5 bg-slate-100 text-slate-700 px-3 py-1.5 rounded-lg">
                                        <i data-lucide="user" class="w-4 h-4"></i>
                                        {{ $renungan->penulis ?? 'Tim Pelayanan' }}
                                    </span>
                                </div>

                                <h2 class="text-2xl sm:text-3xl font-black text-hkbp-900 mb-6 leading-tight">{{ $renungan->judul }}</h2>

                                <div class="text-slate-700 leading-relaxed space-y-4 text-base sm:text-lg">
                                    {!! nl2br(e($renungan->isi)) !!}
                                </div>
                            </div>
                        </article>
                    @else
                        <!-- TAMPILAN JIKA TIDAK ADA RENUNGAN DI TANGGAL TERSEBUT -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
                            <div class="w-20 h-20 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-5">
                                <i data-lucide="book-x" class="w-10 h-10"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-800 mb-2">Tidak Ada Renungan</h3>
                            <p class="text-slate-500 text-sm max-w-md mx-auto mb-6">
                                Belum ada renungan yang dipublikasikan untuk tanggal 
                                <strong class="text-hkbp-900">{{ \Carbon\Carbon::parse($selected_date)->translatedFormat('d F Y') }}</strong>.
                            </p>
                            <a href="/renungan" class="inline-flex items-center gap-2 bg-hkbp-800 hover:bg-hkbp-900 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition">
                                <i data-lucide="calendar-check" class="w-4 h-4"></i> Lihat Renungan Hari Ini
                            </a>
                        </div>
                    @endif
                </div>

                <!-- KOLOM KANAN: WIDGET KALENDER & DAFTAR CEPAT (1 Kolom) -->
                <div class="space-y-6 lg:sticky lg:top-28">
                    
                    <!-- BOX KALENDER PEMILIH TANGGAL -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-black text-hkbp-900 text-base flex items-center gap-2">
                                <i data-lucide="calendar-days" class="w-5 h-5 text-gold-500"></i> Pilih Tanggal
                            </h3>
                            <a href="/renungan" class="text-xs font-bold text-hkbp-800 hover:underline">Hari Ini</a>
                        </div>

                        <form action="/renungan" method="GET" class="space-y-3">
                            <div>
                                <label for="date-picker" class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Renungan:</label>
                                <input type="date" 
                                       id="date-picker" 
                                       name="tanggal" 
                                       value="{{ $selected_date }}" 
                                       onchange="this.form.submit()"
                                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-hkbp-800 focus:outline-none cursor-pointer">
                            </div>
                            <button type="submit" class="w-full bg-hkbp-800 hover:bg-hkbp-900 text-white text-xs font-bold py-2.5 rounded-xl transition flex items-center justify-center gap-2">
                                <i data-lucide="search" class="w-4 h-4"></i> Tampilkan Renungan
                            </button>
                        </form>
                    </div>

                    <!-- BOX RENUNGAN TERBARU LAINNYA -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                        <h3 class="font-black text-hkbp-900 text-base mb-4 flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-5 h-5 text-gold-500"></i> Edisi Lainnya
                        </h3>
                        <div class="divide-y divide-slate-100">
                            @forelse($recent_renungans as $rec)
                                <a href="/renungan?tanggal={{ \Carbon\Carbon::parse($rec->tanggal)->format('Y-m-d') }}" 
                                   class="py-3 block group {{ \Carbon\Carbon::parse($rec->tanggal)->format('Y-m-d') === $selected_date ? 'font-bold text-hkbp-900' : 'text-slate-700' }}">
                                    <p class="text-[11px] font-semibold text-gold-600 mb-0.5">{{ \Carbon\Carbon::parse($rec->tanggal)->translatedFormat('d M Y') }}</p>
                                    <h4 class="text-sm group-hover:text-hkbp-800 transition line-clamp-1 leading-snug">{{ $rec->judul }}</h4>
                                </a>
                            @empty
                                <p class="text-xs text-slate-400 py-2">Belum ada renungan lain.</p>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

</x-layout>