<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;

/**
 * Controller untuk endpoint publik Media.
 *
 * Menyediakan akses baca (read-only) ke media library milik website
 * pemilik kunci API. Hanya field yang aman untuk konsumsi frontend yang
 * dikembalikan — path internal dan konfigurasi disk sengaja tidak
 * di-expose.
 */
class MediaController extends Controller
{
    /**
     * Menampilkan daftar media milik website pemilik kunci API.
     * Mendukung pagination (?page=N) dan pencarian (?q=).
     */
    public function index(Request $request)
    {
        $websiteId = $request->attributes->get('apiKey')->website_id;

        $media = Media::where('website_id', $websiteId)
            ->search($request->input('q'))
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        return response()->json([
            'data' => $media->getCollection()->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'file_name' => $item->file_name,
                'mime_type' => $item->mime_type,
                'size' => $item->size,
                'url' => $item->url,
            ]),
            'meta' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
            ],
        ]);
    }
}
