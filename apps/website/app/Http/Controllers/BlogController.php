<?php

namespace App\Http\Controllers;

use App\Services\NovaApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(private readonly NovaApiClient $api) {}

    /**
     * Daftar post dengan pagination dari API.
     */
    public function index(Request $request): View
    {
        $page = max(1, (int) $request->input('page', 1));

        $payload = $this->api->getPaginated('/posts', ['page' => $page]);

        return view('blog.index', [
            'posts' => $payload['items'],
            'meta' => $payload['meta'],
        ]);
    }

    /**
     * Detail post berdasarkan slug, menyertakan SEO meta dari API.
     * Mengembalikan 404 bila post tidak ditemukan.
     */
    public function show(string $slug): View
    {
        $post = $this->api->getPost($slug);

        abort_if($post === null, 404, 'Post tidak ditemukan.');

        $seo = $post['seo_meta'] ?? [];

        return view('blog.show', [
            'post' => $post,
            'seoTitle' => $seo['meta_title'] ?? $post['title'],
            'seoDescription' => $seo['meta_description'] ?? ($post['excerpt'] ?? ''),
            'seoImage' => $seo['og_image'] ?? ($post['featured_image'] ?? null),
            'canonicalUrl' => $seo['canonical_url'] ?? null,
            'keywords' => $seo['meta_keywords'] ?? null,
        ]);
    }
}
