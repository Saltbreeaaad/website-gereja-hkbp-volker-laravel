<x-filament-panels::page>
    <div class="max-w-3xl space-y-6">
        @php $aktif = auth()->user()->twoFactorAktif(); @endphp

        @if($aktif)
            @php $sisa = $this->sisaKodePemulihan(); @endphp

            <x-filament::section>
                <x-slot name="heading">Autentikasi dua faktor aktif</x-slot>
                <x-slot name="description">Setiap login baru membutuhkan kode enam digit dari aplikasi autentikator.</x-slot>

                {{-- Sisa kode pemulihan ditampilkan terus-menerus, bukan hanya saat
                     habis: kehabisan kode baru terasa persis pada saat ponselnya
                     hilang, yaitu saat sudah terlambat untuk menerbitkan ulang.

                     Memakai <x-filament::badge>, bukan kelas warna sendiri.
                     CSS panel hanya memuat kelas yang dipakai Filament: dari
                     bg-success-*/bg-warning-* yang sempat ditulis di sini tidak
                     satu pun ada di public/css/filament/filament/app.css, jadi
                     penandanya akan tampil polos justru pada keadaan sehat. --}}
                <div class="mb-5">
                    <x-filament::badge :color="$sisa === 0 ? 'danger' : ($sisa <= 3 ? 'warning' : 'success')">
                        @if($sisa === 0)
                            Tidak ada kode pemulihan tersisa — terbitkan ulang segera
                        @else
                            Sisa {{ $sisa }} kode pemulihan
                        @endif
                    </x-filament::badge>

                    @if($sisa === 0)
                        <p class="mt-2 text-sm text-gray-500">Kehilangan ponsel sekarang berarti kehilangan akses ke akun ini.</p>
                    @endif
                </div>

                <form wire:submit="nonaktifkan" class="space-y-4">
                    <div>
                        <label for="kataSandi" class="block text-sm font-medium mb-1">Kata sandi akun</label>
                        <input wire:model="kataSandi" id="kataSandi" type="password" autocomplete="current-password" class="fi-input block w-full max-w-xs rounded-lg border-gray-300" required>
                        @error('kataSandi')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="kode" class="block text-sm font-medium mb-1">Kode autentikator</label>
                        <input wire:model="kode" id="kode" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="fi-input block w-full max-w-xs rounded-lg border-gray-300" required>
                        @error('kode')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <x-filament::button type="submit" color="danger">Nonaktifkan 2FA</x-filament::button>
                        <x-filament::button type="button" color="gray" wire:click="buatUlangKodePemulihan">Terbitkan ulang kode pemulihan</x-filament::button>
                    </div>
                    <p class="text-xs text-gray-500">Keduanya memerlukan kata sandi dan kode autentikator yang berlaku saat ini.</p>
                </form>
            </x-filament::section>
        @else
            <x-filament::section>
                <x-slot name="heading">Aktifkan autentikasi dua faktor</x-slot>
                <x-slot name="description">Tambahkan akun secara manual di Google Authenticator, Microsoft Authenticator, Authy, atau aplikasi TOTP lain.</x-slot>

                <div class="space-y-5">
                    <div class="rounded-xl bg-gray-50 dark:bg-white/5 p-4">
                        <p class="text-sm font-medium">Kunci penyiapan</p>
                        <p class="mt-2 font-mono text-lg font-bold tracking-wider break-all select-all">{{ $rahasia }}</p>
                        <p class="mt-2 text-xs text-gray-500 break-all">URI: {{ $this->uriAuthenticator() }}</p>
                    </div>

                    <form wire:submit="aktifkan" class="space-y-4">
                        <div>
                            <label for="kataSandiAktif" class="block text-sm font-medium mb-1">Kata sandi akun</label>
                            <input wire:model="kataSandi" id="kataSandiAktif" type="password" autocomplete="current-password" class="fi-input block w-full max-w-xs rounded-lg border-gray-300" required>
                            @error('kataSandi')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="kodeAktif" class="block text-sm font-medium mb-1">Masukkan kode enam digit untuk konfirmasi</label>
                            <input wire:model="kode" id="kodeAktif" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="fi-input block w-full max-w-xs rounded-lg border-gray-300" required>
                            @error('kode')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <x-filament::button type="submit">Aktifkan 2FA</x-filament::button>
                    </form>
                </div>
            </x-filament::section>
        @endif

        @if($kodePemulihan)
            <x-filament::section>
                <x-slot name="heading">Simpan kode pemulihan sekarang</x-slot>
                <x-slot name="description">Masing-masing hanya dapat dipakai sekali. Kode ini tidak akan ditampilkan lagi.</x-slot>
                <div class="grid sm:grid-cols-2 gap-2 font-mono">
                    @foreach($kodePemulihan as $pemulihan)<code class="rounded-lg bg-gray-100 dark:bg-white/5 p-3 select-all">{{ $pemulihan }}</code>@endforeach
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
