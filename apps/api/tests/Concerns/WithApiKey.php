<?php

namespace Tests\Concerns;

use App\Models\ApiKey;
use App\Models\Website;

/**
 * Trait untuk test yang memanggil endpoint publik /api/v1/*.
 *
 * Semua endpoint publik kini dilindungi middleware api.key. Trait ini
 * membuat satu kunci API aktif untuk website pertama dan menyuntikkannya
 * sebagai header Authorization default sehingga test tinggal memanggil
 * getJson('/api/v1/...') seperti biasa.
 */
trait WithApiKey
{
    protected ApiKey $apiKey;

    /**
     * Buat kunci API aktif dan daftarkan sebagai header default.
     *
     * Panggil dari setUp() test class yang memakai endpoint publik.
     */
    protected function useApiKey(): void
    {
        /** @var Website|null $website */
        $website = Website::first();

        $this->apiKey = ApiKey::factory()->create([
            'website_id' => $website?->id ?? Website::factory()->create()->id,
        ]);

        $this->withHeader('Authorization', "Bearer {$this->apiKey->key}");
    }
}
