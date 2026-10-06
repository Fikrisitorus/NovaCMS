<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\SeoMeta;
use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder data demo untuk development dan evaluasi endpoint API publik.
 * Bisa dijalankan ulang kapan saja (idempotent).
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $website = Website::firstOrCreate(
            ['domain' => 'demo.novacms.local'],
            ['name' => 'Demo NovaCMS', 'is_active' => true],
        );

        $author = User::firstOrCreate(
            ['email' => 'redaksi@novacms.local'],
            ['name' => 'Redaksi Demo', 'password' => bcrypt('password')],
        );

        $media = Media::firstOrCreate(
            ['file_name' => 'hero-demo.jpg'],
            [
                'name' => 'Hero Demo',
                'mime_type' => 'image/jpeg',
                'path' => 'media/hero-demo.jpg',
                'disk' => 'public',
                'size' => 245000,
                'alt_text' => 'Gambar hero demo NovaCMS',
            ],
        );

        $categories = collect(['Berita', 'Tutorial', 'Pengumuman'])->map(function ($name) {
            return Category::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => "Kategori $name untuk situs demo.",
            ]);
        });

        $posts = [
            [
                'title' => 'Selamat Datang di NovaCMS',
                'content' => '<p>NovaCMS adalah headless CMS yang dibangun dengan Laravel 12 dan Filament v3.</p>',
                'category' => 'Pengumuman',
            ],
            [
                'title' => 'Tutorial: Membuat Halaman Pertama Anda',
                'content' => '<p>Panduan langkah demi langkah membuat halaman di panel admin Filament.</p>',
                'category' => 'Tutorial',
            ],
            [
                'title' => 'Rilis v0.2: API Publik Lengkap',
                'content' => '<p>Endpoint publik sekarang mendukung pagination, filter publish, dan relasi author, categories, seo_meta.</p>',
                'category' => 'Berita',
            ],
        ];

        foreach ($posts as $data) {
            $post = Post::firstOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'website_id' => $website->id,
                    'author_id' => $author->id,
                    'title' => $data['title'],
                    'excerpt' => Str::limit(strip_tags($data['content']), 120),
                    'content' => $data['content'],
                    'featured_image' => $media->url,
                    'is_published' => true,
                    'published_at' => now()->subHours(rand(1, 48)),
                ],
            );

            $category = $categories->firstWhere('name', $data['category']);
            $post->categories()->syncWithoutDetaching([$category->id]);

            SeoMeta::firstOrCreate(
                [
                    'seoable_type' => $post->getMorphClass(),
                    'seoable_id' => $post->id,
                ],
                [
                    'meta_title' => $post->title,
                    'meta_description' => $post->excerpt,
                ],
            );
        }

        $homePage = Page::firstOrCreate(
            ['slug' => 'home'],
            [
                'website_id' => $website->id,
                'title' => 'Beranda',
                'is_published' => true,
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Bangun Situs Modern Lebih Cepat dengan NovaCMS',
                            'subheading' => 'Headless CMS dan Visual Content Builder berbasis Laravel 12 & Filament v3 untuk pengalaman pengembang dan editor yang maksimal.',
                            'button_label' => 'Jelajahi Blog',
                            'button_url' => '/blog',
                        ],
                    ],
                    [
                        'type' => 'faq',
                        'data' => [
                            'heading' => 'Pertanyaan yang Sering Diajukan',
                            'questions' => [
                                [
                                    'question' => 'Apa itu NovaCMS?',
                                    'answer' => 'NovaCMS adalah Headless CMS modern dengan panel admin berbasis Filament dan API publik untuk frontend apa pun.',
                                ],
                                [
                                    'question' => 'Bagaimana cara menambahkan konten baru?',
                                    'answer' => 'Masuk ke panel admin di /admin, pilih menu Posts atau Site Pages, dan gunakan visual block builder untuk menyusun halaman.',
                                ],
                                [
                                    'question' => 'Apakah mendukung pencarian?',
                                    'answer' => 'Ya, endpoint publik mendukung parameter query ?q= untuk artikel, halaman, kategori, dan media.',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        SeoMeta::firstOrCreate(
            [
                'seoable_type' => $homePage->getMorphClass(),
                'seoable_id' => $homePage->id,
            ],
            [
                'meta_title' => 'NovaCMS — Modern Headless CMS & Website Builder',
                'meta_description' => 'Platform Headless CMS dengan integrasi Laravel 12, Filament, dan frontend modern.',
            ]
        );

        $this->command->info('Demo data selesai: 1 website, 1 user, 1 page (home), 3 kategori, 1 media, 3 post.');
    }
}
