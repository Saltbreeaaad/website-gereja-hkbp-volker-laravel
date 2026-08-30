{{-- Tombol jeda/putar carousel. Disembunyikan sampai JS memasangnya, supaya
     tidak ada kontrol mati bila JavaScript gagal dimuat. --}}
<button type="button"
        data-swiper-toggle
        hidden
        aria-pressed="false"
        class="inline-flex items-center gap-1.5 h-11 px-4 text-xs font-bold text-slate-600 bg-white border border-slate-300 rounded-xl hover:border-hkbp-800 hover:text-hkbp-900 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-hkbp-800">
    <i data-lucide="pause" class="w-4 h-4" data-icon="pause" aria-hidden="true"></i>
    <i data-lucide="play" class="w-4 h-4 hidden" data-icon="play" aria-hidden="true"></i>
    <span class="sr-only">Putar otomatis: </span><span data-label>Jeda</span>
</button>
