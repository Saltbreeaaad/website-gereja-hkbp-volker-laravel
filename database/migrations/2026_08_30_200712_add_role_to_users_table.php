<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Peran pengurus.
 *
 * Sebelumnya siapa pun yang bisa masuk panel memegang kuasa penuh: bendahara
 * bisa menghapus seluruh arsip warta, sekretaris bisa mengubah catatan kas.
 * Untuk data keuangan gereja pemisahan ini bukan kemewahan.
 *
 * Baris lama dijadikan `admin` supaya tidak ada pengurus yang tiba-tiba
 * kehilangan akses yang selama ini ia pakai.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default(User::ADMIN)->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
    }
};
