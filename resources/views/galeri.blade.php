<x-layout title="Galeri - HKBP Persiapan Resort Volker">
    <section class="relative bg-hkbp-900 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h1 class="text-4xl sm:text-5xl font-black mb-4">Galeri Kegiatan</h1>
            <p class="text-lg text-blue-200 max-w-2xl mx-auto">Dokumentasi momen kebersamaan dan pelayanan di gereja kita.</p>
        </div>
    </section>

    <section class="py-16 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($galeris as $item)

                <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-200 flex flex-col group hover:shadow-xl transition-all duration-300">
                    <div class="w-full h-64 overflow-hidden relative bg-slate-100 border-b border-slate-200">
                        @if($item->foto)
                            <img src="{{ asset('storage/' . $item->foto) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center group-hover:scale-105 transition-transform duration-500">
                                <i data-lucide="camera" class="w-10 h-10 text-slate-300 mb-2"></i>
                                <span class="text-xs font-semibold text-slate-400">Belum ada foto</span>
                            </div>
                        @endif
                    </div>

                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-center gap-1.5 text-gold-500 text-xs font-bold mb-3">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            {{ $item->tanggal->translatedFormat('d F Y') }}
                        </div>
                        <h3 class="text-hkbp-900 font-bold text-xl leading-snug group-hover:text-hkbp-700 transition-colors">{{ $item->judul }}</h3>
                    </div>
                </div>

                @empty
                <div class="col-span-full text-center py-20 text-slate-500">
                    Belum ada foto galeri yang diunggah.
                </div>
                @endforelse
            </div>

            @if($galeris->hasPages())
                <div class="mt-10">
                    {{ $galeris->links() }}
                </div>
            @endif
            
        </div>
    </section>
</x-layout>