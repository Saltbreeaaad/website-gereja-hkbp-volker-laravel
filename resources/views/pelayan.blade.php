<x-layout title="Pelayan Jemaat - HKBP Persiapan Resort Volker">

    <section class="relative bg-hkbp-900 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h1 class="text-4xl sm:text-5xl font-black mb-4">Pelayan & Pengurus Jemaat</h1>
            <p class="text-lg text-blue-200 max-w-2xl mx-auto">Mengenal lebih dekat para pelayan Tuhan yang menggembalakan dan melayani di HKBP Persiapan Resort Volker.</p>
        </div>
    </section>

    <!-- PENDETA -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-b-2 border-gold-500 pb-2 mb-10 inline-block">
                <h2 class="text-3xl font-extrabold text-hkbp-900">Pendeta</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($pendeta as $person)
                <div class="bg-slate-50 rounded-2xl border border-slate-200 shadow-sm text-center overflow-hidden flex flex-col group hover:shadow-xl transition-all duration-300">
                    <div class="w-full h-80 relative overflow-hidden bg-slate-200 border-b-4 border-gold-500">
                        @if($person->foto)
                            <img src="{{ asset('storage/' . $person->foto) }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full bg-hkbp-900 text-white flex items-center justify-center font-bold text-6xl uppercase">{{ substr($person->nama, 0, 2) }}</div>
                        @endif
                    </div>
                    <div class="p-8 flex-1 flex flex-col justify-center">
                        <span class="inline-block bg-amber-100 text-amber-800 text-xs font-bold px-4 py-1.5 rounded-full mb-4 self-center">{{ $person->jabatan }}</span>
                        <h3 class="font-bold text-2xl text-hkbp-900 mb-2">{{ $person->nama }}</h3>
                        <p class="text-slate-600 text-sm mb-6">{{ $person->keterangan }}</p>
                    </div>
                </div>
                @empty
                <p class="text-slate-500 italic">Data pendeta belum tersedia.</p>
                @endforelse
            </div>
        </div>
    </section>

    <!-- PARHALADO -->
    <section class="py-16 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-b-2 border-gold-500 pb-2 mb-2 inline-block">
                <h2 class="text-3xl font-extrabold text-hkbp-900">Parhalado (Majelis Jemaat)</h2>
            </div>
            
            @forelse($parhalado as $bidang => $anggota)
                <div class="mt-10 mb-6">
                    <h3 class="text-xl font-bold text-hkbp-800 border-l-4 border-gold-500 pl-3">{{ $bidang ?: 'Umum / Lainnya' }}</h3>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($anggota as $person)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm text-center overflow-hidden flex flex-col group hover:shadow-lg transition-all duration-300">
                        <div class="w-full h-64 relative overflow-hidden bg-slate-200 border-b-2 border-blue-200">
                            @if($person->foto)
                                <img src="{{ asset('storage/' . $person->foto) }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full bg-hkbp-800 text-white flex items-center justify-center font-bold text-5xl uppercase">{{ substr($person->nama, 0, 2) }}</div>
                            @endif
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-center">
                            <span class="inline-block bg-blue-50 text-hkbp-800 text-xs font-bold px-3 py-1 rounded-md mb-3 self-center border border-blue-100">{{ $person->jabatan }}</span>
                            <h4 class="font-bold text-lg text-hkbp-900 mb-1">{{ $person->nama }}</h4>
                            <p class="text-slate-500 text-sm">{{ $person->keterangan }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @empty
                <p class="text-slate-500 italic mt-6">Data parhalado belum tersedia.</p>
            @endforelse
        </div>
    </section>

    <!-- PENGURUS KATEGORIAL -->
    <section class="py-16 bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-b-2 border-gold-500 pb-2 mb-2 inline-block">
                <h2 class="text-3xl font-extrabold text-hkbp-900">Pengurus Kategorial</h2>
            </div>
            
            @forelse($kategorial as $bidang => $anggota)
                <div class="mt-10 mb-6">
                    <h3 class="text-xl font-bold text-hkbp-800 border-l-4 border-gold-500 pl-3">{{ $bidang ?: 'Umum / Lainnya' }}</h3>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($anggota as $person)
                    <div class="bg-slate-50 rounded-2xl border border-slate-200 shadow-sm text-center overflow-hidden flex flex-col group hover:shadow-md transition-all duration-300">
                        <div class="w-full h-56 relative overflow-hidden bg-slate-200">
                            @if($person->foto)
                                <img src="{{ asset('storage/' . $person->foto) }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full bg-slate-400 text-white flex items-center justify-center font-bold text-4xl uppercase">{{ substr($person->nama, 0, 2) }}</div>
                            @endif
                        </div>
                        <div class="p-5 flex-1 flex flex-col justify-center">
                            <h4 class="font-bold text-lg text-hkbp-900 mb-1">{{ $person->nama }}</h4>
                            <p class="text-hkbp-700 text-sm font-semibold">{{ $person->jabatan }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @empty
                <p class="text-slate-500 italic mt-6">Data pengurus kategorial belum tersedia.</p>
            @endforelse
        </div>
    </section>
</x-layout>