<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithApiKey;
use Tests\TestCase;

/**
 * Regression test untuk perbaikan endpoint publik Page & Post.
 *
 * 1. GET /api/v1/pages/{slug} tidak boleh crash karena relasi 'sections'
 *    yang sudah dihapus, dan harus mengembalikan kolom 'blocks'.
 * 2. Post yang belum dipublikasi (is_published=false) atau yang
 *    dijadwalkan di masa depan (published_at > sekarang) tidak boleh
 *    muncul di endpoint publik maupun blog Blade.
 */
class ApiPagePostPublishTest extends TestCase
{
    use RefreshDatabase, WithApiKey;

    private Website $website;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useApiKey();

        $this->website = Website::create([
            'name' => 'Site Test',
            'domain' => 'test.example.com',
        ]);
    }

    public function test_page_detail_mengembalikan_blocks_tanpa_crash(): void
    {
        $page = Page::create([
            'website_id' => $this->website->id,
            'title' => 'Tentang Kami',
            'slug' => 'tentang-kami',
            'is_published' => true,
            'blocks' => [
                ['type' => 'hero', 'content' => ['title' => 'Halo']],
            ],
        ]);

        $response = $this->getJson("/api/v1/pages/{$page->slug}");

        $response->assertOk()
            ->assertJsonPath('data.slug', 'tentang-kami')
            ->assertJsonPath('data.blocks.0.type', 'hero')
            ->assertJsonMissingPath('data.sections');
    }

    public function test_page_detail_404_ketika_tidak_dipublikasi(): void
    {
        Page::create([
            'website_id' => $this->website->id,
            'title' => 'Draft',
            'slug' => 'draft-rahasia',
            'is_published' => false,
            'blocks' => null,
        ]);

        $this->getJson('/api/v1/pages/draft-rahasia')->assertNotFound();
    }

    public function test_post_index_hanya_menampilkan_post_yang_sudah_terbit(): void
    {
        // Sudah terbit -> tampil
        Post::create([
            'website_id' => $this->website->id,
            'title' => 'Terbit',
            'slug' => 'terbit',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        // published_at terisi tapi is_published=false -> tidak tampil
        Post::create([
            'website_id' => $this->website->id,
            'title' => 'Unpublished',
            'slug' => 'unpublished',
            'is_published' => false,
            'published_at' => now()->subDay(),
        ]);

        // is_published=true tapi dijadwalkan masa depan -> tidak tampil
        Post::create([
            'website_id' => $this->website->id,
            'title' => 'Terjadwal',
            'slug' => 'terjadwal',
            'is_published' => true,
            'published_at' => now()->addWeek(),
        ]);

        $this->getJson('/api/v1/posts')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'terbit')
            ->assertJsonMissingPath('data.1');
    }

    public function test_post_detail_404_untuk_unpublished_dan_terjadwal(): void
    {
        Post::create([
            'website_id' => $this->website->id,
            'title' => 'Unpublished',
            'slug' => 'unpublished',
            'is_published' => false,
            'published_at' => now()->subDay(),
        ]);

        Post::create([
            'website_id' => $this->website->id,
            'title' => 'Terjadwal',
            'slug' => 'terjadwal',
            'is_published' => true,
            'published_at' => now()->addWeek(),
        ]);

        $this->getJson('/api/v1/posts/unpublished')->assertNotFound();
        $this->getJson('/api/v1/posts/terjadwal')->assertNotFound();
    }

    public function test_blog_index_menyembunyikan_post_belum_terbit(): void
    {
        Post::create([
            'website_id' => $this->website->id,
            'title' => 'Terbit',
            'slug' => 'terbit',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        Post::create([
            'website_id' => $this->website->id,
            'title' => 'Terjadwal',
            'slug' => 'terjadwal',
            'is_published' => true,
            'published_at' => now()->addWeek(),
        ]);

        $response = $this->get('/blog');

        $response->assertOk()
            ->assertViewHas('posts');

        $slugs = $response->viewData('posts')->pluck('slug')->all();

        $this->assertSame(['terbit'], $slugs);
    }

    public function test_blog_detail_404_untuk_post_belum_terbit(): void
    {
        Post::create([
            'website_id' => $this->website->id,
            'title' => 'Unpublished',
            'slug' => 'unpublished',
            'is_published' => false,
            'published_at' => now()->subDay(),
        ]);

        $this->get('/blog/unpublished')->assertNotFound();
    }
}
