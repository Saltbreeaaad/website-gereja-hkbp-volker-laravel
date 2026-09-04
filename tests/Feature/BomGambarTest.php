<?php

namespace Tests\Feature;

use App\Filament\Resources\GaleriResource\Pages\CreateGaleri;
use App\Models\Galeri;
use App\Models\User;
use App\Support\PengoptimalGambar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Gambar berdimensi raksasa tidak boleh sampai ke imagecreatefromstring().
 *
 * Berkasnya kecil di disk, jadi batas ukuran unggahan melewatkannya; yang
 * mahal baru muncul saat di-decode, dan kehabisan memori di sana mematikan
 * proses PHP tanpa bisa ditangkap. Karena itu penjaganya harus bekerja
 * sebelum decode, bukan sesudahnya.
 */
class BomGambarTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function gambar_berdimensi_raksasa_dilewati_dan_berkas_asli_dipertahankan(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('galeri-photos/bom.png', $this->pngPalsu(20000, 20000));

        $hasil = PengoptimalGambar::optimalkan('galeri-photos/bom.png', 1600);

        $this->assertSame('galeri-photos/bom.png', $hasil, 'Path asli harus dikembalikan apa adanya.');
        Storage::disk('public')->assertExists('galeri-photos/bom.png');
        Storage::disk('public')->assertMissing('galeri-photos/bom.webp');
    }

    /** Model yang menyimpannya tetap tersimpan; hanya optimasinya yang dilewati. */
    #[Test]
    public function menyimpan_model_dengan_gambar_raksasa_tidak_menggagalkan_penyimpanan(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('galeri-photos/bom.png', $this->pngPalsu(20000, 20000));

        $galeri = Galeri::query()->create([
            'judul' => 'Spanduk Pindaian',
            'kategori' => 'Umum',
            'tanggal' => today(),
            'foto' => 'galeri-photos/bom.png',
        ]);

        $this->assertDatabaseHas('galeri', ['id' => $galeri->id]);
        $this->assertSame('galeri-photos/bom.png', $galeri->fresh()->foto);
    }

    /** Gambar berukuran wajar harus tetap diproses seperti biasa. */
    #[Test]
    public function gambar_wajar_tetap_diubah_ke_webp(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('galeri-photos/wajar.png', $this->pngAsli(120, 90));

        $hasil = PengoptimalGambar::optimalkan('galeri-photos/wajar.png', 1600);

        $this->assertSame('galeri-photos/wajar.webp', $hasil);
        Storage::disk('public')->assertExists('galeri-photos/wajar.webp');
        Storage::disk('public')->assertExists('galeri-photos/wajar-thumb.webp');
        Storage::disk('public')->assertMissing('galeri-photos/wajar.png');
    }

    /**
     * Penjaga yang sesungguhnya diuji di sini.
     *
     * Gambarnya valid dan dapat di-decode, jadi tanpa pemeriksaan dimensi
     * pengoptimal akan mengubahnya ke WebP. Dengan batas piksel diturunkan di
     * bawah ukurannya, ia harus dilewati — inilah perilaku yang menahan bom
     * dekompresi, hanya diperagakan pada skala yang aman untuk mesin penguji.
     */
    #[Test]
    public function gambar_di_atas_batas_piksel_dilewati_walau_dapat_di_decode(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('galeri-photos/besar.png', $this->pngAsli(120, 90));

        config(['gereja.maksimal_piksel_gambar' => 5_000]); // 120 x 90 = 10.800 piksel

        $hasil = PengoptimalGambar::optimalkan('galeri-photos/besar.png', 1600);

        $this->assertSame('galeri-photos/besar.png', $hasil);
        Storage::disk('public')->assertExists('galeri-photos/besar.png');
        Storage::disk('public')->assertMissing('galeri-photos/besar.webp');
    }

    /** Tepat di bawah batas harus tetap lolos — penjaganya bukan penolak buta. */
    #[Test]
    public function gambar_tepat_di_bawah_batas_piksel_tetap_diproses(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('galeri-photos/pas.png', $this->pngAsli(120, 90));

        config(['gereja.maksimal_piksel_gambar' => 10_800]);

        $this->assertSame(
            'galeri-photos/pas.webp',
            PengoptimalGambar::optimalkan('galeri-photos/pas.png', 1600),
        );
    }

    #[Test]
    public function berkas_yang_bukan_gambar_ditolak_tanpa_galat(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('galeri-photos/palsu.png', 'ini bukan gambar sama sekali');

        $this->assertSame(
            'galeri-photos/palsu.png',
            PengoptimalGambar::optimalkan('galeri-photos/palsu.png', 1600),
        );
    }

    /**
     * Lapis pertama: formulir menolaknya lebih dulu.
     *
     * Penjaga di PengoptimalGambar menyelamatkan prosesnya, tetapi diam — foto
     * tetap tersimpan tanpa dioptimalkan dan tidak ada yang memberi tahu
     * pengurus. Aturan `dimensions` di sini yang membuat penolakannya terlihat.
     */
    #[Test]
    public function formulir_menolak_gambar_yang_terlalu_besar_dimensinya(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateGaleri::class)
            ->fillForm([
                'judul' => 'Spanduk Pindaian',
                'kategori' => 'Umum',
                'tanggal' => today(),
                'foto' => [UploadedFile::fake()->image('lebar.png', 6001, 100)],
            ])
            ->call('create')
            ->assertHasFormErrors(['foto']);
    }

    #[Test]
    public function formulir_tetap_menerima_gambar_berukuran_wajar(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateGaleri::class)
            ->fillForm([
                'judul' => 'Ibadah Minggu',
                'kategori' => 'Umum',
                'tanggal' => today(),
                'foto' => [UploadedFile::fake()->image('wajar.png', 800, 600)],
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    /**
     * PNG yang hanya berisi tanda tangan dan satu chunk IHDR.
     *
     * getimagesizefromstring() cukup membaca IHDR untuk melaporkan dimensi,
     * jadi berkas ini menguji penjaganya persis seperti bom sungguhan — tanpa
     * perlu mengalokasikan 20000x20000 piksel di mesin yang menjalankan tes.
     */
    private function pngPalsu(int $lebar, int $tinggi): string
    {
        $ihdr = 'IHDR'.pack('N2', $lebar, $tinggi).pack('C5', 8, 2, 0, 0, 0);

        return "\x89PNG\r\n\x1a\n".pack('N', 13).$ihdr.pack('N', crc32($ihdr));
    }

    private function pngAsli(int $lebar, int $tinggi): string
    {
        $gambar = imagecreatetruecolor($lebar, $tinggi);
        imagefill($gambar, 0, 0, imagecolorallocate($gambar, 200, 210, 220));

        ob_start();
        imagepng($gambar);
        $isi = ob_get_clean();
        imagedestroy($gambar);

        return (string) $isi;
    }
}
