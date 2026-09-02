@props(['kode', 'judul', 'pesan'])

<x-layout :title="$kode . ' - ' . $judul" :description="$pesan">
    <section class="min-h-[60vh] flex items-center justify-center bg-slate-50 px-4 py-24">
        <div class="text-center max-w-lg">
            <p class="text-7xl sm:text-8xl font-black text-hkbp-900/15 leading-none select-none">{{ $kode }}</p>
            <h1 class="mt-2 text-2xl sm:text-3xl font-black text-hkbp-900 text-balance">{{ $judul }}</h1>
            <p class="mt-4 text-slate-600 text-pretty">{{ $pesan }}</p>

            <div class="mt-8 flex flex-wrap gap-3 justify-center">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center gap-2 bg-linear-to-b from-hkbp-800 to-hkbp-900 hover:from-hkbp-700 hover:to-hkbp-800 text-white text-sm font-bold px-5 py-3 rounded-xl shadow-brand-sm transition-all duration-200 hover:-translate-y-0.5">
                    <i data-lucide="cross" class="w-4 h-4" aria-hidden="true"></i> Kembali ke Beranda
                </a>
                <a href="{{ route('penggunaan-gereja') }}"
                   class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:border-hkbp-800 text-slate-700 text-sm font-bold px-5 py-3 rounded-xl transition-all duration-200 hover:-translate-y-0.5">
                    <i data-lucide="calendar-days" class="w-4 h-4" aria-hidden="true"></i> Jadwal Gereja
                </a>
            </div>
        </div>
    </section>
</x-layout>
