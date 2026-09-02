<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tugas terjadwal
|--------------------------------------------------------------------------
|
| Butuh SATU entri cron di server, jika tidak semua ini tidak pernah jalan:
|
|     * * * * * cd /path/ke/hkbp-volker && php artisan schedule:run >> /dev/null 2>&1
|
*/

// Tiap jam, bukan sekali pada dini hari. `--sekali-sehari` membuat perintahnya
// langsung keluar bila hari itu sudah punya cadangan, jadi hasil akhirnya tetap
// satu cadangan per hari — tetapi mesin yang tidak menyala 24 jam (laptop
// pengembangan, atau server yang sempat mati semalam) tidak lagi kehilangan
// cadangan hari itu. Penjadwal Laravel tidak punya mekanisme menyusul jadwal
// yang terlewat, jadi ini yang menggantikannya.
Schedule::command('hkbp:cadangkan --sekali-sehari')
    ->hourlyAt(15)
    // Kalau dump sebelumnya masih berjalan (basis data membesar, server sibuk),
    // jangan menumpuk proses mysqldump di atasnya.
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/cadangan.log'));

Schedule::command('hkbp:kedaluwarsakan-permohonan')
    ->dailyAt('03:30')
    ->withoutOverlapping();

Schedule::command('hkbp:periksa-cadangan')
    ->dailyAt('09:00')
    ->withoutOverlapping();

/*
| Pekerja antrean.
|
| Notifikasi pengurus diantre supaya pengunjung yang mengirim formulir tidak
| ikut menunggu penulisannya. Antrean tanpa pekerja berarti notifikasi itu
| tidak pernah sampai sama sekali — jadi keduanya harus dipasang bersama.
|
| `--stop-when-empty` membuat prosesnya keluar begitu antrean habis, bukan
| menetap sebagai daemon. Itu yang cocok di sini: hosting bersama umumnya tidak
| menyediakan supervisor, dan satu-satunya hal yang dijamin berjalan adalah
| entri cron yang sama dengan penjadwal ini. `--max-time` menjaga agar satu
| pekerjaan yang macet tidak menahan slot berikutnya.
*/
Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=3')
    ->everyMinute()
    ->withoutOverlapping();

/*
| Cache isi halaman publik membatalkan dirinya sendiri setiap kali pengurus
| menyunting data, jadi tidak ada jadwal pembersihan cache di sini. Yang tidak
| terdeteksi hanyalah perubahan lewat SQL langsung — untuk itu jalankan
| `php artisan cache:clear` secara manual.
*/
