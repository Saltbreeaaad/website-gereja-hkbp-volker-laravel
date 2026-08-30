{{--
    Paginasi bergaya merek gereja.

    View bawaan Laravel tinggal di dalam vendor/, sedangkan Tailwind v4 tidak
    memindai direktori itu (vendor/ ada di .gitignore). Akibatnya kelas-kelas
    paginasi bawaan tidak pernah ikut ter-build dan tombolnya tampil polos.
    Karena itu view ini diterbitkan ke resources/views agar ikut dipindai.
--}}

@php
    $gayaDasar = 'inline-flex items-center justify-center min-w-11 h-11 px-4 text-sm font-bold rounded-xl border transition-colors';
    $gayaAktif = $gayaDasar . ' bg-hkbp-800 border-hkbp-800 text-white';
    $gayaTaut = $gayaDasar . ' bg-white border-slate-300 text-slate-700 hover:border-hkbp-800 hover:text-hkbp-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-hkbp-800';
    $gayaMati = $gayaDasar . ' bg-slate-50 border-slate-200 text-slate-500 cursor-not-allowed';
@endphp

@if ($paginator->hasPages())
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        {{-- Ringkasan posisi: penting di mobile, tempat nomor halaman disembunyikan. --}}
        <p class="text-sm text-slate-600 order-2 sm:order-1">
            Menampilkan
            <span class="font-bold text-hkbp-900">{{ $paginator->firstItem() }}</span>–<span class="font-bold text-hkbp-900">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-bold text-hkbp-900">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-2 order-1 sm:order-2">

            {{-- Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="{{ $gayaMati }}" aria-disabled="true">
                    <i data-lucide="chevron-left" class="w-4 h-4" aria-hidden="true"></i>
                    <span class="sr-only">Halaman sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $gayaTaut }}">
                    <i data-lucide="chevron-left" class="w-4 h-4" aria-hidden="true"></i>
                    <span class="sr-only">Halaman sebelumnya</span>
                </a>
            @endif

            {{-- Nomor halaman: disembunyikan di layar sempit agar tidak melimpah. --}}
            <div class="hidden sm:flex items-center gap-2">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 text-slate-500" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="{{ $gayaAktif }}" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $gayaTaut }}">
                                    <span class="sr-only">Halaman </span>{{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Penanda halaman untuk layar sempit --}}
            <span class="sm:hidden text-sm font-bold text-slate-700 px-2">
                Hal. {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            {{-- Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $gayaTaut }}">
                    <i data-lucide="chevron-right" class="w-4 h-4" aria-hidden="true"></i>
                    <span class="sr-only">Halaman berikutnya</span>
                </a>
            @else
                <span class="{{ $gayaMati }}" aria-disabled="true">
                    <i data-lucide="chevron-right" class="w-4 h-4" aria-hidden="true"></i>
                    <span class="sr-only">Halaman berikutnya</span>
                </span>
            @endif
        </div>
    </div>
@endif
