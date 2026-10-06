<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithApiKey;
use Tests\TestCase;

/**
 * Test endpoint pencarian publik: parameter ?q= memfilter post, page,
 * media, dan post dalam kategori.
 */
class ApiSearchTest extends TestCase
{
    use RefreshDatabase, WithApiKey;

    private Website $website;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->website = Website::factory()->create();
        $this->author = User::factory()->create();

        $this->useApiKey();
    }

    public function test_search_post_berdasarkan_title(): void
    {
        $match = Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
            'title' => 'Tutorial Laravel NovaCMS',
        ]);
        Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
            'title' => 'Panduan Memasak',
        ]);

        $response = $this->getJson('/api/v1/posts?q=Laravel');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', $match->slug);
    }

    public function test_search_post_berdasarkan_content(): void
    {
        $match = Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
            'title' => 'Judul Netral',
            'content' => '<p>Isi membahas queue dan job Laravel.</p>',
        ]);

        $response = $this->getJson('/api/v1/posts?q=queue');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', $match->slug);
    }

    public function test_search_post_case_insensitive(): void
    {
        Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
            'title' => 'LARAVEL Advanced',
        ]);

        $this->getJson('/api/v1/posts?q=laravel')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_search_post_tidak_mencari_draft(): void
    {
        Post::factory()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
            'title' => 'Laravel Draft Belum Terbit',
            'is_published' => false,
            'published_at' => null,
        ]);

        $this->getJson('/api/v1/posts?q=Laravel')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    public function test_search_tanpa_parameter_mengembalikan_semua(): void
    {
        Post::factory()->published()->count(3)->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
        ]);

        $this->getJson('/api/v1/posts')
            ->assertOk()
            ->assertJsonPath('meta.total', 3);
    }

    public function test_search_post_dengan_query_kosong_mengembalikan_semua(): void
    {
        Post::factory()->published()->count(2)->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
        ]);

        $this->getJson('/api/v1/posts?q=')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_search_page_berdasarkan_title(): void
    {
        $match = Page::factory()->create([
            'website_id' => $this->website->id,
            'title' => 'Tentang Kami',
            'is_published' => true,
        ]);
        Page::factory()->create([
            'website_id' => $this->website->id,
            'title' => 'Kebijakan Privasi',
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/pages?q=Tentang');

        $response->assertOk()
            ->assertJsonPath('data.0.slug', $match->slug);
    }

    public function test_search_media_berdasarkan_nama(): void
    {
        $match = Media::factory()->create([
            'website_id' => $this->website->id,
            'name' => 'Foto Hero Laravel',
        ]);
        Media::factory()->create([
            'website_id' => $this->website->id,
            'name' => 'Logo Perusahaan',
        ]);

        $response = $this->getJson('/api/v1/media?q=Hero');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $match->id);
    }

    public function test_search_post_dalam_kategori(): void
    {
        $category = Category::factory()->create();
        $match = Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
            'title' => 'Belajar Eloquent',
        ]);
        $other = Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
            'title' => 'Tips Memasak',
        ]);
        $category->posts()->attach([$match->id, $other->id]);

        $response = $this->getJson("/api/v1/categories/{$category->slug}/posts?q=Eloquent");

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', $match->slug);
    }

    public function test_search_kata_yang_tidak_ada_mengembalikan_kosong(): void
    {
        Post::factory()->published()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
            'title' => 'Tutorial Laravel',
        ]);

        $this->getJson('/api/v1/posts?q=xyznonexistent')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }
}
