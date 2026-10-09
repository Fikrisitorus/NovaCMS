<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller untuk endpoint publik Page.
 * Menyediakan akses baca halaman beserta blocks-nya untuk frontend.
 *
 * Isolasi multi-tenant: query dibatasi ke website pemilik kunci API.
 * Response di-cache 15 menit (sama seperti PostController) dan
 * di-invalidate oleh PageObserver setiap kali halaman berubah.
 */
class PageController extends Controller
{
    /**
     * Menampilkan daftar halaman yang sudah dipublikasi milik website
     * pemilik kunci API.
     */
    public function index(Request $request)
    {
        $websiteId = $request->attributes->get('apiKey')->website_id;
        $page = (int) $request->input('page', 1);
        $q = $request->input('q');

        $cacheKey = blank($q)
            ? "api.pages.index.{$websiteId}.{$page}"
            : "api.pages.index.{$websiteId}.{$page}.{$q}";

        $pages = Cache::remember(
            $cacheKey,
            now()->addMinutes(15),
            fn () => Page::where('is_published', true)
                ->where('website_id', $websiteId)
                ->search($q)
                ->with('website')
                ->latest()
                ->paginate(15)
        );

        // Catat key index yang pernah di-cache agar PageObserver bisa
        // menghapusnya saat ada perubahan data (cache store default tidak
        // mendukung cache tags).
        $keys = Cache::get("api.pages.index.keys.{$websiteId}", []);
        if (! in_array($cacheKey, $keys, true)) {
            Cache::forever("api.pages.index.keys.{$websiteId}", [...$keys, $cacheKey]);
        }

        return PageResource::collection($pages);
    }

    /**
     * Menampilkan detail halaman berdasarkan slug.
     *
     * Relasi 'sections' dan tabel page_sections sudah dihapus oleh migrasi
     * 2026_09_10_094631_modify_pages_and_drop_page_sections; konten halaman
     * kini disimpan pada kolom JSON 'blocks'.
     */
    public function showBySlug(Request $request, string $slug)
    {
        $websiteId = $request->attributes->get('apiKey')->website_id;

        $page = Cache::remember(
            "api.pages.show.{$websiteId}.{$slug}",
            now()->addMinutes(15),
            fn () => Page::where('slug', $slug)
                ->where('website_id', $websiteId)
                ->where('is_published', true)
                ->with('website')
                ->firstOrFail()
        );

        return new PageResource($page);
    }
}
