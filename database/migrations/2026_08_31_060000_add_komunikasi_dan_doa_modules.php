<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumuman_pentings', function (Blueprint $table): void {
            $table->id();
            $table->string('judul');
            $table->text('isi');
            $table->string('tautan')->nullable();
            $table->string('label_tautan', 80)->nullable();
            $table->dateTime('mulai_tayang')->nullable();
            $table->dateTime('selesai_tayang')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->index(['aktif', 'mulai_tayang', 'selesai_tayang']);
        });

        Schema::create('permohonan_doas', function (Blueprint $table): void {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('kontak', 50)->nullable();
            $table->text('isi');
            $table->string('status', 30)->default('baru');
            $table->text('catatan_pengurus')->nullable();
            $table->timestamp('ditindaklanjuti_pada')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_doas');
        Schema::dropIfExists('pengumuman_pentings');
    }
};
