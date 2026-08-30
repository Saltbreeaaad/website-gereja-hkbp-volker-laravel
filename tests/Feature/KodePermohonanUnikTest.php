<?php

namespace Tests\Feature;

use App\Models\PenggunaanGereja;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Keunikan kode ditegakkan di tingkat basis data, bukan hanya oleh pengecekan
 * di PHP. Migrasinya memasang indeks unique lewat pernyataan tersendiri, dan
 * tes ini memastikan indeks itu benar-benar terpasang.
 */
class KodePermohonanUnikTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function basis_data_menolak_dua_permohonan_berkode_sama(): void
    {
        $pertama = PenggunaanGereja::factory()->create();

        $this->expectException(QueryException::class);

        // Lewat query builder, supaya pengecekan di PHP tidak ikut campur dan
        // yang benar-benar diuji adalah batasan di basis data.
        DB::table('penggunaan_gerejas')->insert([
            'kode' => $pertama->kode,
            'nama_kegiatan' => 'Kegiatan Kembar',
            'nama_pemohon' => 'Pemohon Lain',
            'kontak' => '081200000000',
            'tanggal' => today()->addDay()->toDateString(),
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '11:00:00',
            'status' => PenggunaanGereja::MENUNGGU,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    #[Test]
    public function kolom_kode_tidak_boleh_kosong(): void
    {
        $this->expectException(QueryException::class);

        DB::table('penggunaan_gerejas')->insert([
            'kode' => null,
            'nama_kegiatan' => 'Tanpa Kode',
            'nama_pemohon' => 'Pemohon',
            'kontak' => '081200000000',
            'tanggal' => today()->addDay()->toDateString(),
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '11:00:00',
            'status' => PenggunaanGereja::MENUNGGU,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
