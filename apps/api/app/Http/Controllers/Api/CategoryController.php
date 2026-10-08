<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller untuk endpoint publik Category.
 * Menyediakan akses baca (read-only) daftar kategori dan post
 * yang termasuk dalam sebuah kategori.
 *
 * Isolasi multi-tenant: tabel categories sendiri tidak memiliki
 * website_id, jadi isolasi dilakukan lewat relasi posts — kategori
 * hanya muncul bila website pemilik kunci API memiliki post di
 * kategori tersebut.
 */
class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori yang memiliki minimal satu post
     * terbit milik website pemilik kunci API.
     */
    public function index(Request $request)
    {
        $websiteId = $request->attributes->get('apiKey')->website_id;

        $categories = Cache::remember(
            "api.categories.index.{$websiteId}",
            now()->addMinutes(15),
            fn () => Category::whereHas('posts', function ($query) use ($websiteId) {
                $query->published()
                    ->where('website_id', $websiteId);
            })
                ->withCount(['posts' => function ($query) use ($websiteId) {
                    $query->published()
                        ->where('website_id', $websiteId);
                }])
                ->orderBy('name')
                ->get()
        );

        return response()->json([
            'data' => $categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'posts_count' => $category->posts_count,
            ]),
        ]);
    }

    /**
     * Menampilkan post terbit yang termasuk dalam kategori berdasarkan
     * slug, hanya dari website pemilik kunci API.
     * Mendukung pagination (?page=N) dan pencarian (?q=).
     */
    public function posts(Request $request, string $slug)
    {
        $websiteId = $request->attributes->get('apiKey')->website_id;

        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = $category->posts()
            ->published()
            ->where('website_id', $websiteId)
            ->search($request->input('q'))
            ->with(['author', 'categories', 'seoMeta'])
            ->orderBy('published_at', 'desc')
            ->paginate(15);

        return PostResource::collection($posts);
    }
}
