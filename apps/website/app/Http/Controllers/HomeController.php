<?php

namespace App\Http\Controllers;

use App\Services\NovaApiClient;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly NovaApiClient $api) {}

    /**
     * Homepage: memuat halaman "home" (render blocks bila ada) dan
     * daftar post terbaru untuk preview blog.
     */
    public function index(): View
    {
        $page = $this->api->getHomePage();
        $posts = $this->api->getRecentPosts(6);

        return view('home', [
            'page' => $page,
            'blocks' => $page['blocks'] ?? [],
            'posts' => $posts,
        ]);
    }

    /**
     * Render halaman dinamis berdasarkan slug (route /{slug}).
     * Dipakai oleh route catch-all di web.php.
     */
    public function showPage(string $slug): View
    {
        $page = $this->api->getPage($slug);

        abort_if($page === null, 404, 'Halaman tidak ditemukan.');

        $seo = $page['seo_meta'] ?? [];

        return view('pages.show', [
            'page' => $page,
            'blocks' => $page['blocks'] ?? [],
            'seoTitle' => $seo['meta_title'] ?? $page['title'],
            'seoDescription' => $seo['meta_description'] ?? null,
            'seoImage' => $seo['og_image'] ?? null,
            'canonicalUrl' => $seo['canonical_url'] ?? null,
            'keywords' => $seo['meta_keywords'] ?? null,
        ]);
    }
}
