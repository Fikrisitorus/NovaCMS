<?php

namespace App\Http\Controllers;

use App\Services\NovaApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly NovaApiClient $api) {}

    /**
     * Daftar seluruh kategori beserta jumlah post-nya.
     */
    public function index(): View
    {
        $categories = $this->api->getCategories();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Daftar post di dalam sebuah kategori, terpaginasi.
     */
    public function show(Request $request, string $slug): View
    {
        $page = max(1, (int) $request->input('page', 1));

        $payload = $this->api->getCategoryPosts($slug, $page);

        // Ambil nama kategori dari item pertama bila tersedia.
        $name = collect($payload['items'])
            ->flatMap(fn ($post) => $post['categories'] ?? [])
            ->firstWhere('slug', $slug)['name'] ?? null;

        return view('categories.show', [
            'categorySlug' => $slug,
            'categoryName' => $name ?? ucfirst(str_replace('-', ' ', $slug)),
            'posts' => $payload['items'],
            'meta' => $payload['meta'],
        ]);
    }
}
