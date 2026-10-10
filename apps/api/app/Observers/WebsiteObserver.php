<?php

namespace App\Observers;

use App\Models\Website;
use Illuminate\Support\Facades\Cache;

/**
 * Observer Website: invalidate cache endpoint publik /websites setiap
 * kali data website berubah, agar frontend tidak menerima konten stale
 * (cache disimpan selama 15 menit di WebsiteController).
 *
 * Cache store default (database/file) tidak mendukung cache tags, jadi
 * daftar key halaman yang pernah di-cache disimpan di satu kunci kecil
 * per website dan di-flush satu per satu.
 */
class WebsiteObserver
{
    public function saved(Website $website): void
    {
        Cache::forget("api.websites.index.{$website->id}");
        $this->forgetIndexPages($website->id);
    }

    public function deleted(Website $website): void
    {
        Cache::forget("api.websites.index.{$website->id}");
        $this->forgetIndexPages($website->id);
    }

    /**
     * Hapus semua cache halaman index milik website tertentu.
     */
    private function forgetIndexPages(string $websiteId): void
    {
        $keys = Cache::get("api.websites.pages.keys.{$websiteId}", []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget("api.websites.pages.keys.{$websiteId}");
    }
}
