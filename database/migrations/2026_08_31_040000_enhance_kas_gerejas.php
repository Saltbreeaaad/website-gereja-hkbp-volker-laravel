<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kas_gerejas', function (Blueprint $table) {
            $table->string('bukti')->nullable()->after('nominal');
        });

        Schema::create('periode_kas', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7)->unique();
            $table->bigInteger('saldo_awal')->default(0);
            $table->timestamp('ditutup_at')->nullable();
            $table->foreignId('ditutup_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_kas');

        Schema::table('kas_gerejas', function (Blueprint $table) {
            $table->dropColumn('bukti');
        });
    }
};
