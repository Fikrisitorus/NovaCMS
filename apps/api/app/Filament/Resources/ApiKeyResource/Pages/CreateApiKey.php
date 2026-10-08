<?php

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Halaman buat kunci API. Setelah sukses, tampilkan kunci penuh sekali
 * saja melalui notifikasi — tidak pernah disimpan di tampilan daftar.
 */
class CreateApiKey extends CreateRecord
{
    protected static string $resource = ApiKeyResource::class;

    protected function afterCreate(): void
    {
        $this->notify(
            'success',
            'Kunci API: '.$this->record->key.' — salin sekarang, tidak akan ditampilkan lagi.',
            durationMs: 15000,
        );
    }
}
