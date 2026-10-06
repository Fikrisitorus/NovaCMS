<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebsiteResource;
use App\Models\Website;
use Illuminate\Http\Request;

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
     * yang sudah dipublikasi.
     */
    public function index(Request $request)
    {
        $apiKey = $request->attributes->get('apiKey');

        $website = Website::where('is_active', true)
            ->where('id', $apiKey->website_id)
            ->with(['pages' => function ($query) {
                $query->where('is_published', true);
            }])
            ->firstOrFail();

        return new WebsiteResource($website);
    }

    /**
     * Menampilkan detail website berdasarkan domain — tetap dibatasi
     * ke website pemilik kunci API; domain tenant lain mengembalikan 404.
     */
    public function showByDomain(Request $request, string $domain)
    {
        $apiKey = $request->attributes->get('apiKey');

        $website = Website::where('domain', $domain)
            ->where('is_active', true)
            ->where('id', $apiKey->website_id)
            ->with(['pages' => function ($query) {
                $query->where('is_published', true);
            }])
            ->firstOrFail();

        return new WebsiteResource($website);
    }
}
