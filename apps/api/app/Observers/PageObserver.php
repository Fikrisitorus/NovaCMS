<?php

namespace App\Observers;

use App\Models\Page;
use Illuminate\Support\Facades\Cache;

/**
 * Observer Page: invalidate cache endpoint publik setiap kali data
 * halaman berubah, agar frontend tidak menerima konten stale (cache
 * disimpan selama 15 menit di PageController).
 *
 * Cache store default (database/file) tidak mendukung cache tags, jadi
 * daftar key index yang pernah di-cache disimpan di satu kunci kecil
 * per website dan di-flush satu per satu.
 *
 * Cache key dibungkus website_id (isolasi multi-tenant), jadi flush
 * hanya mempengaruhi website pemilik halaman yang berubah.
 */
class PageObserver
{
    public function saved(Page $page): void
    {
        $this->forgetIndexPages($page->website_id);
        Cache::forget("api.pages.show.{$page->website_id}.{$page->slug}");
    }

    public function deleted(Page $page): void
    {
        $this->forgetIndexPages($page->website_id);
        Cache::forget("api.pages.show.{$page->website_id}.{$page->slug}");
    }

    /**
     * Hapus semua cache halaman index milik website tertentu.
     */
    private function forgetIndexPages(?string $websiteId): void
    {
        if ($websiteId === null) {
            return;
        }

        $keys = Cache::get("api.pages.index.keys.{$websiteId}", []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget("api.pages.index.keys.{$websiteId}");
    }
}
