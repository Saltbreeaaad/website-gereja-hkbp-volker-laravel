<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_ibadahs', fn (Blueprint $t) => $t->index('tanggal'));
        Schema::table('warta_jemaats', fn (Blueprint $t) => $t->index('tanggal'));
        Schema::table('renungans', fn (Blueprint $t) => $t->index('tanggal'));
        Schema::table('galeris', fn (Blueprint $t) => $t->index('tanggal'));
        
        Schema::table('kas_gerejas', function (Blueprint $t) {
            $t->index('tanggal');
            $t->index('jenis');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_ibadahs', fn (Blueprint $t) => $t->dropIndex(['tanggal']));
        Schema::table('warta_jemaats', fn (Blueprint $t) => $t->dropIndex(['tanggal']));
        Schema::table('renungans', fn (Blueprint $t) => $t->dropIndex(['tanggal']));
        Schema::table('galeris', fn (Blueprint $t) => $t->dropIndex(['tanggal']));
        
        Schema::table('kas_gerejas', function (Blueprint $t) {
            $t->dropIndex(['tanggal']);
            $t->dropIndex(['jenis']);
        });
    }
};