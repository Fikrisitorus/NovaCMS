<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Test fitur blog: list post terpaginasi dan detail post per slug.
 */
class BlogTest extends TestCase
{
    /**
     * Halaman daftar blog menampilkan post dari API.
     */
    public function test_index_menampilkan_daftar_post(): void
    {
        Http::fake([
            '*/posts*' => Http::response([
                'data' => [
                    ['id' => 'p1', 'title' => 'Belajar Laravel', 'slug' => 'belajar-laravel', 'excerpt' => 'Panduan dasar.'],
                ],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 15, 'total' => 1],
            ]),
        ]);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee('Belajar Laravel');
    }

    /**
     * Daftar blog merender navigasi pagination bila ada beberapa halaman.
     */
    public function test_index_menampilkan_pagination(): void
    {
        Http::fake([
            '*/posts*' => Http::response([
                'data' => [['id' => 'p1', 'title' => 'Post Halaman 2', 'slug' => 'post-halaman-2']],
                'meta' => ['current_page' => 2, 'last_page' => 3, 'per_page' => 15, 'total' => 30],
            ]),
        ]);

        $response = $this->get(route('blog.index', ['page' => 2]));

        $response->assertOk();
        $response->assertSee('Post Halaman 2');
        $response->assertSee('aria-label="Pagination"', false);
    }

    /**
     * Detail post merender konten dan SEO meta sesuai data API.
     */
    public function test_show_merender_post_dan_seo_meta(): void
    {
        Http::fake([
            '*/posts/membangun-website' => Http::response([
                'data' => [
                    'id' => 'p1',
                    'title' => 'Membangun Website Modern',
                    'slug' => 'membangun-website',
                    'excerpt' => 'Ringkasan.',
                    'content' => '<p>Konten lengkap post.</p>',
                    'published_at' => '2026-09-01T10:00:00Z',
                    'author' => ['id' => 'u1', 'name' => 'Fikri'],
                    'categories' => [['id' => 'c1', 'name' => 'Web', 'slug' => 'web']],
                    'seo_meta' => [
                        'meta_title' => 'Website Modern | NovaCMS',
                        'meta_description' => 'Deskripsi SEO kustom.',
                    ],
                ],
            ]),
        ]);

        $response = $this->get(route('blog.show', 'membangun-website'));

        $response->assertOk();
        $response->assertSee('Membangun Website Modern');
        $response->assertSee('Konten lengkap post.');
        $response->assertSee('<title>Website Modern | NovaCMS</title>', false);
        $response->assertSee('Deskripsi SEO kustom.', false);
        $response->assertSee('Fikri');
        $response->assertSee('Web');
    }

    /**
     * Detail post mengembalikan 404 bila slug tidak ditemukan di API.
     */
    public function test_show_mengembalikan_404_bila_post_tidak_ada(): void
    {
        Http::fake([
            '*/posts/tidak-ada' => Http::response(['message' => 'Not found'], 404),
        ]);

        $response = $this->get(route('blog.show', 'tidak-ada'));

        $response->assertNotFound();
    }
}
