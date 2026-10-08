<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * api-key:flush-usage — tulis kembali last_used_at dari cache ke DB.
 *
 * Middleware hanya menulis waktu pemakaian ke cache (sekali per jam)
 * agar tidak menghasilkan query UPDATE pada setiap request. Command
 * ini dijadwalkan setiap jam untuk memindahkannya ke database.
 */
class FlushApiKeyUsage extends Command
{
    protected $signature = 'api-key:flush-usage';

    protected $description = 'Pindahkan last_used_at kunci API dari cache ke database';

    public function handle(): int
    {
        $flushed = 0;

        foreach (ApiKey::query()->lazyById() as $apiKey) {
            $lastUsed = Cache::get("api_key.{$apiKey->id}.last_used");

            if ($lastUsed && $apiKey->last_used_at?->lt($lastUsed)) {
                $apiKey->update(['last_used_at' => $lastUsed]);
                Cache::forget("api_key.{$apiKey->id}.last_used");
                $flushed++;
            }
        }

        $this->info("{$flushed} kunci API diperbarui.");

        return self::SUCCESS;
    }
}
