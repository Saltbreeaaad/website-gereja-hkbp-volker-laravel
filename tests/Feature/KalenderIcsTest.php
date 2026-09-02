<?php

namespace Tests\Feature;

use App\Models\JadwalIbadah;
use App\Models\PenggunaanGereja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kepatuhan berkas .ics terhadap RFC 5545 §3.1.
 *
 * Baris konten dibatasi 75 oktet. `keterangan` boleh sampai 1.000 karakter,
 * jadi tanpa pelipatan satu DESCRIPTION saja sudah melanggarnya belasan kali.
 */
class KalenderIcsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function agenda_tidak_punya_baris_melebihi_75_oktet(): void
    {
        JadwalIbadah::factory()->create([
            'nama_ibadah' => str_repeat('Ibadah Syukur Tahun Baru Bersama Seluruh Jemaat ', 6),
            'keterangan' => str_repeat('Keterangan yang sangat panjang sekali. ', 30),
            'tanggal' => today()->addWeek(),
            'waktu' => '09:00',
        ]);

        $this->pastikanTiapBarisPatuh($this->get(route('agenda.kalender'))->assertOk()->getContent());
    }

    #[Test]
    public function jadwal_gedung_tidak_punya_baris_melebihi_75_oktet(): void
    {
        PenggunaanGereja::factory()->create([
            'status' => PenggunaanGereja::DISETUJUI,
            'tanggal' => today()->addWeek(),
            'nama_kegiatan' => str_repeat('Latihan Koor Naposobulung Gabungan ', 8),
            'keterangan' => str_repeat('Rincian acara yang panjang. ', 40),
        ]);

        $this->pastikanTiapBarisPatuh($this->get(route('penggunaan-gereja.kalender'))->assertOk()->getContent());
    }

    /** Karakter non-ASCII tidak boleh terpotong di tengah oleh pelipatan. */
    #[Test]
    public function pelipatan_tidak_merusak_karakter_multibita(): void
    {
        JadwalIbadah::factory()->create([
            'nama_ibadah' => str_repeat('Perayaan Natal — Horas! ', 12),
            'tanggal' => today()->addWeek(),
            'waktu' => '09:00',
        ]);

        $isi = $this->get(route('agenda.kalender'))->assertOk()->getContent();

        $this->pastikanTiapBarisPatuh($isi);
        $this->assertTrue(mb_check_encoding($isi, 'UTF-8'), 'Berkas .ics harus tetap UTF-8 yang sah.');

        // Baris yang dilipat disambung kembali: teks aslinya harus utuh.
        $disambung = str_replace("\r\n ", '', $isi);
        $this->assertStringContainsString('Perayaan Natal — Horas!', $disambung);
    }

    private function pastikanTiapBarisPatuh(string $isi): void
    {
        foreach (explode("\r\n", $isi) as $nomor => $baris) {
            $this->assertLessThanOrEqual(
                75,
                strlen($baris),
                'Baris '.($nomor + 1).' melebihi 75 oktet: '.substr($baris, 0, 90),
            );
        }
    }
}
