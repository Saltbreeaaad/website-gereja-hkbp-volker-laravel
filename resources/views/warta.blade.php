<x-layout
    :title="'Arsip Warta Jemaat - ' . config('gereja.nama')"
    :description="'Arsip warta jemaat ' . config('gereja.nama') . ' yang dapat dibaca dan diunduh, tersusun dari terbaru ke terlama.'">

    <x-page-hero
        judul="Arsip Warta Jemaat"
        deskripsi="Seluruh warta jemaat yang pernah diterbitkan, tersusun dari yang terbaru. Silakan dibaca atau diunduh." />

    <section class="py-16 bg-slate-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <x-filter-tahun rute="warta" :tahun="$tahun" :tersedia="$tahunTersedia" label="Tahun terbit" />

            {{-- DAFTAR WARTA --}}
            <ul class="space-y-4">
                @forelse($wartas as $item)
                    @php $unduhan = $item->urlUnduhan() @endphp
                    <li>
                        {{-- Bertumpuk di ponsel: sebaris dengan tombol unduh yang tidak
                             bisa menyusut, judulnya terpotong di layar sempit. --}}
                        <article class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4 min-w-0">
                                <span class="w-12 h-12 bg-blue-50 text-hkbp-800 rounded-xl flex items-center justify-center shrink-0">
                                    <i data-lucide="file-text" class="w-6 h-6" aria-hidden="true"></i>
                                </span>
                                <div class="min-w-0">
                                    <h2 class="font-bold text-hkbp-900 text-lg leading-snug text-balance">{{ $item->judul }}</h2>
                                    <p class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 mt-1">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                        <time datetime="{{ $item->tanggal->toDateString() }}">{{ $item->tanggal->translatedFormat('l, d F Y') }}</time>
                                    </p>
                                </div>
                            </div>

                            @if($unduhan)
                                <div class="flex gap-2 shrink-0">
                                    <a href="{{ $unduhan }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-2 min-h-11 bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 rounded-xl text-sm font-bold transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4" aria-hidden="true"></i>
                                        Baca<span class="sr-only"> warta {{ $item->judul }}</span>
                                    </a>
                                    <a href="{{ $unduhan }}" download
                                       class="inline-flex items-center gap-2 min-h-11 bg-hkbp-800 hover:bg-hkbp-900 text-white px-4 rounded-xl text-sm font-bold transition-colors">
                                        <i data-lucide="arrow-right" class="w-4 h-4 rotate-90" aria-hidden="true"></i>
                                        Unduh<span class="sr-only"> warta {{ $item->judul }}</span>
                                    </a>
                                </div>
                            @else
                                <span class="shrink-0 text-xs font-semibold text-slate-500 italic">Berkas belum tersedia</span>
                            @endif
                        </article>
                    </li>
                @empty
                    <li>
                        <x-empty-state
                            ikon="file-text"
                            :judul="$tahun ? 'Tidak ada warta pada tahun ' . $tahun : 'Arsip masih kosong'"
                            :pesan="$tahun
                                ? 'Belum ada warta jemaat yang diterbitkan pada tahun tersebut.'
                                : 'Belum ada warta jemaat yang diunggah. Silakan kembali lagi nanti.'">
                            @if($tahun)
                                <a href="{{ route('warta') }}"
                                   class="inline-flex items-center justify-center min-h-11 gap-2 bg-hkbp-800 hover:bg-hkbp-900 text-white text-sm font-bold px-5 rounded-xl transition-colors">
                                    Lihat seluruh arsip
                                </a>
                            @endif
                        </x-empty-state>
                    </li>
                @endforelse
            </ul>

            @if($wartas->hasPages())
                <nav class="mt-10" aria-label="Navigasi halaman arsip warta">
                    {{ $wartas->links() }}
                </nav>
            @endif
        </div>
    </section>
</x-layout>
