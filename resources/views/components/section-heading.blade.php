@props(['judul', 'deskripsi' => null, 'rata' => 'tengah', 'eyebrow' => null])

<div {{ $attributes->merge(['class' => $rata === 'tengah' ? 'text-center max-w-3xl mx-auto mb-12' : 'mb-10']) }}>
    @if($eyebrow)
        <span class="inline-flex items-center gap-1.5 text-xs font-bold tracking-[0.15em] uppercase text-gold-700 mb-3">
            <span aria-hidden="true" class="w-6 h-px bg-gold-500"></span>
            {{ $eyebrow }}
        </span>
    @endif
    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-hkbp-900 text-balance tracking-tight">{{ $judul }}</h2>
    @if($deskripsi)
        <p class="mt-3 text-slate-600 text-pretty leading-relaxed">{{ $deskripsi }}</p>
    @endif
</div>
