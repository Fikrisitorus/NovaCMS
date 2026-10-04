<?php

use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\WebsiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - NovaCMS Headless CMS
|--------------------------------------------------------------------------
|
| Endpoint publik (read-only) untuk dikonsumsi oleh frontend website.
| Semua route di bawah ini di-prefix dengan /api secara otomatis.
|
| Contoh akses:
|   GET /api/websites
|   GET /api/websites/{domain}
|   GET /api/pages?website_id=xxx
|   GET /api/pages/{slug}
|   GET /api/posts
|   GET /api/posts/{slug}
|
*/

Route::prefix('v1')->group(function () {
    // Website endpoints
    Route::get('/websites', [WebsiteController::class, 'index']);
    Route::get('/websites/{domain}', [WebsiteController::class, 'showByDomain']);

    // Page endpoints
    Route::get('/pages', [PageController::class, 'index']);
    Route::get('/pages/{slug}', [PageController::class, 'showBySlug']);

    // Post (Blog) endpoints
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{slug}', [PostController::class, 'showBySlug']);
});
