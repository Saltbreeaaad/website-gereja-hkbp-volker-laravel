@props([
    'rute',
    'tahun' => null,
    'tersedia' => [],
    'label' => 'Tahun',
])

{{--
    Penyaring tahun untuk arsip warta dan galeri.

    Tautan, bukan <select>: tiap tahun menjadi URL tersendiri yang bisa
    ditandai dan dibagikan, tetap berfungsi tanpa JavaScript, dan ikut terbaca
    mesin pencari. Disembunyikan bila hanya ada satu tahun — penyaring yang
    cuma punya satu pilihan hanya menambah kebisingan.
--}}
@if(count($tersedia) > 1)
    @php
        $dasar = 'inline-flex items-center min-h-11 px-4 rounded-xl border text-sm font-bold transition-colors';
        $aktif = $dasar . ' bg-hkbp-800 border-hkbp-800 text-white';
        $mati = $dasar . ' bg-white border-slate-300 text-slate-700 hover:border-hkbp-800 hover:text-hkbp-900';
    @endphp

    <nav aria-label="Saring menurut tahun" {{ $attributes->merge(['class' => 'mb-10']) }}>
        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">{{ $label }}</h2>

        <ul class="flex flex-wrap gap-2">
            <li>
                <a href="{{ route($rute) }}"
                   @if(! $tahun) aria-current="page" @endif
                   class="{{ $tahun ? $mati : $aktif }}">Semua</a>
            </li>
            @foreach($tersedia as $item)
                <li>
                    <a href="{{ route($rute, ['tahun' => $item]) }}"
                       @if($tahun === $item) aria-current="page" @endif
                       class="{{ $tahun === $item ? $aktif : $mati }}">{{ $item }}</a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
