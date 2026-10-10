<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Http\Resources\WebsiteResource;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller untuk endpoint publik Website.
 *
 * Isolasi multi-tenant: endpoint hanya mengembalikan website pemilik
 * kunci API yang dipakai, tidak pernah daftar semua tenant.
 */
class WebsiteController extends Controller
{
    /**
     * Menampilkan website pemilik kunci API beserta halaman
     * yang sudah dipublikasikan (dipaginasi 15 per halaman).
     */
    public function index(Request $request)
    {
        $apiKey = $request->attributes->get('apiKey');
        $page = (int) $request->input('page', 1);

        $website = Cache::remember(
            "api.websites.index.{$apiKey->website_id}",
            now()->addMinutes(15),
            fn () => Website::where('is_active', true)
                ->where('id', $apiKey->website_id)
                ->firstOrFail()
        );

        // Halaman dipaginasi terpisah dari cache website induk; nomor
        // halaman disertakan di cache key agar tiap halaman tersendiri.
        $pages = Cache::remember(
            "api.websites.pages.{$apiKey->website_id}.{$page}",
            now()->addMinutes(15),
            fn () => $website->pages()
                ->where('is_published', true)
                ->latest()
                ->paginate(15)
        );

        // Catat key halaman yang pernah di-cache agar WebsiteObserver
        // bisa menghapusnya saat data berubah (cache store default tidak
        // mendukung cache tags).
        $keys = Cache::get("api.websites.pages.keys.{$apiKey->website_id}", []);
        $pageKey = "api.websites.pages.{$apiKey->website_id}.{$page}";
        if (! in_array($pageKey, $keys, true)) {
            Cache::forever("api.websites.pages.keys.{$apiKey->website_id}", [...$keys, $pageKey]);
        }

        return PageResource::collection($pages);
    }

    /**
     * Menampilkan detail website berdasarkan domain — tetap dibatasi
     * ke website pemilik kunci API; domain tenant lain mengembalikan 404.
     */
    public function showByDomain(Request $request, string $domain)
    {
        $apiKey = $request->attributes->get('apiKey');

        $website = Cache::remember(
            "api.websites.show.{$apiKey->website_id}.{$domain}",
            now()->addMinutes(15),
            fn () => Website::where('domain', $domain)
                ->where('is_active', true)
                ->where('id', $apiKey->website_id)
                ->with(['pages' => function ($query) {
                    $query->where('is_published', true);
                }])
                ->firstOrFail()
        );

        return new WebsiteResource($website);
    }
}
