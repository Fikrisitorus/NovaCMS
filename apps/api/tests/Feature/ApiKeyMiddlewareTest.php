<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Post;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Test middleware api.key: otentikasi via kunci API dan
 * rate limiting per kunci pada endpoint publik /api/v1.
 */
class ApiKeyMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private ApiKey $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->website = Website::factory()->create();
        $this->apiKey = ApiKey::factory()->withPlainText()->create([
            'website_id' => $this->website->id,
        ]);

        // Rate limit memakai cache; pastikan bersih antar test.
        Cache::flush();

    }

    public function test_request_tanpa_kunci_ditolak_401(): void
    {
        $this->getJson('/api/v1/websites')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Kunci API tidak valid atau tidak disertakan.');
    }

    public function test_kunci_tidak_valid_ditolak_401(): void
    {
        $this->getJson('/api/v1/websites', [
            'Authorization' => 'Bearer novacms_tidakada',
        ])
            ->assertUnauthorized();
    }

    public function test_kunci_lewat_query_string_diterima(): void
    {
        $this->getJson("/api/v1/websites?api_key={$this->apiKey->plain_text_key}")
            ->assertOk();
    }

    public function test_kunci_lewat_bearer_header_diterima(): void
    {
        $this->getJson('/api/v1/websites', [
            'Authorization' => "Bearer {$this->apiKey->plain_text_key}",
        ])
            ->assertOk();
    }

    public function test_response_mengandung_header_rate_limit(): void
    {
        $response = $this->getJson("/api/v1/websites?api_key={$this->apiKey->plain_text_key}");

        $response->assertOk()
            ->assertHeader('X-RateLimit-Limit', '60')
            ->assertHeader('X-RateLimit-Remaining', '59');
    }

    public function test_kunci_di_revoke_ditolak_401(): void
    {
        $this->apiKey->update(['revoked_at' => now()]);

        $this->getJson("/api/v1/websites?api_key={$this->apiKey->plain_text_key}")
            ->assertUnauthorized();
    }

    public function test_melewati_rate_limit_mengembalikan_429(): void
    {
        // Batas 60 request/menit; request ke-61 harus ditolak.
        for ($i = 0; $i < 60; $i++) {
            $this->getJson("/api/v1/websites?api_key={$this->apiKey->plain_text_key}");
        }

        $response = $this->getJson("/api/v1/websites?api_key={$this->apiKey->plain_text_key}");

        $response->assertStatus(429)
            ->assertJsonPath('message', 'Terlalu banyak request. Coba lagi nanti.')
            ->assertHeader('X-RateLimit-Remaining', '0');
    }

    public function test_rate_limit_terpisah_per_kunci(): void
    {
        $otherKey = ApiKey::factory()->withPlainText()->create([
            'website_id' => $this->website->id,
        ]);

        for ($i = 0; $i < 60; $i++) {
            $this->getJson("/api/v1/websites?api_key={$this->apiKey->plain_text_key}");
        }

        // Kunci pertama sudah throttle, tapi kunci kedua masih bebas.
        $this->getJson("/api/v1/websites?api_key={$otherKey->plain_text_key}")
            ->assertOk()
            ->assertHeader('X-RateLimit-Remaining', '59');
    }

    public function test_pemakaian_dicatat_di_cache_untuk_flush(): void
    {
        $this->getJson("/api/v1/websites?api_key={$this->apiKey->plain_text_key}");

        $this->assertNotNull(Cache::get("api_key.{$this->apiKey->id}.last_used"));
    }

    public function test_endpoint_post_juga_terproteksi(): void
    {
        Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => User::factory()->create()->id,
        ]);

        $this->getJson('/api/v1/posts')
            ->assertUnauthorized();

        $this->getJson("/api/v1/posts?api_key={$this->apiKey->plain_text_key}")
            ->assertOk();
    }

    public function test_kunci_disimpan_sebagai_hash_bukan_plaintext(): void
    {
        // Isi DB tidak boleh berisi plaintext — hash bcrypt berawalan $2y$.
        $this->assertStringStartsWith('$2y$', $this->apiKey->key);
        $this->assertStringNotContainsString($this->apiKey->plain_text_key, $this->apiKey->key);

        // Prefix 8 karakter pertama plaintext ikut disimpan untuk lookup.
        $this->assertSame('novacms_', $this->apiKey->key_prefix);
        $this->assertSame(
            substr($this->apiKey->plain_text_key, 0, 8),
            $this->apiKey->key_prefix
        );

        // Plaintext yang mengandung prefix saja (tidak cocok hash) tetap ditolak.
        $this->getJson('/api/v1/websites', [
            'Authorization' => 'Bearer '.$this->apiKey->key_prefix.Str::random(40),
        ])
            ->assertUnauthorized();
    }

    public function test_lookup_prefix_tidak_aktif_tanpa_hash_cocok(): void
    {
        // Satu kunci (milik setUp) memakai prefix ini, jadi lookup
        // prefix pasti menemukan kandidat — tapi Hash::check gagal.
        $this->assertSame(1, ApiKey::where('key_prefix', 'novacms_')->count());

        // Prefix 8 karakter pertama plaintext sudah ada di DB, tapi
        // hash-nya tidak cocok dengan plaintext ini — tetap 401.
        $bogus = 'novacms_'.Str::random(40);

        $this->getJson("/api/v1/websites?api_key={$bogus}")
            ->assertUnauthorized();
    }
}
