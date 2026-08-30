<?php

namespace Database\Factories;

use App\Models\PenggunaanGereja;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PenggunaanGereja> */
class PenggunaanGerejaFactory extends Factory
{
    protected $model = PenggunaanGereja::class;

    public function definition(): array
    {
        return [
            'nama_kegiatan' => 'Latihan Koor',
            'nama_pemohon' => $this->faker->name(),
            'kontak' => '081234567890',
            'tanggal' => today()->addDays(3),
            'waktu_mulai' => '18:00',
            'waktu_selesai' => '20:00',
            'keterangan' => null,
            'status' => PenggunaanGereja::MENUNGGU,
        ];
    }

    public function disetujui(): static
    {
        return $this->state(fn () => ['status' => PenggunaanGereja::DISETUJUI]);
    }

    public function ditolak(): static
    {
        return $this->state(fn () => ['status' => PenggunaanGereja::DITOLAK]);
    }
}
