<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Category;

/**
 * Controller untuk endpoint publik Category.
 * Menyediakan akses baca (read-only) daftar kategori dan post
 * yang termasuk dalam sebuah kategori.
 */
class CategoryController extends Controller
{
    /**
     * Menampilkan daftar semua kategori.
     */
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return response()->json([
            'data' => $categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'posts_count' => $category->posts()->published()->count(),
            ]),
        ]);
    }

    /**
     * Menampilkan post terbit yang termasuk dalam kategori berdasarkan slug.
     * Mendukung pagination melalui query parameter ?page=N.
     */
    public function posts(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = $category->posts()
            ->published()
            ->with(['author', 'categories', 'seoMeta'])
            ->orderBy('published_at', 'desc')
            ->paginate(15);

        return PostResource::collection($posts);
    }
}
