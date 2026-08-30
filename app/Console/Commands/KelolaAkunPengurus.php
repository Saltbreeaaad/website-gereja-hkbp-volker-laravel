<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * Buat akun pengurus baru, atau ganti kata sandi akun yang sudah ada.
 *
 * Ini jalan resmi menuju akun administrator pertama di server produksi.
 * Sebelumnya satu-satunya cara adalah menjalankan seeder — yang ikut menanam
 * seluruh data contoh, termasuk baris kas gereja palsu — atau menghafal
 * perintah `tinker`.
 *
 * Kata sandi tidak boleh lewat argumen baris perintah: argumen tersimpan di
 * riwayat shell dan terlihat di daftar proses.
 */
class KelolaAkunPengurus extends Command
{
    protected $signature = 'hkbp:akun
                            {email? : Alamat surel pengurus}
                            {--peran= : admin, bendahara, atau sekretaris}';

    protected $description = 'Buat akun pengurus baru atau ganti kata sandinya';

    public function handle(): int
    {
        $email = $this->argument('email') ?: text(
            label: 'Alamat surel pengurus',
            required: true,
        );

        $galat = Validator::make(['email' => $email], ['email' => ['required', 'email']])->errors();

        if ($galat->isNotEmpty()) {
            $this->error($galat->first('email'));

            return self::FAILURE;
        }

        $akun = User::query()->where('email', $email)->first();

        return $akun ? $this->perbarui($akun) : $this->buat($email);
    }

    private function buat(string $email): int
    {
        $nama = text(label: 'Nama lengkap', required: true);
        $peran = $this->peran();
        $sandi = $this->sandiBaru();

        if ($sandi === null) {
            return self::FAILURE;
        }

        User::create([
            'name' => $nama,
            'email' => $email,
            'role' => $peran,
            'password' => $sandi,
        ]);

        $this->info("Akun {$email} dibuat sebagai ".User::PERAN[$peran].'.');

        return self::SUCCESS;
    }

    private function perbarui(User $akun): int
    {
        $this->line("Akun {$akun->email} sudah ada ({$akun->labelPeran()}).");

        $peran = $this->option('peran') ? $this->peran() : $akun->role;
        $sandi = $this->sandiBaru();

        if ($sandi === null) {
            return self::FAILURE;
        }

        // Administrator terakhir tidak boleh diturunkan perannya lewat perintah
        // ini: setelahnya tidak ada seorang pun yang bisa mengembalikannya.
        if ($akun->isAdmin() && $peran !== User::ADMIN && $this->jumlahAdmin() <= 1) {
            $this->error('Ini satu-satunya administrator. Angkat administrator lain dulu sebelum menurunkan perannya.');

            return self::FAILURE;
        }

        $akun->update(['role' => $peran, 'password' => $sandi]);

        $this->info("Kata sandi {$akun->email} diperbarui. Peran: ".User::PERAN[$peran].'.');

        return self::SUCCESS;
    }

    private function peran(): string
    {
        $pilihan = $this->option('peran');

        if ($pilihan !== null && array_key_exists($pilihan, User::PERAN)) {
            return $pilihan;
        }

        if ($pilihan !== null) {
            $this->warn("Peran '{$pilihan}' tidak dikenal.");
        }

        return select(
            label: 'Peran',
            options: User::PERAN,
            default: User::ADMIN,
        );
    }

    /** @return string|null Kata sandi baru, atau null bila konfirmasinya tidak cocok. */
    private function sandiBaru(): ?string
    {
        $sandi = password(
            label: 'Kata sandi baru (minimal 8 karakter)',
            required: true,
            validate: fn (string $nilai): ?string => strlen($nilai) < 8
                ? 'Kata sandi minimal 8 karakter.'
                : null,
        );

        if ($sandi !== password(label: 'Ulangi kata sandi')) {
            $this->error('Kata sandi tidak cocok.');

            return null;
        }

        return $sandi;
    }

    private function jumlahAdmin(): int
    {
        return User::query()->where('role', User::ADMIN)->count();
    }
}
