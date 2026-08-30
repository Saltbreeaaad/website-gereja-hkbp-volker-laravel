<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `galeris.foto` dan `warta_jemaats.file_warta` semula NOT NULL, sementara
 * tampilan publik dan seeder sudah memperlakukan berkas sebagai sesuatu yang
 * bisa belum ada — seeder bahkan menyiasatinya dengan menyimpan string kosong.
 * Jadikan kolomnya nullable supaya "berkas belum diunggah" bisa direpresentasikan
 * apa adanya, dan ubah string kosong yang terlanjur tersimpan menjadi NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galeris', function (Blueprint $table) {
            $table->string('foto')->nullable()->change();
        });

        Schema::table('warta_jemaats', function (Blueprint $table) {
            $table->string('file_warta')->nullable()->change();
        });

        DB::table('galeris')->where('foto', '')->update(['foto' => null]);
        DB::table('warta_jemaats')->where('file_warta', '')->update(['file_warta' => null]);
    }

    public function down(): void
    {
        DB::table('galeris')->whereNull('foto')->update(['foto' => '']);
        DB::table('warta_jemaats')->whereNull('file_warta')->update(['file_warta' => '']);

        Schema::table('galeris', function (Blueprint $table) {
            $table->string('foto')->nullable(false)->change();
        });

        Schema::table('warta_jemaats', function (Blueprint $table) {
            $table->string('file_warta')->nullable(false)->change();
        });
    }
};
