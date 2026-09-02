<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Support\Totp;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Locked;

class KeamananAkun extends Page
{
    /** Banyaknya kode pemulihan yang dibuat sekali jalan. */
    private const JUMLAH_KODE_PEMULIHAN = 8;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $navigationLabel = 'Keamanan Akun';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.keamanan-akun';

    /**
     * Rahasia TOTP calon dan kode pemulihan sekali-tampil.
     *
     * `#[Locked]` wajib: properti publik Livewire ikut dikirim ke peramban dan
     * dikirim balik pada tiap permintaan, jadi tanpa penguncian ini isinya
     * dapat diganti dari sisi klien — rahasia yang dikonfirmasi pengguna bukan
     * lagi rahasia yang dibuat server, dan daftar kode pemulihan yang tampil
     * bukan lagi yang di-hash ke basis data.
     */
    #[Locked]
    public string $rahasia = '';

    public string $kode = '';

    /**
     * Kata sandi akun, diminta ulang untuk tiap perubahan di halaman ini.
     *
     * Tanpa ini, sesi yang ditinggal terbuka di komputer bersama sudah cukup
     * untuk mematikan 2FA — atau untuk menyalakannya dengan rahasia milik orang
     * lain dan mengunci pemiliknya sendiri. Kode autentikator saja tidak
     * menutupnya: ia terbaca di layar ponsel yang tergeletak di meja yang sama.
     */
    public string $kataSandi = '';

    /** @var list<string> */
    #[Locked]
    public array $kodePemulihan = [];

    public function mount(): void
    {
        if (! $this->pengguna()->twoFactorAktif()) {
            $this->rahasia = Totp::buatRahasia();
        }
    }

    public function aktifkan(): void
    {
        if (! $this->kataSandiBenar()) {
            return;
        }

        if (! Totp::verifikasiSekali($this->rahasia, $this->kode, $this->pengguna()->getKey())) {
            $this->addError('kode', 'Kode tidak cocok. Pastikan waktu ponsel sudah otomatis.');

            return;
        }

        $pengguna = $this->pengguna();
        $kodePemulihan = $this->buatKodePemulihan();

        $pengguna->forceFill([
            'two_factor_secret' => $this->rahasia,
            'two_factor_recovery_codes' => $this->hashKodePemulihan($kodePemulihan),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->kodePemulihan = $kodePemulihan;

        session(['two_factor_verified_user_id' => $pengguna->getKey()]);
        $this->reset('kode', 'kataSandi');
        Notification::make()->title('Autentikasi dua faktor aktif')->success()->send();
    }

    public function nonaktifkan(): void
    {
        if (! $this->kataSandiBenar()) {
            return;
        }

        $pengguna = $this->pengguna();

        if (! Totp::verifikasiSekali((string) $pengguna->two_factor_secret, $this->kode, $pengguna->getKey())) {
            $this->addError('kode', 'Masukkan kode autentikator yang benar untuk menonaktifkan 2FA.');

            return;
        }

        $pengguna->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        session()->forget('two_factor_verified_user_id');
        $this->rahasia = Totp::buatRahasia();
        $this->reset('kode', 'kataSandi', 'kodePemulihan');
        Notification::make()->title('Autentikasi dua faktor dinonaktifkan')->warning()->send();
    }

    /**
     * Terbitkan ulang kedelapan kode pemulihan.
     *
     * Sebelumnya kode hanya dibuat sekali, saat 2FA dinyalakan, tanpa cara
     * menambah. Delapan kode habis terpakai — atau catatannya hilang — berarti
     * akun itu tergantung sepenuhnya pada satu ponsel, dan kehilangan ponsel
     * berarti terkunci permanen sampai seseorang menyunting basis data.
     */
    public function buatUlangKodePemulihan(): void
    {
        if (! $this->kataSandiBenar()) {
            return;
        }

        $pengguna = $this->pengguna();

        if (! $pengguna->twoFactorAktif()) {
            return;
        }

        if (! Totp::verifikasiSekali((string) $pengguna->two_factor_secret, $this->kode, $pengguna->getKey())) {
            $this->addError('kode', 'Masukkan kode autentikator yang benar untuk menerbitkan ulang.');

            return;
        }

        $kodePemulihan = $this->buatKodePemulihan();

        $pengguna->forceFill([
            'two_factor_recovery_codes' => $this->hashKodePemulihan($kodePemulihan),
        ])->save();

        $this->kodePemulihan = $kodePemulihan;
        $this->reset('kode', 'kataSandi');
        Notification::make()->title('Kode pemulihan baru diterbitkan')->success()->send();
    }

    /** Sisa kode pemulihan yang belum terpakai; ditampilkan sebagai peringatan dini. */
    public function sisaKodePemulihan(): int
    {
        return count($this->pengguna()->two_factor_recovery_codes ?? []);
    }

    public function uriAuthenticator(): string
    {
        return Totp::uri($this->rahasia, $this->pengguna()->email);
    }

    private function pengguna(): User
    {
        return Auth::user();
    }

    private function kataSandiBenar(): bool
    {
        if (Hash::check($this->kataSandi, $this->pengguna()->password)) {
            return true;
        }

        $this->addError('kataSandi', 'Kata sandi salah.');

        return false;
    }

    /** @return list<string> */
    private function buatKodePemulihan(): array
    {
        return collect(range(1, self::JUMLAH_KODE_PEMULIHAN))
            ->map(fn (): string => strtoupper(str()->random(5).'-'.str()->random(5)))
            ->all();
    }

    /**
     * @param  list<string>  $kodePemulihan
     * @return list<string>
     */
    private function hashKodePemulihan(array $kodePemulihan): array
    {
        return collect($kodePemulihan)->map(fn (string $kode): string => Hash::make($kode))->all();
    }
}
