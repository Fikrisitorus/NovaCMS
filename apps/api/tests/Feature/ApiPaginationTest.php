<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Page;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\WithApiKey;
use Tests\TestCase;

/**
 * Test paginasi endpoint /pages dan /websites: response mengikuti
 * format paginasi standar Laravel (data + links + meta) dengan 15 item
 * per halaman, dan isolasi multi-tenant tetap utuh di tiap halaman.
 */
class ApiPaginationTest extends TestCase
{
    use RefreshDatabase, WithApiKey;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->useApiKey();
    }

    public function test_page_index_terpaginasi_15_per_halaman(): void
    {
        Page::factory()->count(20)->create([
            'website_id' => $this->website->id,
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/pages');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonCount(15, 'data');
    }

    public function test_page_index_halaman_kedua(): void
    {
        Page::factory()->count(20)->create([
            'website_id' => $this->website->id,
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/pages?page=2');

        $response->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonCount(5, 'data');
    }

    public function test_page_index_hanya_menampilkan_halaman_terbit(): void
    {
        Page::factory()->count(10)->create([
            'website_id' => $this->website->id,
            'is_published' => true,
        ]);
        Page::factory()->create([
            'website_id' => $this->website->id,
            'is_published' => false,
        ]);

        $this->getJson('/api/v1/pages')
            ->assertOk()
            ->assertJsonPath('meta.total', 10)
            ->assertJsonCount(10, 'data');
    }

    public function test_page_index_menyertakan_relasi_website(): void
    {
        Page::factory()->create([
            'website_id' => $this->website->id,
            'is_published' => true,
        ]);

        $this->getJson('/api/v1/pages')
            ->assertOk()
            ->assertJsonPath('data.0.website.id', $this->website->id)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'website_id',
                        'title',
                        'slug',
                        'is_published',
                        'blocks',
                        'website',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_page_index_terisolasi_per_tenant(): void
    {
        $otherWebsite = Website::factory()->create();

        Page::factory()->count(3)->create([
            'website_id' => $this->website->id,
            'is_published' => true,
        ]);
        Page::factory()->count(2)->create([
            'website_id' => $otherWebsite->id,
            'is_published' => true,
        ]);

        // Kunci tenant lain melihat website-nya sendiri di halaman 1.
        $otherKey = ApiKey::factory()->create([
            'website_id' => $otherWebsite->id,
        ]);

        $this->withHeader('Authorization', "Bearer {$otherKey->key}");

        $this->getJson('/api/v1/pages')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(2, 'data');
    }

    public function test_page_index_mencache_setiap_halaman_dengan_key_terpisah(): void
    {
        Page::factory()->count(20)->create([
            'website_id' => $this->website->id,
            'is_published' => true,
        ]);

        $this->getJson('/api/v1/pages')->assertOk();
        $this->getJson('/api/v1/pages?page=2')->assertOk();

        // Nomor halaman disertakan di cache key; registry menyimpan
        // full cache key agar observer bisa menghapusnya.
        $this->assertTrue(
            Cache::has("api.pages.index.{$this->website->id}.1")
        );
        $this->assertTrue(
            Cache::has("api.pages.index.{$this->website->id}.2")
        );
        $this->assertSame(
            [
                "api.pages.index.{$this->website->id}.1",
                "api.pages.index.{$this->website->id}.2",
            ],
            Cache::get("api.pages.index.keys.{$this->website->id}")
        );
    }

    public function test_website_index_terpaginasi_15_per_halaman(): void
    {
        Page::factory()->count(20)->create([
            'website_id' => $this->website->id,
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/websites');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonCount(15, 'data');
    }

    public function test_website_index_hanya_menampilkan_halaman_terbit(): void
    {
        Page::factory()->count(10)->create([
            'website_id' => $this->website->id,
            'is_published' => true,
        ]);
        Page::factory()->create([
            'website_id' => $this->website->id,
            'is_published' => false,
        ]);

        $this->getJson('/api/v1/websites')
            ->assertOk()
            ->assertJsonPath('meta.total', 10)
            ->assertJsonCount(10, 'data');
    }

    public function test_website_index_halaman_kedua(): void
    {
        Page::factory()->count(20)->create([
            'website_id' => $this->website->id,
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/websites?page=2');

        $response->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonCount(5, 'data');
    }

    public function test_website_index_terisolasi_per_tenant(): void
    {
        $otherWebsite = Website::factory()->create();

        Page::factory()->count(3)->create([
            'website_id' => $this->website->id,
            'is_published' => true,
        ]);
        Page::factory()->count(2)->create([
            'website_id' => $otherWebsite->id,
            'is_published' => true,
        ]);

        // Kunci tenant lain hanya melihat halaman website-nya sendiri.
        $otherKey = ApiKey::factory()->create([
            'website_id' => $otherWebsite->id,
        ]);

        $this->withHeader('Authorization', "Bearer {$otherKey->key}");

        $this->getJson('/api/v1/websites')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(2, 'data');
    }

    public function test_website_nonaktif_ditolak_403(): void
    {
        $this->website->update(['is_active' => false]);

        $this->getJson('/api/v1/websites')
            ->assertForbidden()
            ->assertJsonPath('message', 'Website pemilik kunci ini sudah dinonaktifkan.');
    }
}
