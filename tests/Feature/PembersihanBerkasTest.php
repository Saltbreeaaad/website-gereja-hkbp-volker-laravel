<?php

namespace Tests\Feature;

use App\Models\Galeri;
use App\Models\Parhalado;
use App\Models\WartaJemaat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PembersihanBerkasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function unggah(string $nama): string
    {
        $path = 'galeri-photos/'.$nama;
        Storage::disk('public')->put($path, 'isi berkas');

        return $path;
    }

    #[Test]
    public function menghapus_foto_galeri_ikut_menghapus_berkasnya(): void
    {
        $galeri = Galeri::factory()->create(['foto' => $this->unggah('paskah.jpg')]);

        Storage::disk('public')->assertExists($galeri->foto);

        $galeri->delete();

        Storage::disk('public')->assertMissing('galeri-photos/paskah.jpg');
    }

    #[Test]
    public function mengganti_foto_membuang_berkas_lamanya(): void
    {
        $galeri = Galeri::factory()->create(['foto' => $this->unggah('lama.jpg')]);

        $galeri->update(['foto' => $this->unggah('baru.jpg')]);

        Storage::disk('public')->assertMissing('galeri-photos/lama.jpg');
        Storage::disk('public')->assertExists('galeri-photos/baru.jpg');
    }

    #[Test]
    public function mengosongkan_kolom_foto_juga_membuang_berkasnya(): void
    {
        $pelayan = Parhalado::factory()->create(['foto' => $this->unggah('sintua.jpg')]);

        $pelayan->update(['foto' => null]);

        Storage::disk('public')->assertMissing('galeri-photos/sintua.jpg');
    }

    #[Test]
    public function menyunting_kolom_lain_tidak_menyentuh_berkasnya(): void
    {
        $galeri = Galeri::factory()->create(['foto' => $this->unggah('tetap.jpg')]);

        $galeri->update(['judul' => 'Judul Baru']);

        Storage::disk('public')->assertExists('galeri-photos/tetap.jpg');
    }

    #[Test]
    public function berkas_yang_masih_dirujuk_baris_lain_dipertahankan(): void
    {
        $path = $this->unggah('dipakai-berdua.jpg');

        $pertama = Galeri::factory()->create(['foto' => $path]);
        Galeri::factory()->create(['foto' => $path]);

        $pertama->delete();

        Storage::disk('public')->assertExists($path);
    }

    #[Test]
    public function penghapusan_massal_ikut_membersihkan_berkas(): void
    {
        $a = Galeri::factory()->create(['foto' => $this->unggah('a.jpg')]);
        $b = Galeri::factory()->create(['foto' => $this->unggah('b.jpg')]);

        Galeri::query()->whereKey([$a->id, $b->id])->get()->each->delete();

        Storage::disk('public')->assertMissing('galeri-photos/a.jpg');
        Storage::disk('public')->assertMissing('galeri-photos/b.jpg');
    }

    #[Test]
    public function berkas_warta_pdf_ikut_dibersihkan(): void
    {
        Storage::disk('public')->put('warta-files/warta-01.pdf', '%PDF-1.4');

        $warta = WartaJemaat::factory()->create(['file_warta' => 'warta-files/warta-01.pdf']);

        $warta->delete();

        Storage::disk('public')->assertMissing('warta-files/warta-01.pdf');
    }

    #[Test]
    public function kolom_berkas_kosong_tidak_menimbulkan_galat(): void
    {
        $galeri = Galeri::factory()->create(['foto' => null]);

        $galeri->delete();

        $this->assertDatabaseMissing('galeri', ['id' => $galeri->id]);
    }
}
