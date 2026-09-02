@props(['ikon' => 'inbox', 'judul' => null, 'pesan'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center']) }}>
    <span aria-hidden="true" class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
        <i data-lucide="{{ $ikon }}" class="w-8 h-8 text-slate-300"></i>
    </span>
    @if($judul)
        <h3 class="text-lg font-bold text-slate-800 mb-1">{{ $judul }}</h3>
    @endif
    <p class="text-slate-500 text-sm max-w-md mx-auto leading-relaxed">{{ $pesan }}</p>
    @if(trim($slot) !== '')
        <div class="mt-6">{{ $slot }}</div>
    @endif
</div>
