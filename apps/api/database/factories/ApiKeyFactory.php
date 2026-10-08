<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ApiKey>
 */
class ApiKeyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'website_id' => Website::factory(),
            'name' => fake()->word(),
            'key' => 'novacms_'.Str::random(40),
        ];
    }
}
