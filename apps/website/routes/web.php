<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Frontend Publik NovaCMS
|--------------------------------------------------------------------------
|
| Frontend website adalah consumer dari API publik apps/api. Semua data
| diambil melalui NovaApiClient (config NOVACMS_API_URL).
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Kategori
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

// Kontak
Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Halaman dinamis (page detail via API, route catch-all paling bawah).
Route::get('/{slug}', [HomeController::class, 'showPage'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('pages.show');
