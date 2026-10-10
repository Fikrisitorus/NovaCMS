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
 *
 * Kunci disimpan sebagai hash di kolom `key`; plaintext hanya ditampilkan
 * sekali di output command ini dan tidak bisa dipulihkan lagi.
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

        // Plaintext di-generate di sini lalu langsung di-hash oleh model
        // — plaintext hanya ditampilkan sekali di bawah.
        $plaintext = 'novacms_'.Str::random(40);

        $apiKey = ApiKey::create([
            'website_id' => $website->id,
            'name' => $this->argument('name') ?? 'Default',
            'key' => $plaintext,
            'key_prefix' => substr($plaintext, 0, 8),
        ]);

        $this->info('Kunci API berhasil dibuat.');
        $this->table(
            ['ID', 'Website', 'Nama', 'Kunci'],
            [[$apiKey->id, $website->domain, $apiKey->name, $plaintext]]
        );
        $this->warn('Simpan kunci ini sekarang — tidak akan ditampilkan lagi.');

        return self::SUCCESS;
    }
}
