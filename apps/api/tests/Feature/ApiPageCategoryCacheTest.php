<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\WithApiKey;
use Tests\TestCase;

/**
 * Test caching endpoint /pages dan /categories: response di-cache 15
 * menit dan di-invalidate saat data berubah (PageObserver /
 * PostObserver).
 */
class ApiPageCategoryCacheTest extends TestCase
{
    use RefreshDatabase, WithApiKey;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create();
        Cache::flush();

        $this->useApiKey();
    }

    public function test_response_index_pages_di_cache(): void
    {
        $this->getJson('/api/v1/pages')->assertOk();

        $this->assertTrue(Cache::has("api.pages.index.{$this->website->id}.1"));
    }

    public function test_response_detail_page_di_cache(): void
    {
        $page = $this->website->pages()->create([
            'title' => 'Tentang Kami',
            'slug' => 'tentang-kami',
            'is_published' => true,
        ]);

        $this->getJson("/api/v1/pages/{$page->slug}")->assertOk();

        $this->assertTrue(
            Cache::has("api.pages.show.{$this->website->id}.{$page->slug}")
        );
    }

    public function test_menyimpan_page_menghapus_cache_index(): void
    {
        $this->getJson('/api/v1/pages')->assertOk();
        $this->assertTrue(Cache::has("api.pages.index.{$this->website->id}.1"));

        $this->website->pages()->create([
            'title' => 'Halaman Baru',
            'slug' => 'halaman-baru',
            'is_published' => true,
        ]);

        $this->assertFalse(Cache::has("api.pages.index.{$this->website->id}.1"));
    }

    public function test_mengubah_page_menghapus_cache_detail(): void
    {
        $page = $this->website->pages()->create([
            'title' => 'Kontak',
            'slug' => 'kontak',
            'is_published' => true,
        ]);

        $this->getJson("/api/v1/pages/{$page->slug}")->assertOk();
        $this->assertTrue(
            Cache::has("api.pages.show.{$this->website->id}.{$page->slug}")
        );

        $page->update(['title' => 'Hubungi Kami']);

        $this->assertFalse(
            Cache::has("api.pages.show.{$this->website->id}.{$page->slug}")
        );
    }

    public function test_cache_pages_terpisah_per_website(): void
    {
        $otherWebsite = Website::factory()->create();
        $otherKey = ApiKey::factory()->create(['website_id' => $otherWebsite->id]);

        // Cache website sendiri.
        $this->getJson('/api/v1/pages')->assertOk();
        $this->assertTrue(Cache::has("api.pages.index.{$this->website->id}.1"));

        // Website lain belum di-cache.
        $this->assertFalse(Cache::has("api.pages.index.{$otherWebsite->id}.1"));

        $this->withHeader('Authorization', "Bearer {$otherKey->key}");
        $this->getJson('/api/v1/pages')->assertOk();
        $this->assertTrue(Cache::has("api.pages.index.{$otherWebsite->id}.1"));
    }

    public function test_response_categories_di_cache(): void
    {
        $this->getJson('/api/v1/categories')->assertOk();

        $this->assertTrue(Cache::has("api.categories.index.{$this->website->id}"));
    }

    public function test_menyimpan_post_menghapus_cache_categories(): void
    {
        $this->getJson('/api/v1/categories')->assertOk();
        $this->assertTrue(Cache::has("api.categories.index.{$this->website->id}"));

        Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
        ]);

        $this->assertFalse(Cache::has("api.categories.index.{$this->website->id}"));
    }

    public function test_mengubah_post_kategori_menghapus_cache_categories(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
        ]);
        $category->posts()->attach($post->id);

        // Attach ke kategori mengubah daftar kategori yang punya post.
        $this->getJson('/api/v1/categories')->assertOk();
        $this->assertTrue(Cache::has("api.categories.index.{$this->website->id}"));

        $post->forceDelete();

        $this->assertFalse(Cache::has("api.categories.index.{$this->website->id}"));
    }

    public function test_cache_categories_tidak_bocor_antara_tenant(): void
    {
        $otherWebsite = Website::factory()->create();
        $otherKey = ApiKey::factory()->create(['website_id' => $otherWebsite->id]);

        $this->getJson('/api/v1/categories')->assertOk();
        $this->assertTrue(Cache::has("api.categories.index.{$this->website->id}"));
        $this->assertFalse(Cache::has("api.categories.index.{$otherWebsite->id}"));

        $this->withHeader('Authorization', "Bearer {$otherKey->key}");
        $this->getJson('/api/v1/categories')->assertOk();
        $this->assertTrue(Cache::has("api.categories.index.{$otherWebsite->id}"));
    }

    public function test_pencarian_pages_di_cache_dengan_key_terpisah(): void
    {
        $this->getJson('/api/v1/pages')->assertOk();
        $this->getJson('/api/v1/pages?q=tentang')->assertOk();

        // Dua key berbeda: tanpa q dan dengan q (nomor halaman selalu
        // menjadi bagian cache key sejak /pages dipaginasi).
        $this->assertTrue(Cache::has("api.pages.index.{$this->website->id}.1"));
        $this->assertTrue(Cache::has("api.pages.index.{$this->website->id}.1.tentang"));
    }
}
