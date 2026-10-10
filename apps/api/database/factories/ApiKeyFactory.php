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
        // `key` diisi plaintext; mutator model yang meng-hashnya dan
        // mengisi key_prefix secara otomatis saat disimpan.
        return [
            'website_id' => Website::factory(),
            'name' => fake()->word(),
            'key' => 'novacms_'.Str::random(40),
        ];
    }

    /**
     * State untuk mengambil plaintext kunci hasil generate.
     *
     * Factory mengembalikan model yang sudah disimpan, jadi plaintext
     * tidak bisa didapat dari ->key (sudah jadi hash). Pakai state ini
     * lalu baca $apiKey->plain_text_key untuk header Authorization:
     *
     * ```php
     * $apiKey = ApiKey::factory()->withPlainText()->create();
     * $this->withHeader('Authorization', "Bearer {$apiKey->plain_text_key}");
     * ```
     */
    public function withPlainText(): static
    {
        return $this->afterMaking(function (ApiKey $apiKey): void {
            // (Re)generate plaintext baru agar selalu tersedia untuk
            // dipakai sebagai Bearer token di test. Mutator `key` yang
            // meng-hashnya sekaligus mengisi property plain_text_key.
            $apiKey->key = 'novacms_'.Str::random(40);
        });
    }
}
