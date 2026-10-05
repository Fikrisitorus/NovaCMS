<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

/**
 * Controller untuk endpoint publik Post (Blog).
 * Hanya menampilkan post yang sudah dipublikasikan: is_published true dan
 * published_at sudah lewat (mendukung penjadwalan posting).
 */
class PostController extends Controller
{
    /**
     * Menampilkan daftar post yang sudah dipublikasikan,
     * diurutkan berdasarkan tanggal publikasi terbaru.
     * Mendukung pagination melalui query parameter ?page=N.
     */
    public function index()
    {
        $page = (int) request()->input('page', 1);

        $posts = Cache::remember(
            "api.posts.index.{$page}",
            now()->addMinutes(15),
            fn () => Post::where('is_published', true)
                ->where('published_at', '<=', now())
                ->with(['author', 'categories', 'seoMeta'])
                ->orderBy('published_at', 'desc')
                ->paginate(15)
        );

        // Catat halaman yang pernah di-cache agar PostObserver bisa
        // menghapusnya saat ada perubahan data (cache store default tidak
        // mendukung cache tags).
        $pages = Cache::get('api.posts.index.pages', []);
        if (! in_array($page, $pages, true)) {
            Cache::forever('api.posts.index.pages', [...$pages, $page]);
        }

        return PostResource::collection($posts);
    }

    /**
     * Menampilkan detail post berdasarkan slug.
     */
    public function showBySlug(string $slug)
    {
        $post = Cache::remember(
            "api.posts.show.{$slug}",
            now()->addMinutes(15),
            fn () => Post::where('slug', $slug)
                ->where('is_published', true)
                ->where('published_at', '<=', now())
                ->with(['author', 'categories', 'seoMeta'])
                ->firstOrFail()
        );

        return new PostResource($post);
    }
}
