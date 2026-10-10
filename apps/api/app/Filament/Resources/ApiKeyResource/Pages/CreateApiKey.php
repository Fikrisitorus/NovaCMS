<?php

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Halaman buat kunci API. Setelah sukses, tampilkan kunci penuh sekali
 * saja melalui notifikasi — tidak pernah disimpan di tampilan daftar.
 *
 * Form mengisi `key` dengan plaintext hasil generate; mutator model
 * langsung meng-hashnya sebelum disimpan, sehingga notifikasi di bawah
 * tetap menampilkan plaintext asli yang harus disalin user.
 */
class CreateApiKey extends CreateRecord
{
    protected static string $resource = ApiKeyResource::class;

    protected function afterCreate(): void
    {
        $this->notify(
            'success',
            'Kunci API: '.$this->record->plain_text_key.' — salin sekarang, tidak akan ditampilkan lagi.',
            durationMs: 15000,
        );
    }
}
