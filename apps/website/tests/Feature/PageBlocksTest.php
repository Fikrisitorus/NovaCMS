<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Test render block halaman dinamis: setiap tipe block dari kolom JSON
 * Page harus terender ke partial blade-nya masing-masing.
 */
class PageBlocksTest extends TestCase
{
    /**
     * Semua tipe block wajib terender di halaman dinamis.
     */
    public function test_semua_tipe_block_terender(): void
    {
        Http::fake([
            '*/api/v1/pages/lengkap' => Http::response([
                'data' => [
                    'title' => 'Halaman Lengkap',
                    'slug' => 'lengkap',
                    'blocks' => [
                        ['type' => 'hero', 'data' => [
                            'heading' => 'Judul Hero Block',
                            'subheading' => 'Subheading hero.',
                            'button_label' => 'Mulai Sekarang',
                            'button_url' => 'https://example.com/mulai',
                        ]],
                        ['type' => 'faq', 'data' => ['questions' => [
                            ['question' => 'Apa itu NovaCMS?', 'answer' => 'Headless CMS modern.'],
                        ]]],
                        ['type' => 'gallery', 'data' => ['images' => [
                            'https://example.com/gambar-1.jpg',
                        ]]],
                        ['type' => 'pricing', 'data' => ['plans' => [
                            ['name' => 'Starter', 'price' => 'Rp 99rb', 'features' => ['Fitur A', 'Fitur B']],
                        ]]],
                        ['type' => 'contact', 'data' => [
                            'recipient_email' => 'halo@example.com',
                            'description' => 'Deskripsi block kontak.',
                        ]],
                    ],
                ],
            ]),
        ]);

        $response = $this->get('/lengkap');

        $response->assertOk();
        $response->assertSee('Judul Hero Block');
        $response->assertSee('Subheading hero.');
        $response->assertSee('Mulai Sekarang');
        $response->assertSee('Apa itu NovaCMS?');
        $response->assertSee('Headless CMS modern.');
        $response->assertSee('https://example.com/gambar-1.jpg');
        $response->assertSee('Starter');
        $response->assertSee('Rp 99rb');
        $response->assertSee('Fitur A');
        $response->assertSee('halo@example.com');
        $response->assertSee('Deskripsi block kontak.');
    }

    /**
     * Tipe block tidak dikenal harus dilewati tanpa menghempaskan halaman.
     */
    public function test_block_tipe_tidak_dikenal_dilewati(): void
    {
        Http::fake([
            '*/api/v1/pages/aneh' => Http::response([
                'data' => [
                    'title' => 'Halaman Aneh',
                    'slug' => 'aneh',
                    'blocks' => [
                        ['type' => 'tidak-ada-di-sistem', 'data' => ['foo' => 'bar']],
                    ],
                ],
            ]),
        ]);

        $response = $this->get('/aneh');

        $response->assertOk();
    }

    /**
     * Halaman tanpa blocks sama sekali tetap terender dengan judulnya.
     */
    public function test_halaman_tanpa_block_tetap_terender(): void
    {
        Http::fake([
            '*/api/v1/pages/kosong' => Http::response([
                'data' => ['title' => 'Halaman Kosong', 'slug' => 'kosong', 'blocks' => []],
            ]),
        ]);

        $response = $this->get('/kosong');

        $response->assertOk();
        $response->assertSee('Halaman Kosong');
    }
}
