<?php

namespace Database\Seeders;

use App\Models\Galeri;
use App\Models\JadwalIbadah;
use App\Models\KasGereja;
use App\Models\Parhalado;
use App\Models\PenggunaanGereja;
use App\Models\Renungan;
use App\Models\User;
use App\Models\WartaJemaat;
use Illuminate\Database\Seeder;

/**
 * Data contoh untuk instalasi baru.
 *
 * Kunci pencarian harus berupa identitas yang STABIL (judul, nama, email) —
 * jangan pernah memakai kolom tanggal yang diturunkan dari `today()`. Kunci
 * bertanggal membuat seeder menghasilkan baris baru setiap kali dijalankan di
 * hari yang berbeda, dan itulah yang dulu menggandakan isi renungan.
 *
 * Jadwal ibadah memakai updateOrCreate supaya tanggalnya selalu maju ke
 * kemunculan berikutnya — kalau tidak, seluruh jadwal jatuh ke masa lalu dan
 * beranda (yang menyaring `tanggal >= hari ini`) menjadi kosong.
 *
 * Seeder ini untuk instalasi baru / demo. Jangan dijalankan begitu saja pada
 * basis data yang sudah berisi data jemaat sungguhan.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Data contoh tidak boleh pernah masuk ke basis data sungguhan. Yang
        // paling berbahaya adalah akun admin berkata sandi `password` dan baris
        // kas gereja palsu: keduanya diam-diam terlihat sah.
        if (app()->isProduction()) {
            $this->command?->error('Seeder ini berisi data contoh dan tidak dijalankan di produksi.');
            $this->command?->line('Gunakan `php artisan hkbp:akun` untuk membuat akun pengurus.');

            return;
        }

        $this->akunAdmin();
        $this->renungan();
        $this->jadwalIbadah();
        $this->parhalado();
        $this->kas();
        $this->galeri();
        $this->wartaJemaat();
        $this->penggunaanGereja();
    }

    private function akunAdmin(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@hkbpvolker.test'],
            ['name' => 'Administrator', 'password' => 'password', 'role' => User::ADMIN]
        );
    }

    private function renungan(): void
    {
        $data = [
            [
                'judul' => 'Menemukan Damai di Tengah Badai',
                'tanggal' => today(),
                'penulis' => 'Pendeta Paul Benedict, S.Th.',
                'isi' => 'Seringkali dalam hidup ini kita menghadapi tantangan yang terasa seperti '
                    ."badai besar. Namun, ketahuilah bahwa Tuhan selalu berada di perahu yang sama \n"
                    .'dengan kita. Seperti saat Ia meredakan badai di Danau Galilea, Ia juga sanggup '
                    .'memberikan kedamaian di hati kita yang sedang kalut. Mari kita terus bersandar '
                    .'pada janji-Nya dan tidak kehilangan pengharapan. Tuhan memberkati.',
            ],
            [
                'judul' => 'Kasih yang Mengampuni dan Memulihkan',
                'tanggal' => today()->subDays(3),
                'penulis' => 'St. J. Sitorus',
                'isi' => 'Mengampuni bukanlah hal yang mudah, terutama ketika kita disakiti oleh orang '
                    .'terdekat. Tetapi firman Tuhan mengingatkan kita untuk saling mengampuni, '
                    .'sebagaimana Allah di dalam Kristus telah mengampuni kita. Kasih yang sejati '
                    .'membebaskan kita dari belenggu dendam dan membawa pemulihan yang indah dalam '
                    .'persekutuan jemaat kita.',
            ],
            [
                'judul' => 'Bersyukur dalam Segala Keadaan',
                'tanggal' => today()->subDays(7),
                'penulis' => 'St. M. Simanjuntak',
                'isi' => 'Rasul Paulus mengajarkan kita untuk mengucap syukur dalam segala hal, baik '
                    .'dalam suka maupun duka. Bersyukur bukan berarti mengabaikan kesulitan yang ada, '
                    .'melainkan percaya bahwa Tuhan tetap berkarya di balik setiap keadaan. Mari kita '
                    .'jadikan syukur sebagai gaya hidup, bukan hanya ucapan di bibir.',
            ],
            [
                'judul' => 'Melayani dengan Hati yang Tulus',
                'tanggal' => today()->subDays(14),
                'penulis' => 'R. Panjaitan',
                'isi' => 'Pelayanan yang sejati lahir dari hati yang tulus, bukan karena ingin dipuji '
                    .'atau dilihat orang. Yesus sendiri memberi teladan dengan membasuh kaki '
                    .'murid-murid-Nya. Sebagai jemaat, mari kita terus melayani sesama dengan penuh '
                    .'kerendahan hati, sebagaimana Kristus telah melayani kita terlebih dahulu.',
            ],
        ];

        foreach ($data as $item) {
            // Kunci pada judul, bukan tanggal: tanggal bergerak mengikuti hari
            // seeder dijalankan dan akan menggandakan renungan yang sama.
            Renungan::firstOrCreate(['judul' => $item['judul']], $item);
        }
    }

    private function jadwalIbadah(): void
    {
        $data = [
            ['nama_ibadah' => 'Ibadah Minggu - Bahasa Indonesia', 'hari' => 0, 'waktu' => '07:00'],
            ['nama_ibadah' => 'Ibadah Minggu - Bahasa Batak', 'hari' => 0, 'waktu' => '10:00'],
            ['nama_ibadah' => 'Partangiangan Wijk', 'hari' => 3, 'waktu' => '19:00'],
            ['nama_ibadah' => 'Ibadah Kaum Bapak', 'hari' => 5, 'waktu' => '18:30'],
            ['nama_ibadah' => 'Sekolah Minggu', 'hari' => 0, 'waktu' => '08:30'],
        ];

        foreach ($data as $item) {
            // Kunci hanya pada nama ibadah; tanggal ikut diperbarui ke kemunculan
            // terdekat yang belum lewat supaya beranda selalu punya isi.
            JadwalIbadah::updateOrCreate(
                ['nama_ibadah' => $item['nama_ibadah']],
                [
                    'tanggal' => today()->next($item['hari']),
                    'waktu' => $item['waktu'],
                    'pelayan_firman' => 'Pendeta Paul Benedict, S.Th.',
                    'keterangan' => 'Mohon hadir 15 menit sebelum ibadah dimulai.',
                ]
            );
        }
    }

    private function parhalado(): void
    {
        $data = [
            // Ejaan nama harus sama persis dengan yang dipakai jemaat, kalau tidak
            // firstOrCreate menganggapnya orang lain dan membuat entri kembar.
            ['nama' => 'Pendeta Paul Benedict, S.Th.', 'kategori' => 'Pendeta', 'jabatan' => 'Pimpinan Jemaat', 'bidang' => 'Pendeta', 'telepon' => '081234500001'],
            ['nama' => 'St. J. Sitorus', 'kategori' => 'Parhalado', 'jabatan' => 'Sintua Wijk 1', 'bidang' => 'Dewan Koinonia', 'telepon' => '081234500002'],
            ['nama' => 'St. M. Simanjuntak', 'kategori' => 'Parhalado', 'jabatan' => 'Sintua Wijk 2', 'bidang' => 'Dewan Diakonia', 'telepon' => '081234500003'],
            ['nama' => 'St. B. Hutagalung', 'kategori' => 'Parhalado', 'jabatan' => 'Sintua Wijk 3', 'bidang' => 'Dewan Marturia', 'telepon' => '081234500004'],
            ['nama' => 'R. Panjaitan', 'kategori' => 'Kategorial', 'jabatan' => 'Ketua Naposobulung', 'bidang' => 'Remaja & Naposobulung', 'telepon' => '081234500005'],
            ['nama' => 'D. Sinaga', 'kategori' => 'Kategorial', 'jabatan' => 'Ketua Wanita HKBP', 'bidang' => 'Kaum Ibu', 'telepon' => '081234500006'],
            ['nama' => 'H. Tampubolon', 'kategori' => 'Kategorial', 'jabatan' => 'Ketua Kaum Bapak', 'bidang' => 'Kaum Bapak', 'telepon' => '081234500007'],
        ];

        foreach ($data as $item) {
            Parhalado::firstOrCreate(['nama' => $item['nama']], $item);
        }
    }

    private function kas(): void
    {
        $data = [
            ['keterangan' => 'Persembahan Minggu I', 'jenis' => 'Pemasukan', 'nominal' => 4_500_000],
            ['keterangan' => 'Persembahan Minggu II', 'jenis' => 'Pemasukan', 'nominal' => 3_800_000],
            ['keterangan' => 'Persembahan Minggu III', 'jenis' => 'Pemasukan', 'nominal' => 4_100_000],
            ['keterangan' => 'Perpuluhan Jemaat', 'jenis' => 'Pemasukan', 'nominal' => 2_650_000],
            ['keterangan' => 'Biaya Listrik & Air', 'jenis' => 'Pengeluaran', 'nominal' => 1_200_000],
            ['keterangan' => 'Perawatan Gedung', 'jenis' => 'Pengeluaran', 'nominal' => 2_100_000],
            ['keterangan' => 'Honor Pelayan & Operasional', 'jenis' => 'Pengeluaran', 'nominal' => 3_000_000],
            ['keterangan' => 'Pembelian Alat Musik', 'jenis' => 'Pengeluaran', 'nominal' => 1_750_000],
        ];

        foreach ($data as $i => $item) {
            KasGereja::firstOrCreate(
                ['keterangan' => $item['keterangan']],
                [...$item, 'tanggal' => today()->subDays(($i + 1) * 7)]
            );
        }
    }

    private function galeri(): void
    {
        // Judul juga harus sama persis dengan milik jemaat — lihat catatan pada
        // daftar parhalado di atas.
        $data = [
            'Perayaan Paskah & Perjamuan Kudus' => 10,
            'Kegiatan Gotong Royong Membersihkan Gereja' => 15,
            'Retreat Naposobulung (Pemuda) 2026' => 30,
            'Perayaan Natal Jemaat' => 45,
            'Baptisan Kudus Anak Jemaat' => 60,
        ];

        foreach ($data as $judul => $hariLalu) {
            Galeri::firstOrCreate(
                ['judul' => $judul],
                ['tanggal' => today()->subDays($hariLalu), 'foto' => null]
            );
        }
    }

    private function wartaJemaat(): void
    {
        $data = [
            'Warta Jemaat Minggu Ini' => 0,
            'Warta Jemaat Minggu Lalu' => 7,
            'Warta Jemaat 2 Minggu Lalu' => 14,
            'Warta Jemaat Edisi Natal' => 45,
        ];

        foreach ($data as $judul => $hariLalu) {
            WartaJemaat::firstOrCreate(
                ['judul' => $judul],
                ['tanggal' => today()->subDays($hariLalu), 'file_warta' => null]
            );
        }
    }

    private function penggunaanGereja(): void
    {
        $data = [
            [
                'nama_kegiatan' => 'Pernikahan Keluarga Sitorus',
                'nama_pemohon' => 'Andi Sitorus',
                'kontak' => '081234511111',
                'hari' => 5,
                'waktu_mulai' => '09:00',
                'waktu_selesai' => '12:00',
                'status' => PenggunaanGereja::DISETUJUI,
                'catatan_admin' => 'Disetujui, mohon koordinasi dengan koster untuk dekorasi.',
            ],
            [
                'nama_kegiatan' => 'Ulang Tahun Sekolah Minggu',
                'nama_pemohon' => 'Ny. D. Sinaga',
                'kontak' => '081234522222',
                'hari' => 10,
                'waktu_mulai' => '14:00',
                'waktu_selesai' => '17:00',
                'status' => PenggunaanGereja::MENUNGGU,
                'catatan_admin' => null,
            ],
            [
                'nama_kegiatan' => 'Latihan Koor Paduan Suara',
                'nama_pemohon' => 'H. Tampubolon',
                'kontak' => '081234533333',
                'hari' => 3,
                'waktu_mulai' => '19:00',
                'waktu_selesai' => '21:00',
                'status' => PenggunaanGereja::DISETUJUI,
                'catatan_admin' => 'Disetujui, gunakan pintu samping.',
            ],
            [
                'nama_kegiatan' => 'Syukuran Ulang Tahun Pribadi',
                'nama_pemohon' => 'Rina Panjaitan',
                'kontak' => '081234544444',
                'hari' => 6,
                'waktu_mulai' => '10:00',
                'waktu_selesai' => '13:00',
                'status' => PenggunaanGereja::DITOLAK,
                'catatan_admin' => 'Ditolak karena bentrok dengan jadwal ibadah wijk.',
            ],
            [
                'nama_kegiatan' => 'Rapat Panitia Natal',
                'nama_pemohon' => 'St. B. Hutagalung',
                'kontak' => '081234555555',
                'hari' => 8,
                'waktu_mulai' => '18:00',
                'waktu_selesai' => '20:00',
                'status' => PenggunaanGereja::MENUNGGU,
                'catatan_admin' => null,
            ],
        ];

        foreach ($data as $item) {
            PenggunaanGereja::firstOrCreate(
                ['nama_kegiatan' => $item['nama_kegiatan']],
                [
                    'nama_pemohon' => $item['nama_pemohon'],
                    'kontak' => $item['kontak'],
                    'tanggal' => today()->addDays($item['hari']),
                    'waktu_mulai' => $item['waktu_mulai'],
                    'waktu_selesai' => $item['waktu_selesai'],
                    'keterangan' => 'Mohon izin penggunaan gedung gereja untuk kegiatan ini.',
                    'status' => $item['status'],
                    'catatan_admin' => $item['catatan_admin'],
                ]
            );
        }
    }
}
