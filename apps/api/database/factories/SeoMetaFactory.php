<?php

namespace Database\Factories;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeoMeta>
 */
class SeoMetaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meta_title' => fake()->sentence(4),
            'meta_description' => fake()->paragraph(),
            'og_image' => null,
            'canonical_url' => null,
            'meta_keywords' => fake()->words(3),
        ];
    }
}
