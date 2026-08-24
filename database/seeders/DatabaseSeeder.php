<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Renungan;
use App\Models\Galeri;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. DATA DUMMY RENUNGAN
        Renungan::create([
            'judul' => 'Menemukan Damai di Tengah Badai',
            'tanggal' => Carbon::now()->subDays(2), // 2 hari yang lalu
            'penulis' => 'Pdt. Paul Benedict, S.Th.',
            'isi' => 'Seringkali dalam hidup ini kita menghadapi tantangan yang terasa seperti badai besar. Namun, ketahuilah bahwa Tuhan selalu berada di perahu yang sama dengan kita. Seperti saat Ia meredakan badai di Danau Galilea, Ia juga sanggup memberikan kedamaian di hati kita yang sedang kalut. Mari kita terus bersandar pada janji-Nya dan tidak kehilangan pengharapan. Tuhan memberkati.',
        ]);

        Renungan::create([
            'judul' => 'Kasih yang Mengampuni dan Memulihkan',
            'tanggal' => Carbon::now()->subDays(5), // 5 hari yang lalu
            'penulis' => 'St. J. Sitorus',
            'isi' => 'Mengampuni bukanlah hal yang mudah, terutama ketika kita disakiti oleh orang terdekat. Tetapi firman Tuhan mengingatkan kita untuk saling mengampuni, sebagaimana Allah di dalam Kristus telah mengampuni kita. Kasih yang sejati membebaskan kita dari belenggu dendam dan membawa pemulihan yang indah dalam persekutuan jemaat kita.',
        ]);

        // 2. DATA DUMMY GALERI
        Galeri::create([
            'judul' => 'Perayaan Paskah & Perjamuan Kudus',
            'tanggal' => Carbon::now()->subDays(10),
            'foto' => '', // Dikosongkan sementara
        ]);

        Galeri::create([
            'judul' => 'Kegiatan Gotong Royong Membersihkan Gereja',
            'tanggal' => Carbon::now()->subDays(15),
            'foto' => '', // Dikosongkan sementara
        ]);

        Galeri::create([
            'judul' => 'Retreat Naposobulung (Pemuda) 2026',
            'tanggal' => Carbon::now()->subDays(30),
            'foto' => '', // Dikosongkan sementara
        ]);
    }
}