<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('parhalados', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('nama'); // Menambahkan kolom foto
        });
    }
    public function down(): void {
        Schema::table('parhalados', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};