@props(['person', 'ukuran' => 'sedang'])

@php
    $gaya = [
        'besar' => [
            'kartu' => 'bg-slate-50 hover:shadow-xl',
            'tinggi' => 'h-80',
            'bingkai' => 'border-b-4 border-gold-500',
            'inisial' => 'bg-hkbp-900 text-6xl',
            'padding' => 'p-8',
            'nama' => 'text-2xl',
            'lebar' => 400, 'tinggiPx' => 320,
        ],
        'sedang' => [
            'kartu' => 'bg-white hover:shadow-lg',
            'tinggi' => 'h-64',
            'bingkai' => 'border-b-2 border-blue-200',
            'inisial' => 'bg-hkbp-800 text-5xl',
            'padding' => 'p-6',
            'nama' => 'text-lg',
            'lebar' => 320, 'tinggiPx' => 256,
        ],
        'kecil' => [
            'kartu' => 'bg-slate-50 hover:shadow-md',
            'tinggi' => 'h-56',
            'bingkai' => '',
            'inisial' => 'bg-slate-500 text-4xl',
            'padding' => 'p-5',
            'nama' => 'text-lg',
            'lebar' => 320, 'tinggiPx' => 224,
        ],
    ][$ukuran];
@endphp

<article class="{{ $gaya['kartu'] }} rounded-2xl border border-slate-200 shadow-sm text-center overflow-hidden flex flex-col group transition-shadow duration-300">
    <div class="w-full {{ $gaya['tinggi'] }} relative overflow-hidden bg-slate-200 {{ $gaya['bingkai'] }}">
        @if($person->foto)
            <img src="{{ Storage::disk('public')->url($person->foto) }}"
                 alt="Foto {{ $person->nama }}"
                 width="{{ $gaya['lebar'] }}" height="{{ $gaya['tinggiPx'] }}"
                 loading="lazy" decoding="async"
                 class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
        @else
            <span aria-hidden="true" class="w-full h-full {{ $gaya['inisial'] }} text-white flex items-center justify-center font-bold">{{ $person->inisial() }}</span>
        @endif
    </div>

    <div class="{{ $gaya['padding'] }} flex-1 flex flex-col justify-center">
        @if($ukuran === 'besar')
            <p class="inline-block bg-amber-100 text-amber-900 text-xs font-bold px-4 py-1.5 rounded-full mb-4 self-center">{{ $person->jabatan }}</p>
        @elseif($ukuran === 'sedang')
            <p class="inline-block bg-blue-50 text-hkbp-800 text-xs font-bold px-3 py-1 rounded-md mb-3 self-center border border-blue-100">{{ $person->jabatan }}</p>
        @endif

        <h3 class="font-bold {{ $gaya['nama'] }} text-hkbp-900 mb-1 text-balance">{{ $person->nama }}</h3>

        @if($ukuran === 'kecil')
            <p class="text-hkbp-700 text-sm font-semibold">{{ $person->jabatan }}</p>
        @elseif($person->keterangan)
            <p class="text-slate-600 text-sm">{{ $person->keterangan }}</p>
        @endif
    </div>
</article>
