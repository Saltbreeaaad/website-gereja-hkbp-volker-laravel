<x-layout
    title="Profil & Sejarah - {{ config('gereja.nama') }}"
    description="Sejarah perintisan, visi, dan misi {{ config('gereja.nama') }} sejak 2020 hingga kini.">

    <x-page-hero
        judul="Sejarah & Profil Gereja"
        deskripsi="Perjalanan iman dan pelayanan {{ config('gereja.nama') }}." />

    {{-- TIMELINE --}}
    <section class="py-20 bg-slate-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Sejarah" judul="Jejak Langkah Kami" class="mb-16" />

            <ol class="relative border-l-4 border-gold-500 ml-3 sm:ml-8 space-y-12">
                @php
                    $tahapan = [
                    [
                        'tahun' => '2020',
                        'ikon' => 'sparkles',
                        'judul' => 'Awal Perintisan',
                        'isi' => 'Dimulai dari persekutuan doa kecil di salah satu rumah jemaat di lingkungan Volker.',
                        'kini' => false,
                    ],
                    [
                        'tahun' => '2023',
                        'ikon' => 'cross',
                        'judul' => 'Penetapan Persiapan Resort',
                        'isi' => 'Secara resmi ditetapkan menjadi HKBP Persiapan Resort Volker oleh Ompui Ephorus.',
                        'kini' => false,
                    ],
                    [
                        'tahun' => '2026 - Sekarang',
                        'ikon' => 'map-pin',
                        'judul' => 'Membangun Masa Depan',
                        'isi' => 'Penguatan pelayanan digital dan pembangunan sarana ibadah yang lebih inklusif.',
                        'kini' => true,
                    ],
                ];
                @endphp

                @foreach($tahapan as $tahap)
                    <li class="relative pl-8 sm:pl-12">
                        <span aria-hidden="true"
                              class="absolute -left-4.5 top-0 w-8 h-8 rounded-full border-4 border-slate-50 flex items-center justify-center shadow-brand-sm {{ $tahap['kini'] ? 'bg-linear-to-br from-gold-400 to-gold-600 text-hkbp-900 motion-safe:animate-pulse' : 'bg-linear-to-br from-hkbp-800 to-hkbp-950 text-gold-400' }}">
                            <i data-lucide="{{ $tahap['ikon'] }}" class="w-4 h-4"></i>
                        </span>

                        <div class="bg-white p-6 rounded-2xl shadow-brand-sm transition-all duration-300 hover:-translate-y-0.5 {{ $tahap['kini'] ? 'border border-gold-200 ring-2 ring-gold-100' : 'border border-slate-200/80 hover:border-gold-400 hover:shadow-brand' }}">
                            <p class="text-gold-700 font-bold text-sm">{{ $tahap['tahun'] }}</p>
                            <h3 class="text-xl font-bold text-hkbp-900 mt-1 text-balance">{{ $tahap['judul'] }}</h3>
                            <p class="text-slate-600 mt-2 text-sm leading-relaxed">{{ $tahap['isi'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- VISI MISI --}}
    <section class="py-16 bg-white" aria-labelledby="judul-visi-misi">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="judul-visi-misi" class="sr-only">Visi dan misi</h2>

            <div class="grid md:grid-cols-2 gap-8">
                <div class="relative overflow-hidden bg-linear-to-br from-hkbp-900 to-hkbp-950 text-white p-8 rounded-2xl shadow-brand">
                    <div aria-hidden="true" class="absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-white/5"></div>
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2 relative">
                        <span class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center"><i data-lucide="eye" class="text-gold-400 w-4.5 h-4.5" aria-hidden="true"></i></span> Visi
                    </h3>
                    <p class="text-blue-100/90 leading-relaxed relative">Menjadi Gereja yang inklusif, dialogis, dan terbuka yang membawa damai sejahtera Allah bagi semua ciptaan.</p>
                </div>

                <div class="bg-slate-50 p-8 rounded-2xl border border-slate-200/80">
                    <h3 class="text-xl font-bold text-hkbp-900 mb-4 flex items-center gap-2">
                        <span class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center ring-1 ring-inset ring-amber-100"><i data-lucide="target" class="text-gold-700 w-4.5 h-4.5" aria-hidden="true"></i></span> Misi
                    </h3>
                    <ul class="text-slate-700 leading-relaxed list-disc list-outside pl-5 space-y-2">
                        <li>Mengembangkan spiritualitas jemaat.</li>
                        <li>Meningkatkan pelayanan kasih (Diakonia).</li>
                        <li>Membangun persekutuan (Koinonia).</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- INFORMASI KONTAK --}}
    <section class="py-16 bg-slate-50 border-t border-slate-100" aria-labelledby="judul-kontak">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="judul-kontak" class="text-2xl font-extrabold text-hkbp-900 mb-8">Informasi Gereja</h2>

            <dl class="grid sm:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-brand-sm hover:shadow-brand transition-shadow duration-300">
                    <dt class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-gold-700" aria-hidden="true"></i> Alamat
                    </dt>
                    <dd class="text-slate-700 text-sm leading-relaxed">
                        {{ config('gereja.alamat.jalan') }}<br>
                        {{ config('gereja.alamat.kelurahan') }}, {{ config('gereja.alamat.kota') }}<br>
                        {{ config('gereja.alamat.provinsi') }} {{ config('gereja.alamat.kode_pos') }}
                    </dd>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-brand-sm hover:shadow-brand transition-shadow duration-300">
                    <dt class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">
                        <i data-lucide="phone" class="w-4 h-4 text-gold-700" aria-hidden="true"></i> Telepon
                    </dt>
                    <dd class="text-slate-700 text-sm">
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', config('gereja.telepon')) }}" class="inline-block py-2 font-semibold hover:text-hkbp-800 transition-colors">{{ config('gereja.telepon') }}</a>
                    </dd>
                </div>
            </dl>
        </div>
    </section>
</x-layout>
