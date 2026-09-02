<x-layout
    title="Penggunaan Gedung Gereja - {{ config('gereja.nama') }}"
    description="Cek jadwal pemakaian gedung dan ajukan permohonan penggunaan gedung {{ config('gereja.nama') }} secara online.">

    <x-page-hero
        judul="Penggunaan Gedung Gereja"
        deskripsi="Cek jadwal yang sudah dipakai dan ajukan permohonan pemakaian gedung gereja untuk kegiatan Anda, supaya tidak bentrok dengan kegiatan lain." />

    <section class="py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-5 gap-10 items-start">

                {{-- FORM PERMOHONAN --}}
                {{-- min-w-0: tanpa ini grid item menolak menyusut di bawah min-content
                     dan mendorong halaman melebar di layar sempit. --}}
                <div class="lg:col-span-2 lg:sticky lg:top-28 min-w-0">
                    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-brand border border-slate-200/80">
                        <h2 class="text-xl font-black text-hkbp-900 mb-1 flex items-center gap-2">
                            <i data-lucide="send" class="w-5 h-5 text-gold-700" aria-hidden="true"></i> Ajukan Permohonan
                        </h2>
                        <p class="text-sm text-slate-500 mb-6">Permohonan akan ditinjau oleh pengurus gereja sebelum dikonfirmasi.</p>

                        @if(session('success'))
                            <div role="status"
                                 class="mb-6 bg-green-50 border border-green-200 text-green-900 text-sm font-semibold rounded-xl px-4 py-3 flex items-start gap-2">
                                <i data-lucide="circle-check" class="w-5 h-5 shrink-0 mt-0.5" aria-hidden="true"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        @if($errors->any())
                            <div role="alert"
                                 tabindex="-1"
                                 data-error-summary
                                 class="mb-6 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 bg-red-50 border border-red-200 text-red-900 text-sm font-semibold rounded-xl px-4 py-3">
                                <p>Permohonan belum dapat dikirim. Mohon periksa {{ $errors->count() === 1 ? 'isian berikut' : $errors->count() . ' isian berikut' }}:</p>
                                <ul class="mt-2 list-disc list-outside pl-5 space-y-1 font-normal">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('penggunaan-gereja.store') }}" method="POST" class="space-y-4">
                            @csrf

                            {{-- Honeypot antispam. tabindex/autocomplete mencegahnya
                                 mengganggu pengguna keyboard dan pengelola sandi. --}}
                            <div class="absolute -left-[10000px] w-px h-px overflow-hidden" aria-hidden="true">
                                <label for="website">Website</label>
                                <input type="text" id="website" name="website" value="" tabindex="-1" autocomplete="off">
                            </div>

                            <x-field nama="nama_kegiatan" label="Nama Kegiatan" wajib
                                     maxlength="255" placeholder="Cth: Latihan Koor Remaja" />

                            {{-- autocomplete/inputmode: memicu autofill dan papan ketik
                                 yang tepat di ponsel. --}}
                            <x-field nama="nama_pemohon" label="Nama Pemohon" wajib
                                     maxlength="255" placeholder="Nama Anda"
                                     autocomplete="name" />

                            <x-field nama="kontak" label="Kontak (WA/Telepon)" tipe="tel" wajib
                                     maxlength="50" placeholder="08xxxxxxxxxx"
                                     autocomplete="tel" inputmode="tel"
                                     petunjuk="Nomor yang bisa dihubungi pengurus untuk konfirmasi." />

                            <x-field nama="tanggal" label="Tanggal" tipe="date" wajib
                                     :min="now()->toDateString()"
                                     :max="now()->addYear()->toDateString()" />

                            <div class="grid grid-cols-2 gap-3">
                                <x-field nama="waktu_mulai" label="Jam Mulai" tipe="time" wajib />
                                <x-field nama="waktu_selesai" label="Jam Selesai" tipe="time" wajib />
                            </div>

                            <x-field nama="keterangan" label="Keterangan (opsional)" tipe="textarea"
                                     maxlength="1000" placeholder="Info tambahan, jumlah peserta, dsb." />

                            <button type="submit" class="w-full bg-linear-to-b from-hkbp-800 to-hkbp-900 hover:from-hkbp-700 hover:to-hkbp-800 text-white text-sm font-bold py-3.5 rounded-xl shadow-brand-sm transition-all duration-200 flex items-center justify-center gap-2">
                                <i data-lucide="send" class="w-4 h-4" aria-hidden="true"></i> Kirim Permohonan
                            </button>

                            <p class="text-xs text-slate-500 text-center">
                                Tanda <span class="text-red-600" aria-hidden="true">*</span> menandakan isian wajib.
                            </p>
                        </form>

                        {{-- Di luar <form>: menempatkannya di dalam membuat tautan ini
                             ikut terbaca sebagai bagian dari isian yang harus dikirim. --}}
                        <p class="mt-6 pt-6 border-t border-slate-100 text-center text-sm text-slate-600">
                            Sudah pernah mengirim permohonan?
                            <a href="{{ route('penggunaan-gereja.lacak') }}" class="inline-block py-2 font-bold text-hkbp-800 hover:text-hkbp-900 underline underline-offset-4">
                                Lacak statusnya di sini
                            </a>
                        </p>
                    </div>
                </div>

                {{-- DAFTAR JADWAL --}}
                <div class="lg:col-span-3 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-1">
                        <h2 class="text-xl font-black text-hkbp-900 flex items-center gap-2">
                            <i data-lucide="calendar-days" class="w-5 h-5 text-gold-700" aria-hidden="true"></i> Jadwal Penggunaan Gedung
                        </h2>
                        <a href="{{ route('penggunaan-gereja.kalender') }}" class="inline-flex items-center gap-2 min-h-11 px-4 rounded-xl bg-white border border-slate-300 text-hkbp-800 hover:border-hkbp-800 text-sm font-bold shadow-brand-sm">
                            <i data-lucide="calendar-check" class="w-4 h-4" aria-hidden="true"></i> Unduh Kalender
                        </a>
                    </div>
                    <p class="text-sm text-slate-500 mb-6">Daftar kegiatan yang akan menggunakan gedung gereja. Silakan cek dulu sebelum mengajukan permohonan.</p>

                    <form action="{{ route('penggunaan-gereja') }}" method="GET" class="mb-6 flex gap-3 items-end">
                        <div class="flex-1">
                            <label for="bulan" class="block text-sm font-bold text-hkbp-900 mb-1">Tampilkan bulan</label>
                            <select id="bulan" name="bulan" data-auto-submit class="w-full min-h-11 rounded-xl border border-slate-300 bg-white px-4 text-slate-700 focus:outline-none focus:ring-2 focus:ring-hkbp-800/30">
                                <option value="">Semua jadwal mendatang</option>
                                @foreach($bulanTersedia as $opsi)
                                    <option value="{{ $opsi['nilai'] }}" @selected($bulan === $opsi['nilai'])>{{ $opsi['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($bulan)
                            <a href="{{ route('penggunaan-gereja') }}" class="inline-flex items-center min-h-11 px-4 rounded-xl text-sm font-bold text-slate-600 bg-white border border-slate-300 hover:text-hkbp-900">Reset</a>
                        @endif
                    </form>

                    <div class="space-y-4">
                        @forelse($penggunaans as $item)
                            @php $disetujui = $item->status === \App\Models\PenggunaanGereja::DISETUJUI @endphp

                            {{-- Bertumpuk di ponsel. Sebaris dengan lencana status, kolom
                                 teksnya hanya tersisa ~110px pada layar 390px: nama kegiatan
                                 pecah jadi empat baris dan "19:00 - 21:00 WIB" ikut terpotong.
                                 Lencana baru berdampingan mulai breakpoint sm. --}}
                            <article class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-brand-sm hover:shadow-brand transition-shadow duration-300 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                <div class="flex items-start gap-4 min-w-0">
                                    <p class="bg-blue-50 text-hkbp-800 rounded-xl px-3 py-2 text-center shrink-0 w-20 ring-1 ring-inset ring-blue-100">
                                        <time datetime="{{ $item->tanggal->toDateString() }}">
                                            <span class="block text-[11px] font-bold uppercase">{{ $item->tanggal->translatedFormat('M') }}</span>
                                            <span class="block text-xl font-black leading-none">{{ $item->tanggal->format('d') }}</span>
                                            <span class="block text-[11px] font-semibold">{{ $item->tanggal->format('Y') }}</span>
                                        </time>
                                    </p>

                                    {{-- Teks bebas dari formulir publik HANYA ditampilkan setelah
                                         pengurus menyetujuinya. Formulir ini terbuka tanpa login,
                                         sehingga menayangkan nama kegiatan, nama pemohon, dan
                                         keterangan milik permohonan yang masih "Menunggu" berarti
                                         siapa pun bisa menerbitkan tulisan apa pun di halaman
                                         gereja. Slotnya tetap ditampilkan (tanggal & jam) supaya
                                         pemohon berikutnya tetap bisa menghindari bentrok. --}}
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-hkbp-900 text-balance">
                                            {{ $disetujui ? $item->nama_kegiatan : 'Permohonan menunggu konfirmasi' }}
                                        </h3>
                                        <p class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 font-semibold mt-1">
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="clock" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                                {{ $item->waktu_mulai?->format('H:i') }} - {{ $item->waktu_selesai?->format('H:i') }} WIB
                                            </span>
                                            @if($disetujui)
                                                <span class="flex items-center gap-1">
                                                    <i data-lucide="user" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                                    {{ $item->nama_pemohon }}
                                                </span>
                                            @endif
                                        </p>
                                        @if(! $disetujui)
                                            <p class="text-xs text-slate-500 mt-2">Rincian kegiatan ditampilkan setelah pengurus mengonfirmasi.</p>
                                        @elseif($item->keterangan)
                                            <p class="text-xs text-slate-500 mt-2">{{ $item->keterangan }}</p>
                                        @endif
                                    </div>
                                </div>

                                <p class="self-start shrink-0 inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-md {{ $disetujui ? 'bg-green-100 text-green-900' : 'bg-amber-100 text-amber-900' }}">
                                    <i data-lucide="{{ $disetujui ? 'circle-check' : 'hourglass' }}" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                    {{ $disetujui ? 'Terkonfirmasi' : 'Menunggu' }}
                                </p>
                            </article>
                        @empty
                            <x-empty-state
                                ikon="calendar-x"
                                pesan="Belum ada jadwal penggunaan gedung dalam waktu dekat." />
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </section>
</x-layout>
