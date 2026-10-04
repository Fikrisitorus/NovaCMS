<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;

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
     */
    public function index()
    {
        $posts = Post::where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->get();

        return PostResource::collection($posts);
    }

    /**
     * Menampilkan detail post berdasarkan slug.
     */
    public function showBySlug(string $slug)
    {
        $post = Post::where('slug', $slug)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->firstOrFail();

        return new PostResource($post);
    }
}
