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

    protected Website $website;

    /**
     * Buat kunci API aktif dan daftarkan sebagai header default.
     *
     * Panggil dari setUp() test class yang memakai endpoint publik.
     *
     * Kunci disimpan sebagai hash di DB, jadi plaintext untuk header
     * Authorization diambil dari ->plain_text_key (diisi factory state
     * withPlainText saat kunci di-generate).
     */
    protected function useApiKey(): void
    {
        /** @var Website|null $website */
        $website = Website::first();

        $this->website = $website ?? Website::factory()->create();

        $this->apiKey = ApiKey::factory()->withPlainText()->create([
            'website_id' => $this->website->id,
        ]);

        $this->withHeader('Authorization', "Bearer {$this->apiKey->plain_text_key}");
    }
}
