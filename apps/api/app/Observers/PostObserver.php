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
 */
class PostObserver
{
    public function saved(Post $post): void
    {
        $this->forgetIndexPages();
        Cache::forget('api.posts.show.'.$post->slug);
    }

    public function deleted(Post $post): void
    {
        $this->forgetIndexPages();
        Cache::forget('api.posts.show.'.$post->slug);
    }

    private function forgetIndexPages(): void
    {
        $pages = Cache::get('api.posts.index.pages', []);
        foreach ($pages as $key) {
            Cache::forget("api.posts.index.{$key}");
        }
        Cache::forget('api.posts.index.pages');
    }
}
