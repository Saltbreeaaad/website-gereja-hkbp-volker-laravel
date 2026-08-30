@props(['judul', 'deskripsi' => null, 'ringkas' => false])

<section {{ $attributes->merge(['class' => 'relative bg-hkbp-900 text-white ' . ($ringkas ? 'py-16' : 'py-20')]) }}>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <h1 class="{{ $ringkas ? 'text-3xl sm:text-4xl' : 'text-4xl sm:text-5xl' }} font-black mb-4 text-balance">{{ $judul }}</h1>
        @if($deskripsi)
            <p class="{{ $ringkas ? 'text-sm sm:text-base' : 'text-lg' }} text-blue-200 max-w-2xl mx-auto text-pretty">{{ $deskripsi }}</p>
        @endif
    </div>
</section>
