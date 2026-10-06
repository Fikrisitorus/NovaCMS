<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

/**
 * Controller untuk endpoint publik Page.
 * Menyediakan akses baca halaman beserta blocks-nya untuk frontend.
 *
 * Isolasi multi-tenant: query dibatasi ke website pemilik kunci API.
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

        $pages = Page::where('is_published', true)
            ->where('website_id', $websiteId)
            ->search($request->input('q'))
            ->with('website')
            ->latest()
            ->get();

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

        $page = Page::where('slug', $slug)
            ->where('website_id', $websiteId)
            ->where('is_published', true)
            ->with('website')
            ->firstOrFail();

        return new PageResource($page);
    }
}
