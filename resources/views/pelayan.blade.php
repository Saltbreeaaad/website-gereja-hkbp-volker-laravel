<x-layout
    title="Pelayan Jemaat - {{ config('gereja.nama') }}"
    description="Profil pendeta, parhalado (majelis jemaat), dan pengurus kategorial yang melayani di {{ config('gereja.nama') }}.">

    <x-page-hero
        judul="Pelayan & Pengurus Jemaat"
        deskripsi="Mengenal lebih dekat para pelayan Tuhan yang menggembalakan dan melayani di {{ config('gereja.nama') }}." />

    {{-- PENDETA --}}
    <section class="py-16 bg-white" aria-labelledby="judul-pendeta">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-b-2 border-gold-500 pb-2 mb-10 inline-block">
                <h2 id="judul-pendeta" class="text-3xl font-extrabold text-hkbp-900">Pendeta</h2>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($pendeta as $person)
                    <x-kartu-pelayan :person="$person" ukuran="besar" />
                @empty
                    <div class="col-span-full">
                        <x-empty-state ikon="user" pesan="Data pendeta belum tersedia." />
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- PARHALADO --}}
    <section class="py-16 bg-slate-50 border-t border-slate-200" aria-labelledby="judul-parhalado">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-b-2 border-gold-500 pb-2 mb-2 inline-block">
                <h2 id="judul-parhalado" class="text-3xl font-extrabold text-hkbp-900">Parhalado (Majelis Jemaat)</h2>
            </div>

            @forelse($parhalado as $bidang => $anggota)
                <h3 class="text-xl font-bold text-hkbp-800 border-l-4 border-gold-500 pl-3 mt-10 mb-6">{{ $bidang ?: 'Umum / Lainnya' }}</h3>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($anggota as $person)
                        <x-kartu-pelayan :person="$person" ukuran="sedang" />
                    @endforeach
                </div>
            @empty
                <div class="mt-8">
                    <x-empty-state ikon="user" pesan="Data parhalado belum tersedia." />
                </div>
            @endforelse
        </div>
    </section>

    {{-- PENGURUS KATEGORIAL --}}
    <section class="py-16 bg-white border-t border-slate-200" aria-labelledby="judul-kategorial">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-b-2 border-gold-500 pb-2 mb-2 inline-block">
                <h2 id="judul-kategorial" class="text-3xl font-extrabold text-hkbp-900">Pengurus Kategorial</h2>
            </div>

            @forelse($kategorial as $bidang => $anggota)
                <h3 class="text-xl font-bold text-hkbp-800 border-l-4 border-gold-500 pl-3 mt-10 mb-6">{{ $bidang ?: 'Umum / Lainnya' }}</h3>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($anggota as $person)
                        <x-kartu-pelayan :person="$person" ukuran="kecil" />
                    @endforeach
                </div>
            @empty
                <div class="mt-8">
                    <x-empty-state ikon="user" pesan="Data pengurus kategorial belum tersedia." />
                </div>
            @endforelse
        </div>
    </section>
</x-layout>
