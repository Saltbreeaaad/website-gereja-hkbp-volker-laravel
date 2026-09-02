<?php

namespace App\Models\Concerns;

use App\Support\PengoptimalGambar;
use Illuminate\Support\Facades\Storage;

trait MengoptimalkanGambar
{
    /** @return array<string, int> Nama kolom => lebar maksimum. */
    abstract protected function kolomGambar(): array;

    public static function bootMengoptimalkanGambar(): void
    {
        static::saved(function (self $model): void {
            foreach ($model->kolomGambar() as $kolom => $lebar) {
                if ($model->wasRecentlyCreated || $model->wasChanged($kolom)) {
                    $model->optimalkanGambar($kolom, $lebar);
                }
            }
        });
    }

    public function optimalkanGambar(string $kolom = 'foto', ?int $lebar = null): bool
    {
        $path = $this->getAttribute($kolom);

        if (! is_string($path) || blank($path)) {
            return false;
        }

        $lebar ??= $this->kolomGambar()[$kolom] ?? 1600;
        $hasil = PengoptimalGambar::optimalkan($path, $lebar);

        if ($hasil === $path) {
            return false;
        }

        $this->setAttribute($kolom, $hasil);
        $this->saveQuietly();

        return true;
    }

    public function urlFoto(): ?string
    {
        $path = $this->getAttribute('foto');

        return is_string($path) && filled($path) ? Storage::disk('public')->url($path) : null;
    }

    public function srcsetFoto(): ?string
    {
        $path = $this->getAttribute('foto');

        if (! is_string($path) || ! str_ends_with(strtolower($path), '.webp')) {
            return null;
        }

        $thumbnail = PengoptimalGambar::pathThumbnail($path);

        if (! Storage::disk('public')->exists($thumbnail)) {
            return null;
        }

        // Deskriptor lebar diambil dari kolomGambar(), bukan angka tetap 1600.
        // Foto pelayan diperkecil ke 800 px dan gambar renungan ke 1200 px;
        // mengaku 1600 membuat peramban mengira ada resolusi yang tidak pernah
        // ada, lalu memilih berkas besar pada layar yang tidak membutuhkannya.
        $lebar = $this->kolomGambar()['foto'] ?? 1600;

        return Storage::disk('public')->url($thumbnail).' '.PengoptimalGambar::LEBAR_THUMBNAIL.'w, '
            .Storage::disk('public')->url($path).' '.$lebar.'w';
    }
}
