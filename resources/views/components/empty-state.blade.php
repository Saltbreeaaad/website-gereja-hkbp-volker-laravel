@props(['ikon' => 'inbox', 'judul' => null, 'pesan'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center']) }}>
    <i data-lucide="{{ $ikon }}" class="w-10 h-10 text-slate-300 mx-auto mb-3" aria-hidden="true"></i>
    @if($judul)
        <h3 class="text-lg font-bold text-slate-800 mb-1">{{ $judul }}</h3>
    @endif
    <p class="text-slate-500 text-sm max-w-md mx-auto">{{ $pesan }}</p>
    @if(trim($slot) !== '')
        <div class="mt-6">{{ $slot }}</div>
    @endif
</div>
