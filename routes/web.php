<?php

use App\Http\Controllers\PublicController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/profil', [PublicController::class, 'profil'])->name('profil');
Route::get('/pelayan', [PublicController::class, 'pelayan'])->name('pelayan');
Route::get('/renungan', [PublicController::class, 'renungan'])->name('renungan');
Route::get('/warta', [PublicController::class, 'warta'])->name('warta');
Route::get('/galeri', [PublicController::class, 'galeri'])->name('galeri');

Route::get('/penggunaan-gereja', [PublicController::class, 'penggunaanGereja'])->name('penggunaan-gereja');
Route::post('/penggunaan-gereja', [PublicController::class, 'storePenggunaanGereja'])
    ->middleware('throttle:6,1')
    ->name('penggunaan-gereja.store');

// Dibatasi lajunya: kode penelusuran memang sulit ditebak (31^8 kemungkinan),
// tetapi tanpa batas ini halaman tersebut menjadi alat penebakan beruntun.
Route::get('/penggunaan-gereja/lacak', [PublicController::class, 'lacakPenggunaanGereja'])
    ->middleware('throttle:20,1')
    ->name('penggunaan-gereja.lacak');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

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
