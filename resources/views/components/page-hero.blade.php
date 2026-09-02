@props(['judul', 'deskripsi' => null, 'ringkas' => false])

<section {{ $attributes->merge(['class' => 'relative overflow-hidden bg-linear-to-br from-hkbp-950 via-hkbp-900 to-hkbp-800 text-white ' . ($ringkas ? 'py-16' : 'py-24')]) }}>
    {{-- Hiasan murni dekoratif: disembunyikan dari pembaca layar, boleh diam
         di bawah prefers-reduced-motion (aturan global di app.css meredam
         animation-duration seluruh elemen, termasuk animate-blob di sini). --}}
    <div aria-hidden="true" class="blob-hias w-[28rem] h-[28rem] -top-40 -right-24 bg-hkbp-600/30 animate-blob"></div>
    <div aria-hidden="true" class="blob-hias w-80 h-80 -bottom-32 -left-16 bg-gold-500/10 animate-blob [animation-delay:-6s]"></div>
    <div aria-hidden="true" class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.08)_1px,transparent_0)] [background-size:28px_28px] opacity-40"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <h1 class="{{ $ringkas ? 'text-3xl sm:text-4xl' : 'text-4xl sm:text-5xl lg:text-6xl' }} font-black mb-4 text-balance tracking-tight">{{ $judul }}</h1>
        @if($deskripsi)
            <p class="{{ $ringkas ? 'text-sm sm:text-base' : 'text-lg' }} text-blue-100/90 max-w-2xl mx-auto text-pretty leading-relaxed">{{ $deskripsi }}</p>
        @endif
    </div>
</section>
