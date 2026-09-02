<x-layout title="Sedang Offline - {{ config('gereja.nama') }}" description="Halaman offline {{ config('gereja.nama') }}.">
    <section class="min-h-[65vh] bg-slate-50 flex items-center">
        <div class="max-w-xl mx-auto px-4 py-16 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-hkbp-900 text-gold-400 flex items-center justify-center">
                <i data-lucide="wifi-off" class="w-8 h-8" aria-hidden="true"></i>
            </div>
            <h1 class="mt-6 text-3xl font-black text-hkbp-950">Koneksi sedang terputus</h1>
            <p class="mt-3 text-slate-600 leading-relaxed">Beberapa halaman yang pernah dibuka masih dapat dibaca. Sambungkan kembali internet untuk memperoleh jadwal dan informasi terbaru.</p>
            <button type="button" data-reload class="mt-6 min-h-11 px-5 rounded-xl bg-hkbp-800 text-white font-bold">Coba lagi</button>
        </div>
    </section>
</x-layout>
