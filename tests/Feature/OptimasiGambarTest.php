<?php

namespace Tests\Feature;

use App\Models\Galeri;
use App\Support\PengoptimalGambar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OptimasiGambarTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function gambar_baru_diubah_ke_webp_dan_dibuatkan_thumbnail(): void
    {
        Storage::fake('public');
        $gambar = imagecreatetruecolor(2000, 1000);
        ob_start();
        imagejpeg($gambar, null, 90);
        $isi = ob_get_clean();
        imagedestroy($gambar);

        Storage::disk('public')->put('galeri-photos/besar.jpg', $isi);

        $galeri = Galeri::factory()->create(['foto' => 'galeri-photos/besar.jpg']);

        $this->assertSame('galeri-photos/besar.webp', $galeri->fresh()->foto);
        Storage::disk('public')->assertMissing('galeri-photos/besar.jpg');
        Storage::disk('public')->assertExists('galeri-photos/besar.webp');
        Storage::disk('public')->assertExists(PengoptimalGambar::pathThumbnail('galeri-photos/besar.webp'));

        [$lebar] = getimagesize(Storage::disk('public')->path('galeri-photos/besar.webp'));
        $this->assertLessThanOrEqual(1600, $lebar);
    }

    #[Test]
    public function berkas_bukan_gambar_dibiarkan_utuh(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('galeri-photos/rusak.jpg', 'bukan gambar');

        $galeri = Galeri::factory()->create(['foto' => 'galeri-photos/rusak.jpg']);

        $this->assertSame('galeri-photos/rusak.jpg', $galeri->fresh()->foto);
        Storage::disk('public')->assertExists('galeri-photos/rusak.jpg');
    }
}
