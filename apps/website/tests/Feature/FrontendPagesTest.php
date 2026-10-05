<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Test frontend publik: semua halaman harus merender dengan benar
 * menggunakan response API yang di-mock (Http::fake) — tidak ada
 * koneksi nyata ke apps/api saat test.
 */
class FrontendPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_homepage_merender_hero_dan_post_terbaru(): void
    {
        Http::fake([
            '*/api/v1/pages/home' => Http::response([
                'data' => [
                    'title' => 'Beranda',
                    'slug' => 'home',
                    'blocks' => [
                        ['type' => 'hero', 'data' => ['heading' => 'Selamat Datang']],
                    ],
                    'seo_meta' => ['meta_title' => 'Beranda Resmi', 'meta_description' => 'Deskripsi beranda'],
                ],
            ]),
            '*/api/v1/posts*' => Http::response([
                'data' => [
                    ['id' => '1', 'title' => 'Post Pertama', 'slug' => 'post-pertama', 'excerpt' => 'Ringkasan.'],
                ],
                'meta' => ['current_page' => 1, 'last_page' => 1],
            ]),
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Selamat Datang')
            ->assertSee('Post Pertama');
    }

    public function test_blog_index_merender_list_dan_pagination(): void
    {
        Http::fake([
            '*/api/v1/posts*' => Http::response([
                'data' => [
                    ['id' => '1', 'title' => 'Post Satu', 'slug' => 'post-satu', 'excerpt' => null],
                    ['id' => '2', 'title' => 'Post Dua', 'slug' => 'post-dua', 'excerpt' => null],
                ],
                'meta' => ['current_page' => 1, 'last_page' => 3, 'per_page' => 15, 'total' => 45],
            ]),
        ]);

        $response = $this->get('/blog');

        $response->assertOk()
            ->assertSee('Post Satu')
            ->assertSee('Post Dua')
            ->assertSee('?page=2'); // link pagination
    }

    public function test_blog_detail_merender_content_dan_seo(): void
    {
        Http::fake([
            '*/api/v1/posts/judul-post' => Http::response([
                'data' => [
                    'title' => 'Judul Post',
                    'slug' => 'judul-post',
                    'content' => '<p>Isi lengkap dari post ini.</p>',
                    'seo_meta' => [
                        'meta_title' => 'Judul Post (SEO)',
                        'meta_description' => 'Deskripsi SEO.',
                    ],
                ],
            ]),
        ]);

        $response = $this->get('/blog/judul-post');

        $response->assertOk()
            ->assertSee('Judul Post')
            ->assertSee('Isi lengkap dari post ini.')
            ->assertSee('<title>Judul Post (SEO)', false);
    }

    public function test_blog_detail_404_saat_post_tidak_ada(): void
    {
        Http::fake([
            '*/api/v1/posts/tidak-ada' => Http::response(['message' => 'Not found.'], 404),
        ]);

        $this->get('/blog/tidak-ada')->assertNotFound();
    }

    public function test_category_index_merender_daftar_kategori(): void
    {
        Http::fake([
            '*/api/v1/categories*' => Http::response([
                'data' => [
                    ['id' => '1', 'name' => 'Berita', 'slug' => 'berita', 'description' => 'Kabar terbaru.', 'posts_count' => 5],
                ],
            ]),
        ]);

        $response = $this->get('/categories');

        $response->assertOk()
            ->assertSee('Berita')
            ->assertSee('Kabar terbaru.')
            ->assertSee('5 post');
    }

    public function test_page_dinamis_merender_blocks(): void
    {
        Http::fake([
            '*/api/v1/pages/tentang-kami' => Http::response([
                'data' => [
                    'title' => 'Tentang Kami',
                    'slug' => 'tentang-kami',
                    'blocks' => [
                        ['type' => 'faq', 'data' => ['questions' => [
                            ['question' => 'Apa itu NovaCMS?', 'answer' => 'Headless CMS.'],
                        ]]],
                    ],
                ],
            ]),
        ]);

        $response = $this->get('/tentang-kami');

        $response->assertOk()
            ->assertSee('Tentang Kami')
            ->assertSee('Apa itu NovaCMS?');
    }

    public function test_page_dinamis_404_saat_tidak_ditemukan(): void
    {
        Http::fake([
            '*/api/v1/pages/tidak-ada' => Http::response([], 404),
        ]);

        $this->get('/tidak-ada')->assertNotFound();
    }
}
