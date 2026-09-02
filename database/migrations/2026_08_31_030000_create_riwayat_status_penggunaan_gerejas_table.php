<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_status_penggunaan_gerejas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penggunaan_gereja_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status_lama')->nullable();
            $table->string('status_baru');
            $table->string('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['penggunaan_gereja_id', 'created_at'], 'riwayat_penggunaan_waktu_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_penggunaan_gerejas');
    }
};
