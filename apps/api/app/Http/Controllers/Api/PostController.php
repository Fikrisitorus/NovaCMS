<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller untuk endpoint publik Post (Blog).
 * Hanya menampilkan post yang sudah dipublikasikan: is_published true dan
 * published_at sudah lewat (mendukung penjadwalan posting).
 *
 * Isolasi multi-tenant: semua query dibatasi ke website pemilik kunci
 * API yang dipakai pada request ini (lihat EnsureApiKeyIsValid).
 */
class PostController extends Controller
{
    /**
     * Menampilkan daftar post yang sudah dipublikasikan,
     * diurutkan berdasarkan tanggal publikasi terbaru.
     * Mendukung pagination (?page=N) dan pencarian (?q=).
     */
    public function index(Request $request)
    {
        $websiteId = $request->attributes->get('apiKey')->website_id;
        $page = (int) $request->input('page', 1);
        $q = $request->input('q');

        $cacheKey = blank($q)
            ? "api.posts.index.{$websiteId}.{$page}"
            : "api.posts.index.{$websiteId}.{$page}.{$q}";

        $posts = Cache::remember(
            $cacheKey,
            now()->addMinutes(15),
            fn () => Post::published()
                ->where('website_id', $websiteId)
                ->search($q)
                ->with(['author', 'categories', 'seoMeta'])
                ->orderBy('published_at', 'desc')
                ->paginate(15)
        );

        // Catat halaman yang pernah di-cache agar PostObserver bisa
        // menghapusnya saat ada perubahan data (cache store default tidak
        // mendukung cache tags).
        $pages = Cache::get("api.posts.index.pages.{$websiteId}", []);
        $key = blank($q) ? (string) $page : "{$page}.{$q}";
        if (! in_array($key, $pages, true)) {
            Cache::forever("api.posts.index.pages.{$websiteId}", [...$pages, $key]);
        }

        return PostResource::collection($posts);
    }

    /**
     * Menampilkan detail post berdasarkan slug.
     */
    public function showBySlug(Request $request, string $slug)
    {
        $websiteId = $request->attributes->get('apiKey')->website_id;

        $post = Cache::remember(
            "api.posts.show.{$websiteId}.{$slug}",
            now()->addMinutes(15),
            fn () => Post::where('slug', $slug)
                ->where('website_id', $websiteId)
                ->where('is_published', true)
                ->where('published_at', '<=', now())
                ->with(['author', 'categories', 'seoMeta'])
                ->firstOrFail()
        );

        return new PostResource($post);
    }
}
