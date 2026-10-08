<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\WithApiKey;
use Tests\TestCase;

/**
 * Test caching endpoint publik Post: response di-cache 15 menit, dan
 * cache otomatis ter-invalidasi saat post disimpan atau dihapus.
 */
class ApiPostCacheTest extends TestCase
{
    use RefreshDatabase, WithApiKey;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->website = Website::factory()->create();
        $this->author = User::factory()->create();

        $this->useApiKey();
        Cache::flush();
    }

    public function test_response_index_di_cache(): void
    {
        $post = Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
        ]);

        $this->getJson('/api/v1/posts')->assertOk();
        $this->assertTrue(Cache::has("api.posts.index.{$this->website->id}.1"));

        // Paginator yang di-cache harus berisi post yang baru dibuat.
        $cached = Cache::get("api.posts.index.{$this->website->id}.1");
        $this->assertSame(1, $cached->total());
        $this->assertSame($post->slug, $cached->items()[0]->slug);
    }

    public function test_menyimpan_post_menghapus_cache_index(): void
    {
        Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
        ]);

        $this->getJson('/api/v1/posts')->assertOk();
        $this->assertTrue(Cache::has("api.posts.index.{$this->website->id}.1"));

        // Simpan post baru — observer harus menghapus cache index.
        Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
        ]);

        $this->assertFalse(Cache::has("api.posts.index.{$this->website->id}.1"));
        $this->assertFalse(Cache::has("api.posts.index.pages.{$this->website->id}"));
    }

    public function test_menyimpan_post_menghapus_cache_detail(): void
    {
        $post = Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
        ]);

        $this->getJson("/api/v1/posts/{$post->slug}")->assertOk();
        $this->assertTrue(Cache::has("api.posts.show.{$this->website->id}.{$post->slug}"));

        // Update post — observer harus menghapus cache detail-nya.
        $post->update(['title' => 'Judul baru saja']);

        $this->assertFalse(Cache::has("api.posts.show.{$this->website->id}.{$post->slug}"));
    }

    public function test_menghapus_post_menghapus_cache(): void
    {
        $post = Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
        ]);

        $this->getJson('/api/v1/posts')->assertOk();
        $this->getJson("/api/v1/posts/{$post->slug}")->assertOk();
        $this->assertTrue(Cache::has("api.posts.index.{$this->website->id}.1"));

        $post->forceDelete();

        $this->assertFalse(Cache::has("api.posts.index.{$this->website->id}.1"));
        $this->assertFalse(Cache::has("api.posts.show.{$this->website->id}.{$post->slug}"));
    }

    public function test_cache_detail_tetap_utuh_untuk_post_lain(): void
    {
        $other = Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
            'slug' => 'post-lain-yang-tersentuh',
        ]);

        $post = Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
        ]);

        $this->getJson("/api/v1/posts/{$other->slug}")->assertOk();
        $this->getJson("/api/v1/posts/{$post->slug}")->assertOk();
        $this->assertTrue(Cache::has("api.posts.show.{$this->website->id}.{$other->slug}"));

        $post->update(['title' => 'Judul diubah']);

        // Cache post lain harusnya tidak tersentuh.
        $this->assertTrue(Cache::has("api.posts.show.{$this->website->id}.{$other->slug}"));
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
