<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\PostResource;
use App\Models\Post;


/**
 * Controller untuk endpoint publik Post (Blog).
 * Hanya menampilkan post yang sudah dipublikasikan (published_at tidak null).
 */
class PostController extends Controller
{
    /**
     * Menampilkan daftar post yang sudah dipublikasikan,
     * diurutkan berdasarkan tanggal publikasi terbaru.
     */
    public function index()
    {
        $posts = Post::whereNotNull('published_at')
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
            ->whereNotNull('published_at')
            ->firstOrFail();

        return new PostResource($post);
    }
}
