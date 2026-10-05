<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = fake()->word().'.jpg';

        return [
            'name' => fake()->words(2, true),
            'file_name' => $fileName,
            'mime_type' => 'image/jpeg',
            'path' => 'media/'.$fileName,
            'disk' => 'public',
            'size' => fake()->numberBetween(10000, 5000000),
            'alt_text' => fake()->sentence(3),
            'caption' => fake()->sentence(),
        ];
    }
}
