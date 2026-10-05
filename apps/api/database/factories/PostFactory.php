<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'website_id' => Website::factory(),
            'author_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'featured_image' => null,
            'excerpt' => fake()->paragraph(),
            'content' => '<p>'.fake()->paragraph(3).'</p>',
            'is_published' => false,
            'published_at' => null,
        ];
    }

    /**
     * Post sudah terbit (is_published true dan published_at sudah lewat).
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
    }
}
