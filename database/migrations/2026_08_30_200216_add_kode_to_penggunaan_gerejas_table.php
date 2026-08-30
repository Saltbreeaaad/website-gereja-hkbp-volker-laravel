<?php

use App\Models\PenggunaanGereja;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kode penelusuran permohonan.
 *
 * Sebelumnya pemohon mengirim formulir lalu tidak punya cara apa pun untuk
 * mengetahui hasilnya: `catatan_admin` (tempat pengurus menulis alasan
 * penolakan) tidak pernah ditampilkan ke siapa pun. Kode ini yang dipegang
 * pemohon untuk membuka halaman status permohonannya.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Ditambahkan nullable dulu supaya baris yang sudah ada tidak menabrak
        // batasan unique, baru diisi dan dikunci setelahnya.
        Schema::table('penggunaan_gerejas', function (Blueprint $table) {
            $table->string('kode', 12)->nullable()->after('id');
        });

        DB::table('penggunaan_gerejas')->whereNull('kode')->orderBy('id')
            ->each(function (object $baris) {
                DB::table('penggunaan_gerejas')
                    ->where('id', $baris->id)
                    ->update(['kode' => PenggunaanGereja::kodeBaru()]);
            });

        // Indeks unique dipasang sebagai pernyataan tersendiri, bukan dirangkai
        // ke `change()`: di dalam change() Laravel hanya memperbarui definisi
        // kolom, dan `unique()` yang menempel di sana bisa hilang tanpa suara.
        Schema::table('penggunaan_gerejas', function (Blueprint $table) {
            $table->string('kode', 12)->nullable(false)->change();
        });

        Schema::table('penggunaan_gerejas', function (Blueprint $table) {
            $table->unique('kode');
        });
    }

    public function down(): void
    {
        Schema::table('penggunaan_gerejas', function (Blueprint $table) {
            $table->dropUnique(['kode']);
            $table->dropColumn('kode');
        });
    }
};
