@props([
    'nama',
    'label',
    'tipe' => 'text',
    'petunjuk' => null,
    'wajib' => false,
    'baris' => 3,
])

@php
    $idError = $nama . '-error';
    $idPetunjuk = $nama . '-petunjuk';
    $adaError = $errors->has($nama);

    $dijelaskanOleh = array_filter([
        $petunjuk ? $idPetunjuk : null,
        $adaError ? $idError : null,
    ]);

    // text-base (16px) untuk perangkat sentuh: iOS Safari otomatis memperbesar
    // halaman saat input dengan font < 16px difokus, dan tidak mengembalikan
    // zoom-nya setelah selesai — pengguna tertinggal di tampilan yang tergeser.
    //
    // Patokannya jenis penunjuk, bukan lebar layar. Versi sebelumnya memakai
    // `text-base sm:text-sm`, sehingga ponsel dalam posisi lanskap (lebar 740px,
    // melewati breakpoint sm) kembali mendapat 14px dan auto-zoom-nya muncul lagi
    // — terukur di Brave pada viewport 740x360.
    // py-3 menjaga tinggi bidang sentuh di atas 44px (WCAG 2.5.5).
    $kelasInput = 'w-full bg-slate-50 border rounded-xl px-4 py-3 text-sm pointer-coarse:text-base text-slate-800 '
        .'focus:ring-2 focus:ring-offset-0 focus:outline-none focus:bg-white transition-colors duration-200 '
        .($adaError
            ? 'border-red-400 focus:ring-red-500 focus:border-red-500'
            : 'border-slate-200 focus:ring-hkbp-800/40 focus:border-hkbp-800 hover:border-slate-300');
@endphp

<div>
    <label for="{{ $nama }}" class="block text-xs font-bold text-slate-500 tracking-wide uppercase mb-1.5">
        {{ $label }}
        @if($wajib)
            <span class="text-red-600" aria-hidden="true">*</span>
            <span class="sr-only">(wajib diisi)</span>
        @endif
    </label>

    @if($tipe === 'textarea')
        <textarea id="{{ $nama }}"
                  name="{{ $nama }}"
                  rows="{{ $baris }}"
                  @if($adaError) aria-invalid="true" @endif
                  @if($dijelaskanOleh) aria-describedby="{{ implode(' ', $dijelaskanOleh) }}" @endif
                  {{ $attributes->merge(['class' => $kelasInput]) }}>{{ old($nama) }}</textarea>
    @else
        <input type="{{ $tipe }}"
               id="{{ $nama }}"
               name="{{ $nama }}"
               value="{{ old($nama) }}"
               @if($wajib) required @endif
               @if($adaError) aria-invalid="true" @endif
               @if($dijelaskanOleh) aria-describedby="{{ implode(' ', $dijelaskanOleh) }}" @endif
               {{ $attributes->merge(['class' => $kelasInput . ' font-semibold']) }}>
    @endif

    @if($petunjuk)
        <p id="{{ $idPetunjuk }}" class="mt-1.5 text-xs text-slate-500">{{ $petunjuk }}</p>
    @endif

    @error($nama)
        <p id="{{ $idError }}" class="mt-1.5 text-xs font-semibold text-red-700">{{ $message }}</p>
    @enderror
</div>
