<x-layout title="Permohonan Doa - {{ config('gereja.nama') }}" description="Kirimkan pokok doa secara privat kepada tim pelayanan {{ config('gereja.nama') }}.">
    <x-page-hero judul="Kami Mendoakan Anda" deskripsi="Sampaikan pokok doa dengan aman dan privat. Anda boleh menuliskan nama atau mengirimkannya tanpa identitas." ringkas />

    <section class="py-12 sm:py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-[0.9fr_1.1fr] gap-8 lg:gap-12 items-start">
            <aside class="rounded-3xl bg-linear-to-br from-hkbp-950 to-hkbp-800 text-white p-7 sm:p-9 shadow-brand">
                <span class="w-12 h-12 rounded-2xl bg-white/10 grid place-items-center text-gold-300"><i data-lucide="heart-handshake" class="w-6 h-6" aria-hidden="true"></i></span>
                <h2 class="mt-6 text-2xl font-black">Ruang yang aman untuk berbagi.</h2>
                <p class="mt-4 leading-relaxed text-blue-100">Pokok doa hanya dapat dilihat oleh tim pengurus yang berwenang. Kami tidak menampilkan permohonan ini di situs publik.</p>
                <ul class="mt-7 space-y-4 text-sm text-blue-100">
                    <li class="flex gap-3"><i data-lucide="shield-check" class="w-5 h-5 shrink-0 text-gold-300" aria-hidden="true"></i> Tanpa publikasi otomatis.</li>
                    <li class="flex gap-3"><i data-lucide="user-round-x" class="w-5 h-5 shrink-0 text-gold-300" aria-hidden="true"></i> Nama dan kontak bersifat opsional.</li>
                    <li class="flex gap-3"><i data-lucide="heart" class="w-5 h-5 shrink-0 text-gold-300" aria-hidden="true"></i> Doa Anda akan diteruskan dengan hormat.</li>
                </ul>
            </aside>

            <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 shadow-brand-sm">
                @if(session('success'))
                    <div role="status" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
                @endif
                <form action="{{ route('doa.store') }}" method="post" class="space-y-5">
                    @csrf
                    <div class="grid sm:grid-cols-2 gap-5">
                        <x-field nama="nama" label="Nama (opsional)" autocomplete="name" />
                        <x-field nama="kontak" label="Kontak (opsional)" petunjuk="Hanya bila Anda ingin dihubungi." autocomplete="tel" />
                    </div>
                    <x-field nama="isi" label="Pokok doa" tipe="textarea" :baris="7" wajib placeholder="Tuliskan hal yang ingin didoakan..." />
                    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                    <button class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-linear-to-b from-hkbp-800 to-hkbp-900 px-5 py-3.5 text-sm font-black text-white shadow-brand hover:shadow-brand-lg transition-shadow">
                        <i data-lucide="send" class="w-4 h-4" aria-hidden="true"></i> Kirim pokok doa secara privat
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layout>
