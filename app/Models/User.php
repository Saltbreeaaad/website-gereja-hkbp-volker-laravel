<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ADMIN = 'admin';

    public const BENDAHARA = 'bendahara';

    public const SEKRETARIS = 'sekretaris';

    /** @var array<string, string> Peran => label yang ditampilkan. */
    public const PERAN = [
        self::ADMIN => 'Administrator',
        self::BENDAHARA => 'Bendahara',
        self::SEKRETARIS => 'Sekretaris',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Gerbang akses panel admin.
     *
     * WAJIB ada. Filament\Http\Middleware\Authenticate menolak dengan 403
     * setiap pengguna yang modelnya tidak mengimplementasikan FilamentUser,
     * KECUALI saat APP_ENV=local. Tanpa metode ini panel masih terbuka selama
     * pengembangan, lalu mengunci semua orang keluar pada hari situs naik ke
     * produksi — persis saat pengurus paling butuh masuk.
     *
     * Ketiga peran boleh masuk; yang membedakan adalah apa yang boleh mereka
     * ubah di dalamnya (lihat App\Policies).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return array_key_exists($this->role, self::PERAN);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ADMIN;
    }

    /**
     * Administrator selalu lolos: ia adalah peran pengawas, dan mengharuskan
     * setiap daftar peran menyebutnya secara eksplisit hanya menunggu satu
     * daftar terlupa memasukkannya.
     *
     * @param  list<string>  $peran
     */
    public function berperan(array $peran): bool
    {
        return $this->isAdmin() || in_array($this->role, $peran, strict: true);
    }

    public function labelPeran(): string
    {
        return self::PERAN[$this->role] ?? $this->role;
    }
}
