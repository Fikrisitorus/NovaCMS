<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;

/**
 * Controller untuk endpoint publik Media.
 *
 * Menyediakan akses baca (read-only) ke media library. Hanya field
 * yang aman untuk konsumsi frontend yang dikembalikan — path internal
 * dan konfigurasi disk sengaja tidak di-expose.
 */
class MediaController extends Controller
{
    /**
     * Menampilkan daftar media. Mendukung pagination (?page=N).
     */
    public function index(Request $request)
    {
        $media = Media::search($request->input('q'))
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
