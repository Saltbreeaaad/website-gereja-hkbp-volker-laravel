<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Samakan ejaan nama tabel.
 *
 * Laravel menurunkan nama tabel dari nama model dengan penjamakan Inggris,
 * dan generatornya menerapkan itu apa adanya pada model berbahasa Indonesia:
 * `kas_gerejas`, `warta_jemaats`, `parhalados`. Bahasa Indonesia tidak
 * menjamakkan dengan `-s`, jadi yang lahir adalah kata yang bukan Inggris dan
 * bukan Indonesia.
 *
 * Yang membuatnya layak diperbaiki bukan keindahan, melainkan bahwa tabel yang
 * ditulis belakangan sudah tidak ikut pola itu -- `log_aktivitas`,
 * `periode_kas` -- sehingga basis datanya kini memuat dua konvensi sekaligus.
 * Setiap orang yang menulis query mentah harus mengingat tabel mana ikut yang
 * mana.
 *
 * Ini rename, bukan penyuntingan migrasi lama: basis data yang sudah pernah
 * dimigrasikan -- termasuk yang berisi data jemaat sungguhan -- harus bisa ikut
 * berpindah tanpa dibuat ulang.
 *
 * Tabel milik framework (`users`, `cache`, `jobs`, `sessions`, `notifications`)
 * sengaja dibiarkan: namanya bahasa Inggris, penjamakannya benar, dan Laravel
 * sendiri yang menentukannya.
 *
 * Nama indeks dan kunci asing ikut membawa nama tabel lama setelah rename
 * (MySQL tidak ikut mengganti namanya). Itu dibiarkan: namanya tidak pernah
 * disebut kode mana pun, dan mengganti nama indeks adalah operasi yang jauh
 * lebih berisiko daripada manfaatnya.
 */
return new class extends Migration
{
    /**
     * Ditulis satu per satu, bukan sebagai perulangan atas sebuah tabel peta.
     *
     * Larastan membaca berkas migrasi untuk mengetahui kolom apa dimiliki tabel
     * apa. Ia mengikuti pemanggilan `Schema::rename()` yang literal, tetapi
     * tidak dapat menelusuri nama tabel yang baru terbentuk saat program
     * berjalan. Versi pertama migrasi ini memakai perulangan, dan akibatnya
     * analisis statis kehilangan jejak seluruh tabel yang diganti namanya --
     * `Renungan::$updated_at` langsung dilaporkan tidak ada. Yang hilang bukan
     * hanya satu galat itu: setiap kolom pada sepuluh tabel berhenti dikenali,
     * sehingga salah ketik nama kolom tidak lagi tertangkap sebelum dijalankan.
     */
    public function up(): void
    {
        Schema::rename('parhalados', 'parhalado');
        Schema::rename('jadwal_ibadahs', 'jadwal_ibadah');
        Schema::rename('warta_jemaats', 'warta_jemaat');
        Schema::rename('kas_gerejas', 'kas_gereja');
        Schema::rename('renungans', 'renungan');
        Schema::rename('galeris', 'galeri');
        Schema::rename('penggunaan_gerejas', 'penggunaan_gereja');
        Schema::rename('pengumuman_pentings', 'pengumuman_penting');
        Schema::rename('permohonan_doas', 'permohonan_doa');
        Schema::rename('riwayat_status_penggunaan_gerejas', 'riwayat_status_penggunaan_gereja');
    }

    public function down(): void
    {
        Schema::rename('riwayat_status_penggunaan_gereja', 'riwayat_status_penggunaan_gerejas');
        Schema::rename('permohonan_doa', 'permohonan_doas');
        Schema::rename('pengumuman_penting', 'pengumuman_pentings');
        Schema::rename('penggunaan_gereja', 'penggunaan_gerejas');
        Schema::rename('galeri', 'galeris');
        Schema::rename('renungan', 'renungans');
        Schema::rename('kas_gereja', 'kas_gerejas');
        Schema::rename('warta_jemaat', 'warta_jemaats');
        Schema::rename('jadwal_ibadah', 'jadwal_ibadahs');
        Schema::rename('parhalado', 'parhalados');
    }
};
