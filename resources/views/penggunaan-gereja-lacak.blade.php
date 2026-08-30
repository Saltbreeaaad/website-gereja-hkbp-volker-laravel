<x-layout
    :title="'Lacak Permohonan Gedung - ' . config('gereja.nama')"
    description="Periksa status permohonan pemakaian gedung gereja dengan kode penelusuran yang Anda terima saat mengirim permohonan.">

    <x-page-hero
        ringkas
        judul="Lacak Permohonan"
        deskripsi="Masukkan kode penelusuran yang Anda terima saat mengirim permohonan untuk melihat statusnya." />

    <section class="py-16 bg-slate-50 min-h-[60vh]">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div role="status"
                     class="mb-8 bg-green-50 border border-green-200 text-green-900 rounded-2xl px-5 py-4 flex items-start gap-3">
                    <i data-lucide="circle-check" class="w-5 h-5 shrink-0 mt-0.5" aria-hidden="true"></i>
                    <p class="text-sm font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            {{-- FORMULIR PENELUSURAN --}}
            <form action="{{ route('penggunaan-gereja.lacak') }}" method="GET"
                  class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200 mb-8">
                <label for="kode" class="block font-bold text-hkbp-900 mb-1">Kode penelusuran</label>
                <p class="text-sm text-slate-500 mb-4">Contoh: WG-A2B3C4D5. Huruf besar/kecil dan tanda hubung tidak masalah.</p>

                <div class="flex flex-col sm:flex-row gap-3">
                    {{-- text-base di perangkat sentuh: font < 16px membuat iOS Safari
                         memperbesar halaman saat input difokus. Sama seperti x-field. --}}
                    <input type="text"
                           id="kode"
                           name="kode"
                           value="{{ $kode }}"
                           maxlength="20"
                           autocomplete="off"
                           spellcheck="false"
                           placeholder="WG-XXXXXXXX"
                           @if($tidakDitemukan) aria-describedby="kode-galat" aria-invalid="true" @endif
                           class="flex-1 min-w-0 bg-slate-50 border rounded-xl px-4 py-3 text-sm pointer-coarse:text-base font-bold tracking-wider uppercase text-slate-800 focus:ring-2 focus:ring-hkbp-800 focus:border-hkbp-800 focus:outline-none {{ $tidakDitemukan ? 'border-red-400' : 'border-slate-300' }}">

                    <button type="submit"
                            class="shrink-0 inline-flex items-center justify-center gap-2 min-h-11 bg-hkbp-800 hover:bg-hkbp-900 text-white text-sm font-bold px-6 rounded-xl transition-colors">
                        <i data-lucide="search" class="w-4 h-4" aria-hidden="true"></i> Cek Status
                    </button>
                </div>

                @if($tidakDitemukan)
                    <p id="kode-galat" role="alert" tabindex="-1" data-error-summary
                       class="mt-4 bg-red-50 border border-red-200 text-red-900 text-sm font-semibold rounded-xl px-4 py-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                        Kode <span class="font-mono">{{ $kode }}</span> tidak ditemukan. Periksa kembali penulisannya, atau hubungi pengurus gereja di
                        <a href="{{ $telepon_gereja }}" class="underline underline-offset-2">{{ config('gereja.telepon') }}</a>.
                    </p>
                @endif
            </form>

            {{-- HASIL --}}
            @if($permohonan)
                @php
                    // Dirakit di blok @php: kompilator Blade memotong argumen array
                    // multi-baris yang bersarang di dalam direktif.
                    $gaya = match ($permohonan->status) {
                        $status_disetujui => ['bg' => 'bg-emerald-50', 'garis' => 'border-emerald-200', 'teks' => 'text-emerald-800', 'ikon' => 'circle-check'],
                        $status_ditolak => ['bg' => 'bg-red-50', 'garis' => 'border-red-200', 'teks' => 'text-red-800', 'ikon' => 'x'],
                        default => ['bg' => 'bg-amber-50', 'garis' => 'border-amber-200', 'teks' => 'text-amber-800', 'ikon' => 'hourglass'],
                    };
                @endphp

                <article class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <header class="{{ $gaya['bg'] }} border-b {{ $gaya['garis'] }} px-6 sm:px-8 py-5">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Status permohonan</p>
                        <p class="flex items-center gap-2 text-xl font-black {{ $gaya['teks'] }}">
                            <i data-lucide="{{ $gaya['ikon'] }}" class="w-6 h-6 shrink-0" aria-hidden="true"></i>
                            {{ $permohonan->status }}
                        </p>
                        <p class="mt-2 text-sm {{ $gaya['teks'] }}">{{ $permohonan->penjelasanStatus() }}</p>
                    </header>

                    <div class="px-6 sm:px-8 py-6">
                        <dl class="space-y-4 text-sm">
                            <div>
                                <dt class="font-bold text-slate-500">Kode penelusuran</dt>
                                <dd class="font-mono font-bold text-hkbp-900 tracking-wider text-base">{{ $permohonan->kode }}</dd>
                            </div>
                            <div>
                                <dt class="font-bold text-slate-500">Kegiatan</dt>
                                <dd class="font-bold text-hkbp-900 text-base">{{ $permohonan->nama_kegiatan }}</dd>
                            </div>
                            <div>
                                <dt class="font-bold text-slate-500">Pemohon</dt>
                                <dd class="text-slate-800">{{ $permohonan->nama_pemohon }}</dd>
                            </div>
                            <div>
                                <dt class="font-bold text-slate-500">Waktu</dt>
                                <dd class="text-slate-800">
                                    <time datetime="{{ $permohonan->tanggal->toDateString() }}">{{ $permohonan->tanggal->translatedFormat('l, d F Y') }}</time>,
                                    {{ $permohonan->waktu_mulai->format('H:i') }}&ndash;{{ $permohonan->waktu_selesai->format('H:i') }} WIB
                                </dd>
                            </div>
                            @if($permohonan->keterangan)
                                <div>
                                    <dt class="font-bold text-slate-500">Keterangan</dt>
                                    <dd class="text-slate-800 whitespace-pre-line">{{ $permohonan->keterangan }}</dd>
                                </div>
                            @endif
                        </dl>

                        {{-- Catatan pengurus. Inilah kolom yang dulu diisi admin di panel
                             tetapi tidak pernah sampai ke pemohon mana pun. --}}
                        @if($permohonan->catatan_admin)
                            <div class="mt-6 bg-slate-50 border border-slate-200 rounded-xl p-4">
                                <h2 class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">
                                    <i data-lucide="inbox" class="w-4 h-4" aria-hidden="true"></i> Catatan dari pengurus
                                </h2>
                                <p class="text-sm text-slate-800 whitespace-pre-line">{{ $permohonan->catatan_admin }}</p>
                            </div>
                        @endif
                    </div>
                </article>
            @endif

            <p class="mt-8 text-center">
                <a href="{{ route('penggunaan-gereja') }}" class="inline-block py-2 text-sm font-bold text-hkbp-800 hover:text-hkbp-900 underline underline-offset-4 transition-colors">
                    &larr; Kembali ke jadwal &amp; formulir permohonan
                </a>
            </p>
        </div>
    </section>
</x-layout>
