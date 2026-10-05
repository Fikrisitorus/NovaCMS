<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\SeoMeta;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk endpoint publik yang baru ditambahkan dan kelengkapan
 * response PostResource (relasi author, categories, seo_meta, pagination).
 */
class ApiPublicEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private Website $website;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->website = Website::factory()->create();
        $this->author = User::factory()->create();
    }

    public function test_post_index_mengembalikan_relasi_dan_pagination(): void
    {
        Post::factory()->count(20)->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/posts');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'excerpt',
                        'content',
                        'featured_image',
                        'is_published',
                        'published_at',
                        'author' => ['id', 'name'],
                        'categories',
                        'seo_meta',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
                'links',
            ])
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 20);
    }

    public function test_post_detail_menyertakan_author_categories_dan_seo(): void
    {
        $category = Category::factory()->create();

        $post = Post::factory()->create([
            'website_id' => $this->website->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $post->categories()->attach($category);
        SeoMeta::factory()->create([
            'seoable_type' => $post->getMorphClass(),
            'seoable_id' => $post->id,
        ]);

        $response = $this->getJson("/api/v1/posts/{$post->slug}");

        $response->assertOk()
            ->assertJsonPath('data.author.name', $this->author->name)
            ->assertJsonPath('data.categories.0.slug', $category->slug)
            ->assertJsonStructure([
                'data' => [
                    'seo_meta' => [
                        'meta_title',
                        'meta_description',
                        'og_image',
                        'canonical_url',
                        'meta_keywords',
                    ],
                ],
            ]);
    }

    public function test_category_index_mengembalikan_jumlah_post(): void
    {
        $category = Category::factory()->create();
        $published = Post::factory()->create([
            'website_id' => $this->website->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        $draft = Post::factory()->create([
            'website_id' => $this->website->id,
            'is_published' => false,
            'published_at' => null,
        ]);
        $category->posts()->attach([$published->id, $draft->id]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonPath('data.0.posts_count', 1);
    }

    public function test_category_posts_hanya_menampilkan_post_terbit(): void
    {
        $category = Category::factory()->create();
        $published = Post::factory()->create([
            'website_id' => $this->website->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        $draft = Post::factory()->create([
            'website_id' => $this->website->id,
            'is_published' => false,
            'published_at' => null,
        ]);
        $category->posts()->attach([$published->id, $draft->id]);

        $response = $this->getJson("/api/v1/categories/{$category->slug}/posts");

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', $published->slug);
    }

    public function test_category_posts_404_untuk_slug_tidak_dikenal(): void
    {
        $this->getJson('/api/v1/categories/tidak-ada/posts')->assertNotFound();
    }

    public function test_media_index_tidak_mengekspos_path_internal(): void
    {
        Media::factory()->create();

        $response = $this->getJson('/api/v1/media');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'file_name', 'mime_type', 'size', 'url'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        // Pastikan field internal tidak ikut di-expose.
        $this->assertArrayNotHasKey('path', $response->json('data.0'));
        $this->assertArrayNotHasKey('disk', $response->json('data.0'));
    }
}
