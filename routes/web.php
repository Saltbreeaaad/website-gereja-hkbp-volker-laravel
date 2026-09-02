<?php

use App\Http\Controllers\LaporanKasController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TwoFactorChallengeController;
use App\Http\Middleware\PastikanTwoFactorTerverifikasi;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/profil', [PublicController::class, 'profil'])->name('profil');
Route::view('/offline', 'offline')->name('offline');
Route::get('/pelayan', [PublicController::class, 'pelayan'])->name('pelayan');
Route::get('/renungan', [PublicController::class, 'renungan'])->name('renungan');
Route::get('/renungan/arsip', [PublicController::class, 'arsipRenungan'])->name('renungan.arsip');
Route::get('/warta', [PublicController::class, 'warta'])->name('warta');
Route::get('/galeri', [PublicController::class, 'galeri'])->name('galeri');
Route::get('/agenda', [PublicController::class, 'agenda'])->name('agenda');
Route::get('/agenda/kalender.ics', [PublicController::class, 'kalenderAgenda'])->name('agenda.kalender');
Route::get('/doa', [PublicController::class, 'doa'])->name('doa');
Route::post('/doa', [PublicController::class, 'storeDoa'])
    ->middleware('throttle:5,1')
    ->name('doa.store');

Route::get('/penggunaan-gereja', [PublicController::class, 'penggunaanGereja'])->name('penggunaan-gereja');
Route::get('/penggunaan-gereja/kalender.ics', [PublicController::class, 'kalenderPenggunaanGereja'])
    ->name('penggunaan-gereja.kalender');
Route::post('/penggunaan-gereja', [PublicController::class, 'storePenggunaanGereja'])
    ->middleware('throttle:6,1')
    ->name('penggunaan-gereja.store');

// Dibatasi lajunya: kode penelusuran memang sulit ditebak (31^8 kemungkinan),
// tetapi tanpa batas ini halaman tersebut menjadi alat penebakan beruntun.
Route::get('/penggunaan-gereja/lacak', [PublicController::class, 'lacakPenggunaanGereja'])
    ->middleware('throttle:20,1')
    ->name('penggunaan-gereja.lacak');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Laporan kas hidup di luar panel Filament (butuh tata letak cetak sendiri),
// jadi daftar middleware panel tidak ikut berlaku dan harus disebut ulang di
// sini. Tanpa `auth` pengunjung anonim hanya kena 403 dari Gate alih-alih
// diantar ke halaman masuk; tanpa PastikanTwoFactorTerverifikasi seluruh
// riwayat keuangan gereja terbuka bagi sesi yang belum melewati 2FA — persis
// yang dicegah panel di semua halaman lain.
Route::prefix('admin/laporan-kas')
    ->middleware(['auth', PastikanTwoFactorTerverifikasi::class])
    ->group(function () {
        Route::get('/', [LaporanKasController::class, 'tampil'])->name('admin.kas.laporan');
        Route::get('/csv', [LaporanKasController::class, 'csv'])->name('admin.kas.csv');
    });

// Halaman tantangan sendiri tentu tidak boleh memakai PastikanTwoFactorTerverifikasi
// (itu akan mengalihkan ke dirinya sendiri tanpa henti), tetapi tetap butuh `auth`.
Route::middleware('auth')->group(function () {
    Route::get('/admin/two-factor-challenge', [TwoFactorChallengeController::class, 'tampil'])
        ->name('two-factor.challenge');
    Route::post('/admin/two-factor-challenge', [TwoFactorChallengeController::class, 'verifikasi'])
        ->middleware('throttle:6,1')
        ->name('two-factor.verify');
});

// robots.txt disajikan lewat route agar URL sitemap selalu absolut mengikuti APP_URL.
Route::get('/robots.txt', function () {
    $isi = implode("\n", [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin',
        '',
        'Sitemap: '.route('sitemap'),
        '',
    ]);

    return response($isi, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');
