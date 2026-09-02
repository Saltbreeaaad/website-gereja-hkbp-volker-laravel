<x-layout title="Agenda Gereja - {{ config('gereja.nama') }}" description="Agenda ibadah dan kegiatan mendatang {{ config('gereja.nama') }}.">
    <x-page-hero judul="Agenda Gereja" deskripsi="Jangan lewatkan ibadah dan kegiatan pelayanan yang akan datang." ringkas />

    <section class="py-12 sm:py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-black tracking-[0.18em] uppercase text-gold-700">Kalender Pelayanan</p>
                    <h2 class="mt-2 text-2xl sm:text-3xl font-black tracking-tight text-hkbp-900">Ruang untuk hadir bersama</h2>
                </div>
                <a href="{{ route('agenda.kalender') }}" class="inline-flex w-fit items-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-bold text-hkbp-800 border border-slate-200 shadow-brand-sm hover:shadow-brand transition-shadow">
                    <i data-lucide="calendar-plus" class="w-4 h-4" aria-hidden="true"></i> Tambahkan ke kalender
                </a>
            </div>

            <div class="space-y-4">
                @forelse($agenda as $item)
                    <article class="group grid grid-cols-[4.5rem_1fr] sm:grid-cols-[7rem_1fr_auto] gap-4 sm:gap-6 rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-6 shadow-brand-sm hover:shadow-brand transition-shadow">
                        <div class="rounded-xl bg-hkbp-950 text-white text-center p-3 self-start">
                            <time datetime="{{ $item->tanggal->toDateString() }}" class="block">
                                <span class="block text-2xl sm:text-3xl font-black leading-none text-gold-300">{{ $item->tanggal->format('d') }}</span>
                                <span class="mt-1 block text-[10px] sm:text-xs font-bold tracking-wider uppercase">{{ $item->tanggal->translatedFormat('M Y') }}</span>
                            </time>
                        </div>
                        <div class="min-w-0">
                            <p class="flex items-center gap-2 text-xs font-bold text-hkbp-700">
                                <i data-lucide="clock-3" class="w-4 h-4" aria-hidden="true"></i> {{ $item->waktu?->format('H:i') }} WIB
                            </p>
                            <h3 class="mt-2 text-lg sm:text-xl font-black text-hkbp-900 text-balance">{{ $item->nama_ibadah }}</h3>
                            @if($item->keterangan)<p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $item->keterangan }}</p>@endif
                            @if($item->pelayan_firman)<p class="mt-3 text-sm font-semibold text-slate-700">Pelayan firman: {{ $item->pelayan_firman }}</p>@endif
                        </div>
                        <div class="col-start-2 sm:col-start-auto sm:self-center">
                            <a href="{{ route('penggunaan-gereja') }}" class="inline-flex py-2 text-sm font-bold text-hkbp-700 hover:text-hkbp-950">Informasi gedung <span class="sr-only">untuk {{ $item->nama_ibadah }}</span></a>
                        </div>
                    </article>
                @empty
                    <x-empty-state ikon="calendar-x" pesan="Agenda mendatang akan segera dipublikasikan." />
                @endforelse
            </div>
        </div>
    </section>
</x-layout>
