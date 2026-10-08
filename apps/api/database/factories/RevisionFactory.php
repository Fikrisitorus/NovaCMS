<?php

namespace Database\Factories;

use App\Models\Revision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Revision>
 */
class RevisionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'revisable_type' => null,
            'revisable_id' => null,
            'content' => [
                'attribute' => 'content',
                'data' => null,
            ],
            'user_id' => null,
            'summary' => null,
            'created_at' => now(),
        ];
    }
}
