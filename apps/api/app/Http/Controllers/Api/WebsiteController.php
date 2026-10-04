<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebsiteResource;
use App\Models\Website;

/**
 * Controller untuk endpoint publik Website.
 * Hanya menyediakan akses baca (read-only) untuk konsumsi frontend.
 */
class WebsiteController extends Controller
{
    /**
     * Menampilkan daftar semua website yang aktif.
     */
    public function index()
    {
        $websites = Website::where('is_active', true)
            ->latest()
            ->get();

        return WebsiteResource::collection($websites);
    }

    /**
     * Menampilkan detail website berdasarkan domain,
     * beserta daftar halaman yang sudah dipublikasi.
     */
    public function showByDomain(string $domain)
    {
        $website = Website::where('domain', $domain)
            ->where('is_active', true)
            ->with(['pages' => function ($query) {
                $query->where('is_published', true);
            }])
            ->firstOrFail();

        return new WebsiteResource($website);
    }
}
