<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

/**
 * Observer Post: invalidate cache endpoint publik setiap kali data post
 * berubah, agar frontend tidak menerima konten stale (cache disimpan
 * selama 15 menit di PostController).
 *
 * Cache store default (database/file) tidak mendukung cache tags, jadi
 * daftar halaman yang pernah di-cache disimpan di satu kunci kecil dan
 * di-flush satu per satu.
 *
 * Cache key dibungkus website_id (isolasi multi-tenant), jadi flush
 * hanya mempengaruhi website pemilik post yang berubah.
 */
class PostObserver
{
    public function saved(Post $post): void
    {
        $this->forgetIndexPages($post->website_id);
        Cache::forget("api.posts.show.{$post->website_id}.{$post->slug}");
    }

    public function deleted(Post $post): void
    {
        $this->forgetIndexPages($post->website_id);
        Cache::forget("api.posts.show.{$post->website_id}.{$post->slug}");
    }

    /**
     * Hapus semua cache halaman index milik website tertentu.
     */
    private function forgetIndexPages(?string $websiteId): void
    {
        if ($websiteId === null) {
            return;
        }

        $pages = Cache::get("api.posts.index.pages.{$websiteId}", []);
        foreach ($pages as $key) {
            Cache::forget("api.posts.index.{$websiteId}.{$key}");
        }
        Cache::forget("api.posts.index.pages.{$websiteId}");
    }
}
