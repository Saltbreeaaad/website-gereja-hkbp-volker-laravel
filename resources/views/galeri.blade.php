<x-layout
    title="Galeri Kegiatan - {{ config('gereja.nama') }}"
    description="Dokumentasi foto kegiatan, ibadah, dan pelayanan di {{ config('gereja.nama') }}.">

    <x-page-hero
        judul="Galeri Kegiatan"
        deskripsi="Dokumentasi momen kebersamaan dan pelayanan di gereja kita." />

    <section class="py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-filter-tahun rute="galeri" :tahun="$tahun" :tersedia="$tahunTersedia" label="Tahun kegiatan" />

            <form action="{{ route('galeri') }}" method="GET" class="mb-8 grid sm:grid-cols-[1fr_12rem_auto] gap-3">
                @if($tahun)<input type="hidden" name="tahun" value="{{ $tahun }}">@endif
                <label for="cari-galeri" class="sr-only">Cari foto</label>
                <input id="cari-galeri" name="q" value="{{ $cari }}" maxlength="60" placeholder="Cari kegiatan…"
                       class="min-w-0 min-h-11 rounded-xl border border-slate-300 bg-white px-4 text-base focus:outline-none focus:ring-2 focus:ring-hkbp-800/30 focus:border-hkbp-800">
                <label for="kategori" class="sr-only">Kategori galeri</label>
                <select id="kategori" name="kategori" class="min-h-11 rounded-xl border border-slate-300 bg-white px-4 text-slate-700 focus:outline-none focus:ring-2 focus:ring-hkbp-800/30">
                    <option value="">Semua kategori</option>
                    @foreach($kategoriTersedia as $opsi)
                        <option value="{{ $opsi }}" @selected($kategori === $opsi)>{{ $opsi }}</option>
                    @endforeach
                </select>
                <button class="min-h-11 px-5 rounded-xl bg-hkbp-800 text-white font-bold text-sm inline-flex justify-center items-center gap-2 hover:bg-hkbp-900">
                    <i data-lucide="search" class="w-4 h-4" aria-hidden="true"></i> Cari
                </button>
            </form>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($galeris as $item)
                    @php $url = $item->urlFoto() @endphp

                    <figure class="bg-white rounded-2xl overflow-hidden shadow-brand-sm border border-slate-200/80 flex flex-col group hover:shadow-brand-lg hover:-translate-y-1 transition-all duration-300">
                        <div class="w-full h-64 overflow-hidden relative bg-slate-100 border-b border-slate-200/80">
                            @if($url)
                                {{-- Tombol, bukan div: foto bisa diperbesar lewat keyboard maupun sentuh. --}}
                                <button type="button"
                                        data-lightbox="{{ $url }}"
                                        data-lightbox-caption="{{ $item->judul }}"
                                        class="w-full h-full block cursor-zoom-in fokus-dalam">
                                    <img src="{{ $url }}"
                                         @if($srcset = $item->srcsetFoto()) srcset="{{ $srcset }}" sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw" @endif
                                         alt="{{ $item->judul }}"
                                         width="600" height="256"
                                         loading="{{ $loop->index < 3 ? 'eager' : 'lazy' }}"
                                         decoding="async"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">

                                    <span class="absolute inset-0 bg-hkbp-950/0 group-hover:bg-hkbp-950/25 transition-colors flex items-center justify-center">
                                        <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-white/95 text-hkbp-900 rounded-full p-3 shadow-lg">
                                            <i data-lucide="zoom-in" class="w-5 h-5" aria-hidden="true"></i>
                                        </span>
                                    </span>
                                    <span class="sr-only">Perbesar foto: {{ $item->judul }}</span>
                                </button>
                            @else
                                <span aria-hidden="true" class="w-full h-full flex flex-col items-center justify-center">
                                    <i data-lucide="camera" class="w-10 h-10 text-slate-300 mb-2"></i>
                                    <span class="text-xs font-semibold text-slate-500">Belum ada foto</span>
                                </span>
                            @endif
                        </div>

                        <figcaption class="p-6 flex-1 flex flex-col">
                            <p class="flex items-center gap-1.5 text-gold-700 text-xs font-bold mb-3">
                                <i data-lucide="calendar" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                <time datetime="{{ $item->tanggal->toDateString() }}">{{ $item->tanggal->translatedFormat('d F Y') }}</time>
                            </p>
                            <p class="text-[11px] font-bold text-hkbp-700 uppercase tracking-wide mb-2">{{ $item->kategori }}</p>
                            <h2 class="text-hkbp-900 font-bold text-xl leading-snug text-balance group-hover:text-hkbp-700 transition-colors">{{ $item->judul }}</h2>
                        </figcaption>
                    </figure>
                @empty
                    <div class="col-span-full">
                        <x-empty-state
                            ikon="camera"
                            :judul="($cari || $kategori) ? 'Foto tidak ditemukan' : ($tahun ? 'Tidak ada foto pada tahun ' . $tahun : 'Galeri masih kosong')"
                            :pesan="($cari || $kategori) ? 'Coba pencarian atau kategori yang berbeda.' : ($tahun
                                ? 'Belum ada dokumentasi kegiatan pada tahun tersebut.'
                                : 'Belum ada foto kegiatan yang diunggah. Silakan kembali lagi nanti.')">
                            @if($tahun)
                                <a href="{{ route('galeri') }}"
                                   class="inline-flex items-center justify-center min-h-11 gap-2 bg-hkbp-800 hover:bg-hkbp-900 text-white text-sm font-bold px-5 rounded-xl transition-colors">
                                    Lihat seluruh galeri
                                </a>
                            @endif
                        </x-empty-state>
                    </div>
                @endforelse
            </div>

            @if($galeris->hasPages())
                <nav class="mt-10" aria-label="Navigasi halaman galeri">
                    {{-- Livewire mengganti view paginasi global saat panel admin
                         dirender. Pilih view publik secara eksplisit agar tautan
                         tetap berupa <a> biasa dan tidak berubah menjadi tombol
                         wire:click yang tidak bekerja di halaman publik. --}}
                    {{ $galeris->links('vendor.pagination.tailwind') }}
                </nav>
            @endif
        </div>
    </section>

    {{-- Lightbox: <dialog> bawaan browser sudah menangani penjebakan fokus,
         tutup dengan Escape, dan latar modal. --}}
    <dialog id="lightbox"
            aria-label="Pratinjau foto"
            class="bg-transparent p-4 max-w-5xl w-full max-h-full">
        <div class="relative bg-white rounded-2xl overflow-hidden shadow-2xl">
            <form method="dialog">
                <button type="submit"
                        class="absolute top-3 right-3 z-10 w-11 h-11 inline-flex items-center justify-center rounded-xl bg-white/90 text-hkbp-900 hover:bg-white shadow-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-hkbp-800">
                    <i data-lucide="x" class="w-5 h-5" aria-hidden="true"></i>
                    <span class="sr-only">Tutup pratinjau</span>
                </button>
            </form>

            <img data-lightbox-image alt="" class="w-full max-h-[75vh] object-contain bg-slate-100">

            <p data-lightbox-caption class="p-5 font-bold text-hkbp-900 text-center text-balance"></p>
        </div>
    </dialog>
</x-layout>
