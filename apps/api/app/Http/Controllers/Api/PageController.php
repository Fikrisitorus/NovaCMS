<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

/**
 * Controller untuk endpoint publik Page.
 * Menyediakan akses baca halaman beserta section-nya untuk frontend.
 */
class PageController extends Controller
{
    /**
     * Menampilkan daftar halaman yang sudah dipublikasi.
     * Bisa difilter berdasarkan website_id melalui query parameter.
     */
    public function index(Request $request)
    {
        $query = Page::where('is_published', true)
            ->with('website');

        // Filter berdasarkan website_id jika diberikan
        if ($request->has('website_id')) {
            $query->where('website_id', $request->input('website_id'));
        }

        $pages = $query->latest()->get();

        return PageResource::collection($pages);
    }

    /**
     * Menampilkan detail halaman berdasarkan slug.
     *
     * Relasi 'sections' dan tabel page_sections sudah dihapus oleh migrasi
     * 2026_09_10_094631_modify_pages_and_drop_page_sections; konten halaman
     * kini disimpan pada kolom JSON 'blocks'.
     */
    public function showBySlug(string $slug)
    {
        $page = Page::where('slug', $slug)
            ->where('is_published', true)
            ->with('website')
            ->firstOrFail();

        return new PageResource($page);
    }
}
