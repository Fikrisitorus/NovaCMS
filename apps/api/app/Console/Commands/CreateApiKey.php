<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * api-key:create — buat kunci API baru untuk sebuah website.
 *
 * Contoh: php artisan api-key:create {website-domain} "Production"
 */
class CreateApiKey extends Command
{
    protected $signature = 'api-key:create
        {website : Domain website, mis. demo.novacms.local}
        {name? : Nama kunci, mis. "Production"}';

    protected $description = 'Buat kunci API baru untuk sebuah website';

    public function handle(): int
    {
        $website = Website::where('domain', $this->argument('website'))->first();

        if (! $website) {
            $this->error("Website dengan domain '{$this->argument('website')}' tidak ditemukan.");

            return self::FAILURE;
        }

        $key = 'novacms_'.Str::random(40);

        $apiKey = ApiKey::create([
            'website_id' => $website->id,
            'name' => $this->argument('name') ?? 'Default',
            'key' => $key,
        ]);

        $this->info('Kunci API berhasil dibuat.');
        $this->table(
            ['ID', 'Website', 'Nama', 'Kunci'],
            [[$apiKey->id, $website->domain, $apiKey->name, $key]]
        );
        $this->warn('Simpan kunci ini sekarang — tidak akan ditampilkan lagi.');

        return self::SUCCESS;
    }
}
