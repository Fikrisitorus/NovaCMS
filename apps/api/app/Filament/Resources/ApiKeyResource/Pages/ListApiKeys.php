<?php

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * Daftar kunci API. Read-only terhadap kolom kunci (hanya potongan
 * awal yang tampil); aksi utama adalah mencabut kunci.
 */
class ListApiKeys extends ListRecords
{
    protected static string $resource = ApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat kunci API'),
        ];
    }
}
