<?php

namespace App\Filament\Resources\RevisionResource\Pages;

use App\Filament\Resources\RevisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRevisions extends ListRecords
{
    protected static string $resource = RevisionResource::class;

    protected function getHeaderActions(): array
    {
        // Tidak ada tombol Create: revision hanya dibuat otomatis oleh
        // trait HasRevisions saat konten Page/Post diupdate.
        return [
            Actions\Action::make('info')
                ->label('Revision dibuat otomatis')
                ->icon('heroicon-o-information-circle')
                ->color('gray')
                ->disabled()
                ->tooltip('Setiap perubahan konten Page/Post otomatis tersimpan di sini'),
        ];
    }
}
