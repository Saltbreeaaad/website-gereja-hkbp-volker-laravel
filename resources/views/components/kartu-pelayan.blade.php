@props(['person', 'ukuran' => 'sedang'])

@php
    $gaya = [
        'besar' => [
            'kartu' => 'bg-white hover:shadow-brand-lg',
            'tinggi' => 'h-80',
            'bingkai' => 'border-b-4 border-gold-500',
            'inisial' => 'bg-linear-to-br from-hkbp-800 to-hkbp-950 text-6xl',
            'padding' => 'p-8',
            'nama' => 'text-2xl',
            'lebar' => 400, 'tinggiPx' => 320,
        ],
        'sedang' => [
            'kartu' => 'bg-white hover:shadow-brand',
            'tinggi' => 'h-64',
            'bingkai' => 'border-b-2 border-blue-200',
            'inisial' => 'bg-linear-to-br from-hkbp-700 to-hkbp-900 text-5xl',
            'padding' => 'p-6',
            'nama' => 'text-lg',
            'lebar' => 320, 'tinggiPx' => 256,
        ],
        'kecil' => [
            'kartu' => 'bg-white hover:shadow-brand-sm',
            'tinggi' => 'h-56',
            'bingkai' => '',
            'inisial' => 'bg-slate-500 text-4xl',
            'padding' => 'p-5',
            'nama' => 'text-lg',
            'lebar' => 320, 'tinggiPx' => 224,
        ],
    ][$ukuran];
@endphp

<article class="{{ $gaya['kartu'] }} rounded-2xl border border-slate-200/80 shadow-brand-sm text-center overflow-hidden flex flex-col group transition-all duration-300 hover:-translate-y-1">
    <div class="w-full {{ $gaya['tinggi'] }} relative overflow-hidden bg-slate-100 {{ $gaya['bingkai'] }}">
        @if($person->foto)
            <img src="{{ $person->urlFoto() }}"
                 @if($srcset = $person->srcsetFoto()) srcset="{{ $srcset }}" sizes="128px" @endif
                 alt="Foto {{ $person->nama }}"
                 width="{{ $gaya['lebar'] }}" height="{{ $gaya['tinggiPx'] }}"
                 loading="lazy" decoding="async"
                 class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
        @else
            <span aria-hidden="true" class="w-full h-full {{ $gaya['inisial'] }} text-white flex items-center justify-center font-bold">{{ $person->inisial() }}</span>
        @endif
        <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-16 bg-linear-to-t from-black/25 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </div>

    <div class="{{ $gaya['padding'] }} flex-1 flex flex-col justify-center">
        @if($ukuran === 'besar')
            <p class="inline-block bg-amber-50 text-amber-900 ring-1 ring-inset ring-amber-200 text-xs font-bold px-4 py-1.5 rounded-full mb-4 self-center">{{ $person->jabatan }}</p>
        @elseif($ukuran === 'sedang')
            <p class="inline-block bg-blue-50 text-hkbp-800 text-xs font-bold px-3 py-1 rounded-md mb-3 self-center ring-1 ring-inset ring-blue-100">{{ $person->jabatan }}</p>
        @endif

        <h3 class="font-bold {{ $gaya['nama'] }} text-hkbp-900 mb-1 text-balance">{{ $person->nama }}</h3>

        @if($ukuran === 'kecil')
            <p class="text-hkbp-700 text-sm font-semibold">{{ $person->jabatan }}</p>
        @elseif($person->keterangan)
            <p class="text-slate-600 text-sm leading-relaxed">{{ $person->keterangan }}</p>
        @endif
    </div>
</article>
