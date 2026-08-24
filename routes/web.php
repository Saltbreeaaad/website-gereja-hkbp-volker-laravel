<?php

use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/pelayan', [PublicController::class, 'pelayan'])->name('pelayan');
Route::get('/profil', [PublicController::class, 'profil'])->name('profil');
Route::get('/renungan', [PublicController::class, 'renungan'])->name('renungan');
Route::get('/galeri', [PublicController::class, 'galeri'])->name('galeri');