<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Test fitur homepage: memuat halaman "home" + post terbaru dari API.
 */
class HomePageTest extends TestCase
{
    /**
     * Homepage harus terender 200 walau API sama sekali tidak terjangkau,
     * sehingga situs tetap online saat backend bermasalah.
     */
    public function test_homepage_tetap_terender_saat_api_tidak_terjangkau(): void
    {
        Http::preventStrayRequests();
        Http::fake(fn () => Http::response(null, 500));

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Post Terbaru');
    }

    /**
     * Homepage merender block dari halaman "home" dan daftar post terbaru.
     */
    public function test_homepage_merender_block_dan_post_terbaru(): void
    {
        Http::fake([
            '*/pages/home' => Http::response([
                'data' => [
                    'id' => 'page-1',
                    'title' => 'Beranda',
                    'slug' => 'home',
                    'blocks' => [
                        ['type' => 'hero', 'data' => ['heading' => 'Selamat Datang di NovaCMS']],
                    ],
                ],
            ]),
            '*/posts*' => Http::response([
                'data' => [
                    [
                        'id' => 'post-1',
                        'title' => 'Post Pertama',
                        'slug' => 'post-pertama',
                        'excerpt' => 'Ringkasan post pertama.',
                    ],
                ],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 6, 'total' => 1],
            ]),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Selamat Datang di NovaCMS');
        $response->assertSee('Post Pertama');
    }

    /**
     * Homepage memakai judul SEO dari seo_meta halaman bila tersedia.
     */
    public function test_homepage_memakai_seo_meta_dari_api(): void
    {
        Http::fake([
            '*/pages/home' => Http::response([
                'data' => [
                    'title' => 'Beranda',
                    'slug' => 'home',
                    'seo_meta' => ['meta_title' => 'NovaCMS - CMS Modern'],
                    'blocks' => [],
                ],
            ]),
            '*/posts*' => Http::response(['data' => [], 'meta' => ['current_page' => 1, 'last_page' => 1]]),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('<title>NovaCMS - CMS Modern</title>', false);
    }
}
