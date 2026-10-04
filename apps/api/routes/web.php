<?php

use App\Models\Post;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - NovaCMS Public Frontend
|--------------------------------------------------------------------------
|
| Route untuk halaman publik yang bisa diakses oleh pengunjung website.
| Admin panel Filament sudah memiliki route tersendiri di /admin.
|
*/

// Homepage
Route::get('/', function () {
    return view('home');
})->name('home');

// Blog listing - menampilkan post yang sudah dipublikasikan
Route::get('/blog', function () {
    $posts = Post::where('is_published', true)
        ->where('published_at', '<=', now())
        ->orderBy('published_at', 'desc')
        ->get();

    return view('blog.index', compact('posts'));
})->name('blog.index');

// Blog detail - menampilkan post berdasarkan slug
Route::get('/blog/{slug}', function (string $slug) {
    $post = Post::where('slug', $slug)
        ->where('is_published', true)
        ->where('published_at', '<=', now())
        ->firstOrFail();

    return view('blog.show', compact('post'));
})->name('blog.show');
