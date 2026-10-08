<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Command;

/**
 * api-key:revoke — cabut kunci API tanpa menghapus barisnya.
 *
 * Contoh: php artisan api-key:revoke novacms_abc123
 */
class RevokeApiKey extends Command
{
    protected $signature = 'api-key:revoke {key : Kunci API yang akan dicabut}';

    protected $description = 'Cabut (revoke) kunci API';

    public function handle(): int
    {
        $apiKey = ApiKey::where('key', $this->argument('key'))->first();

        if (! $apiKey) {
            $this->error('Kunci API tidak ditemukan.');

            return self::FAILURE;
        }

        $apiKey->update(['revoked_at' => now()]);

        $this->info("Kunci '{$apiKey->name}' berhasil dicabut pada {$apiKey->revoked_at}.");

        return self::SUCCESS;
    }
}
