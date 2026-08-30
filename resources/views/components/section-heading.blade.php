@props(['judul', 'deskripsi' => null, 'rata' => 'tengah'])

<div {{ $attributes->merge(['class' => $rata === 'tengah' ? 'text-center max-w-3xl mx-auto mb-12' : 'mb-10']) }}>
    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-hkbp-900 text-balance">{{ $judul }}</h2>
    @if($deskripsi)
        <p class="mt-3 text-slate-600 text-pretty">{{ $deskripsi }}</p>
    @endif
</div>
