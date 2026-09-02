<x-layout
    :title="'Arsip Renungan - ' . config('gereja.nama')"
    :description="'Cari dan baca arsip renungan dari ' . config('gereja.nama') . '.'">

    <x-page-hero ringkas judul="Arsip Renungan" deskripsi="Temukan kembali santapan rohani berdasarkan judul, penulis, isi, atau tahun." />

    <section class="py-14 bg-slate-50 min-h-[65vh]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('renungan.arsip') }}" method="GET" class="mb-8 bg-white border border-slate-200/80 shadow-brand-sm rounded-2xl p-4 flex flex-col sm:flex-row gap-3">
                <label for="q" class="sr-only">Cari renungan</label>
                <input id="q" name="q" value="{{ $cari }}" maxlength="60" placeholder="Cari judul, penulis, atau isi renungan…"
                       class="flex-1 min-h-11 rounded-xl border border-slate-300 px-4 text-base focus:outline-none focus:ring-2 focus:ring-hkbp-800/30 focus:border-hkbp-800">
                <label for="tahun" class="sr-only">Tahun</label>
                <select id="tahun" name="tahun" class="min-h-11 rounded-xl border border-slate-300 px-4 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-hkbp-800/30">
                    <option value="">Semua tahun</option>
                    @foreach($tahunTersedia as $opsi)
                        <option value="{{ $opsi }}" @selected($tahun === $opsi)>{{ $opsi }}</option>
                    @endforeach
                </select>
                <button class="min-h-11 px-5 rounded-xl bg-hkbp-800 hover:bg-hkbp-900 text-white font-bold text-sm inline-flex items-center justify-center gap-2">
                    <i data-lucide="search" class="w-4 h-4" aria-hidden="true"></i> Cari
                </button>
            </form>

            <div class="grid md:grid-cols-2 gap-5">
                @forelse($renungans as $item)
                    <article class="bg-white rounded-2xl border border-slate-200/80 shadow-brand-sm p-6 flex flex-col hover:shadow-brand transition-shadow">
                        <p class="text-xs font-bold text-gold-700"><time datetime="{{ $item->tanggal->toDateString() }}">{{ $item->tanggal->translatedFormat('d F Y') }}</time></p>
                        <h2 class="mt-2 text-xl font-black text-hkbp-900 text-balance">{{ $item->judul }}</h2>
                        <p class="mt-1 text-xs font-semibold text-slate-500">{{ $item->penulis ?: 'Tim Pelayanan' }}</p>
                        <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ $item->ringkasan(150) }}</p>
                        <a href="{{ route('renungan', ['tanggal' => $item->tanggal->toDateString()]) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-bold text-hkbp-800 hover:text-hkbp-900">
                            Baca renungan <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                    </article>
                @empty
                    <div class="md:col-span-2"><x-empty-state ikon="book-x" judul="Renungan tidak ditemukan" pesan="Coba kata pencarian atau tahun yang berbeda." /></div>
                @endforelse
            </div>

            @if($renungans->hasPages())
                <nav class="mt-10" aria-label="Navigasi halaman arsip renungan">{{ $renungans->links('vendor.pagination.tailwind') }}</nav>
            @endif
        </div>
    </section>
</x-layout>
