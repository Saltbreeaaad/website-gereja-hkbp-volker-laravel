<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\MencatatAktivitas;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string|null $two_factor_secret
 * @property list<string>|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 */
#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MencatatAktivitas, Notifiable;

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
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
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

    public function twoFactorAktif(): bool
    {
        $atribut = $this->getAttributes();

        if (array_key_exists('two_factor_secret', $atribut) && array_key_exists('two_factor_confirmed_at', $atribut)) {
            return filled($atribut['two_factor_secret']) && $atribut['two_factor_confirmed_at'] !== null;
        }

        // Model dari actingAs/session atau query kolom-terbatas dapat tidak
        // membawa kolom nullable. Verifikasi ke DB agar keadaan aktif tidak
        // pernah salah dianggap nonaktif.
        return $this->newQuery()
            ->whereKey($this->getKey())
            ->whereNotNull('two_factor_secret')
            ->whereNotNull('two_factor_confirmed_at')
            ->exists();
    }

    /** Hapus sesi aktif akun ini dari penyimpanan sesi database. */
    public function akhiriSemuaSesi(?string $kecualiId = null): int
    {
        if (config('session.driver') !== 'database') {
            return 0;
        }

        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $this->getKey())
            ->when($kecualiId, fn ($query) => $query->where('id', '!=', $kecualiId))
            ->delete();
    }

    protected static function booted(): void
    {
        static::updated(function (self $user): void {
            if (! $user->wasChanged('password')) {
                return;
            }

            $user->akhiriSemuaSesi(
                app()->runningInConsole() || ! request()->hasSession()
                    ? null
                    : request()->session()->getId(),
            );
        });

        static::deleted(fn (self $user) => $user->akhiriSemuaSesi());
    }
}
