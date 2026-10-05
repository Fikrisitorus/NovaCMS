<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MediaController;
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
|   GET /api/v1/websites
|   GET /api/v1/websites/{domain}
|   GET /api/v1/pages?website_id=xxx
|   GET /api/v1/pages/{slug}
|   GET /api/v1/posts
|   GET /api/v1/posts/{slug}
|   GET /api/v1/categories
|   GET /api/v1/categories/{slug}/posts
|   GET /api/v1/media
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

    // Category endpoints
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}/posts', [CategoryController::class, 'posts']);

    // Media endpoints
    Route::get('/media', [MediaController::class, 'index']);
});
