@props(['label' => 'carousel'])

{{--
    Baris kontrol carousel: geser kiri, jeda/putar, geser kanan.

    Ketiganya `hidden` sampai JavaScript memasangnya. Tanpa JS carousel-nya
    menjadi daftar biasa yang tergulir sendiri, dan tombol yang tidak
    melakukan apa-apa lebih membingungkan daripada tidak ada tombol.

    Tinggi 44px (h-11) memenuhi ambang target sentuh WCAG 2.5.8 — tombol
    panah carousel termasuk yang paling sering meleset ditekan di ponsel.

    Teks sr-only adalah cadangan: setelah Swiper terpasang, modul a11y-nya
    memasang aria-label ("Slide sebelumnya"/"Slide berikutnya") yang menang
    atas isi tombol. Teks di sini yang dipakai bila modul itu gagal dimuat.
--}}

@php
    $gaya = 'inline-flex items-center justify-center w-11 h-11 rounded-xl border border-slate-300 bg-white text-hkbp-900 transition-colors hover:border-hkbp-800 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-hkbp-800';
@endphp

<div class="flex items-center justify-center gap-2 mt-4">
    <button type="button" data-swiper-prev hidden class="{{ $gaya }}">
        <i data-lucide="chevron-left" class="w-5 h-5" aria-hidden="true"></i>
        <span class="sr-only">Geser {{ $label }} ke kiri</span>
    </button>

    <x-swiper-toggle />

    <button type="button" data-swiper-next hidden class="{{ $gaya }}">
        <i data-lucide="chevron-right" class="w-5 h-5" aria-hidden="true"></i>
        <span class="sr-only">Geser {{ $label }} ke kanan</span>
    </button>
</div>
