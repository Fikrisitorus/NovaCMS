<?php

namespace Tests\Feature;

use App\Models\ApiKey;
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
 * Test isolasi multi-tenant: kunci API hanya boleh mengakses data
 * website pemilik kunci tersebut, tidak pernah tenant lain.
 */
class ApiTenantIsolationTest extends TestCase
{
    use RefreshDatabase, WithApiKey;

    private Website $otherWebsite;

    private ApiKey $otherApiKey;

    protected function setUp(): void
    {
        parent::setUp();

        // Website pertama + kunci disiapkan oleh useApiKey().
        $this->useApiKey();

        $this->otherWebsite = Website::factory()->create();
        $this->otherApiKey = ApiKey::factory()->create([
            'website_id' => $this->otherWebsite->id,
        ]);
    }

    public function test_post_index_tidak_menampilkan_post_tenant_lain(): void
    {
        $ownPost = Post::factory()->published()->create([
            'website_id' => $this->apiKey->website_id,
            'author_id' => User::factory()->create()->id,
            'title' => 'Post Milik Sendiri',
        ]);
        Post::factory()->published()->create([
            'website_id' => $this->otherWebsite->id,
            'author_id' => User::factory()->create()->id,
            'title' => 'Post Tenant Lain',
        ]);

        $response = $this->getJson('/api/v1/posts');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', $ownPost->slug);
    }

    public function test_post_detail_tenant_lain_mengembalikan_404(): void
    {
        $otherPost = Post::factory()->published()->create([
            'website_id' => $this->otherWebsite->id,
            'author_id' => User::factory()->create()->id,
        ]);

        $this->getJson("/api/v1/posts/{$otherPost->slug}")
            ->assertNotFound();
    }

    public function test_post_detail_milik_sendiri_tetap_bisa_diakses(): void
    {
        $ownPost = Post::factory()->published()->create([
            'website_id' => $this->apiKey->website_id,
            'author_id' => User::factory()->create()->id,
        ]);

        $this->getJson("/api/v1/posts/{$ownPost->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $ownPost->slug);
    }

    public function test_page_index_hanya_milik_sendiri(): void
    {
        $ownPage = Page::factory()->create([
            'website_id' => $this->apiKey->website_id,
            'is_published' => true,
        ]);
        Page::factory()->create([
            'website_id' => $this->otherWebsite->id,
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/pages');

        $response->assertOk()
            ->assertJsonPath('data.0.slug', $ownPage->slug)
            ->assertJsonMissing(['slug' => Page::where('website_id', $this->otherWebsite->id)->value('slug')]);
    }

    public function test_page_detail_tenant_lain_404(): void
    {
        $otherPage = Page::factory()->create([
            'website_id' => $this->otherWebsite->id,
            'is_published' => true,
        ]);

        $this->getJson("/api/v1/pages/{$otherPage->slug}")
            ->assertNotFound();
    }

    public function test_media_index_hanya_milik_sendiri(): void
    {
        $ownMedia = Media::factory()->create([
            'website_id' => $this->apiKey->website_id,
            'name' => 'Media Sendiri',
        ]);
        Media::factory()->create([
            'website_id' => $this->otherWebsite->id,
            'name' => 'Media Lain',
        ]);

        $response = $this->getJson('/api/v1/media');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $ownMedia->id);
    }

    public function test_category_index_hanya_yang_memiliki_post_sendiri(): void
    {
        $category = Category::factory()->create();
        $ownPost = Post::factory()->published()->create([
            'website_id' => $this->apiKey->website_id,
            'author_id' => User::factory()->create()->id,
        ]);
        $otherPost = Post::factory()->published()->create([
            'website_id' => $this->otherWebsite->id,
            'author_id' => User::factory()->create()->id,
        ]);
        $category->posts()->attach([$ownPost->id, $otherPost->id]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonPath('data.0.slug', $category->slug)
            ->assertJsonPath('data.0.posts_count', 1);
    }

    public function test_category_posts_tidak_menampilkan_post_tenant_lain(): void
    {
        $category = Category::factory()->create();
        $ownPost = Post::factory()->published()->create([
            'website_id' => $this->apiKey->website_id,
            'author_id' => User::factory()->create()->id,
        ]);
        $otherPost = Post::factory()->published()->create([
            'website_id' => $this->otherWebsite->id,
            'author_id' => User::factory()->create()->id,
        ]);
        $category->posts()->attach([$ownPost->id, $otherPost->id]);

        $response = $this->getJson("/api/v1/categories/{$category->slug}/posts");

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', $ownPost->slug);
    }

    public function test_website_index_hanya_mengembalikan_website_sendiri(): void
    {
        $response = $this->getJson('/api/v1/websites');

        $response->assertOk()
            ->assertJsonPath('data.id', $this->apiKey->website_id)
            ->assertJsonMissing(['id' => $this->otherWebsite->id]);
    }

    public function test_website_detail_domain_tenant_lain_404(): void
    {
        $this->getJson("/api/v1/websites/{$this->otherWebsite->domain}")
            ->assertNotFound();
    }

    public function test_website_yang_dinonaktifkan_ditolak_403(): void
    {
        $website = Website::find($this->apiKey->website_id);
        $website->update(['is_active' => false]);

        $this->getJson('/api/v1/websites')
            ->assertForbidden()
            ->assertJsonPath('message', 'Website pemilik kunci ini sudah dinonaktifkan.');
    }

    public function test_kunci_tenant_lain_tidak_bisa_akses_website_kita(): void
    {
        // Buat post milik website kita, lalu akses pakai kunci tenant lain.
        Post::factory()->published()->create([
            'website_id' => $this->apiKey->website_id,
            'author_id' => User::factory()->create()->id,
        ]);

        $this->withHeader('Authorization', "Bearer {$this->otherApiKey->key}");

        $this->getJson('/api/v1/posts')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }
}
