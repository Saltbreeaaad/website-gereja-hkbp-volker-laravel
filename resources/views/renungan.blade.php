<x-layout
    :title="($renungan?->judul ? $renungan->judul . ' - Renungan' : 'Renungan Harian') . ' - ' . config('gereja.nama')"
    :description="$renungan?->ringkasan(160) ?: 'Renungan harian dan santapan rohani dari ' . config('gereja.nama') . '.'"
    :image="$renungan?->foto ? Storage::disk('public')->url($renungan->foto) : null">

    <x-page-hero
        ringkas
        judul="Renungan Harian"
        deskripsi="Santapan rohani dan firman Tuhan untuk menguatkan langkah iman setiap hari." />

    <section class="py-12 bg-slate-50 min-h-[70vh]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-3 gap-8 items-start">

                {{-- ISI RENUNGAN --}}
                <div class="lg:col-span-2">
                    @if($renungan)
                        <article class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            @if($renungan->foto)
                                <img src="{{ Storage::disk('public')->url($renungan->foto) }}"
                                     alt="Ilustrasi renungan: {{ $renungan->judul }}"
                                     width="900" height="480" fetchpriority="high" decoding="async"
                                     class="w-full h-72 sm:h-96 object-cover">
                            @endif

                            <div class="p-6 sm:p-10">
                                <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm font-bold mb-4">
                                    <span class="flex items-center gap-1.5 bg-blue-50 text-hkbp-800 px-3 py-1.5 rounded-lg border border-blue-100">
                                        <i data-lucide="calendar" class="w-4 h-4" aria-hidden="true"></i>
                                        <time datetime="{{ $renungan->tanggal->toDateString() }}">{{ $renungan->tanggal->translatedFormat('l, d F Y') }}</time>
                                    </span>
                                    <span class="flex items-center gap-1.5 bg-slate-100 text-slate-700 px-3 py-1.5 rounded-lg">
                                        <i data-lucide="user" class="w-4 h-4" aria-hidden="true"></i>
                                        {{ $renungan->penulis ?: 'Tim Pelayanan' }}
                                    </span>
                                </div>

                                <h1 class="text-2xl sm:text-3xl font-black text-hkbp-900 mb-6 leading-tight text-balance">{{ $renungan->judul }}</h1>

                                <div class="text-slate-700 leading-relaxed space-y-4 text-base sm:text-lg">
                                    {!! nl2br(e($renungan->isi)) !!}
                                </div>
                            </div>
                        </article>
                    @else
                        <x-empty-state
                            ikon="book-x"
                            judul="Tidak Ada Renungan"
                            :pesan="'Belum ada renungan yang dipublikasikan untuk tanggal ' . \Illuminate\Support\Carbon::parse($selected_date)->translatedFormat('d F Y') . '.'">
                            <a href="{{ route('renungan') }}"
                               class="inline-flex items-center justify-center min-h-11 gap-2 bg-hkbp-800 hover:bg-hkbp-900 text-white text-sm font-bold px-5 rounded-xl transition-colors">
                                <i data-lucide="calendar-check" class="w-4 h-4" aria-hidden="true"></i> Lihat Renungan Hari Ini
                            </a>
                        </x-empty-state>
                    @endif

                    {{-- Navigasi edisi sebelum / sesudah tanggal yang sedang dibuka --}}
                    @if($sebelumnya || $berikutnya)
                        <nav aria-label="Navigasi antar edisi renungan" data-no-print
                             class="mt-6 grid sm:grid-cols-2 gap-4">
                            @if($sebelumnya)
                                <a href="{{ route('renungan', ['tanggal' => $sebelumnya->tanggal->toDateString()]) }}"
                                   class="group bg-white border border-slate-200 rounded-2xl p-4 hover:border-hkbp-800 transition-colors">
                                    <span class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500 uppercase tracking-wide">
                                        <i data-lucide="chevron-left" class="w-3.5 h-3.5" aria-hidden="true"></i> Edisi sebelumnya
                                    </span>
                                    <span class="block mt-1 text-sm font-bold text-hkbp-900 line-clamp-1 group-hover:text-hkbp-700">{{ $sebelumnya->judul }}</span>
                                    <span class="block text-xs text-slate-500">{{ $sebelumnya->tanggal->translatedFormat('d M Y') }}</span>
                                </a>
                            @else
                                <span aria-hidden="true"></span>
                            @endif

                            @if($berikutnya)
                                <a href="{{ route('renungan', ['tanggal' => $berikutnya->tanggal->toDateString()]) }}"
                                   class="group bg-white border border-slate-200 rounded-2xl p-4 hover:border-hkbp-800 transition-colors sm:text-right">
                                    <span class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500 uppercase tracking-wide sm:justify-end">
                                        Edisi berikutnya <i data-lucide="chevron-right" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                    </span>
                                    <span class="block mt-1 text-sm font-bold text-hkbp-900 line-clamp-1 group-hover:text-hkbp-700">{{ $berikutnya->judul }}</span>
                                    <span class="block text-xs text-slate-500">{{ $berikutnya->tanggal->translatedFormat('d M Y') }}</span>
                                </a>
                            @endif
                        </nav>
                    @endif
                </div>

                {{-- SIDEBAR --}}
                <aside class="space-y-6 lg:sticky lg:top-28" data-no-print>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between mb-4 gap-3">
                            <h2 class="font-black text-hkbp-900 text-base flex items-center gap-2">
                                <i data-lucide="calendar-days" class="w-5 h-5 text-gold-700" aria-hidden="true"></i> Pilih Tanggal
                            </h2>
                            <a href="{{ route('renungan') }}" class="inline-block py-2 px-1 -mr-1 text-xs font-bold text-hkbp-800 hover:underline shrink-0">Hari Ini</a>
                        </div>

                        <form action="{{ route('renungan') }}" method="GET" class="space-y-3">
                            <div>
                                <label for="date-picker" class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal renungan</label>
                                <input type="date"
                                       id="date-picker"
                                       name="tanggal"
                                       value="{{ $selected_date }}"
                                       data-auto-submit
                                       {{-- text-base di perangkat sentuh: font < 16px membuat iOS Safari
                                            memperbesar halaman saat input difokus. Patokannya jenis
                                            penunjuk, bukan lebar layar — lihat x-field. --}}
                                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm pointer-coarse:text-base font-bold text-slate-800 focus:ring-2 focus:ring-hkbp-800 focus:border-hkbp-800 focus:outline-none cursor-pointer">
                            </div>
                            <button type="submit" class="w-full min-h-11 bg-hkbp-800 hover:bg-hkbp-900 text-white text-xs font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                                <i data-lucide="search" class="w-4 h-4" aria-hidden="true"></i> Tampilkan Renungan
                            </button>
                        </form>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                        <h2 class="font-black text-hkbp-900 text-base mb-4 flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-5 h-5 text-gold-700" aria-hidden="true"></i> Edisi Lainnya
                        </h2>

                        <ul class="divide-y divide-slate-100">
                            @forelse($recent_renungans as $rec)
                                @php $aktif = $rec->tanggal->toDateString() === $selected_date @endphp
                                <li>
                                    <a href="{{ route('renungan', ['tanggal' => $rec->tanggal->toDateString()]) }}"
                                       @if($aktif) aria-current="page" @endif
                                       class="py-3 block group {{ $aktif ? 'font-bold text-hkbp-900' : 'text-slate-700' }}">
                                        <span class="block text-[11px] font-semibold text-gold-700 mb-0.5">{{ $rec->tanggal->translatedFormat('d M Y') }}</span>
                                        <span class="block text-sm group-hover:text-hkbp-800 transition-colors line-clamp-1 leading-snug">{{ $rec->judul }}</span>
                                    </a>
                                </li>
                            @empty
                                <li class="text-xs text-slate-500 py-2">Belum ada renungan lain.</li>
                            @endforelse
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</x-layout>
