<?php

namespace App\Filament\Resources\RevisionResource\Pages;

use App\Filament\Resources\RevisionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewRevision extends ViewRecord
{
    protected static string $resource = RevisionResource::class;

    protected function getHeaderActions(): array
    {
        // Snapshot adalah audit log: tidak bisa diedit atau dihapus,
        // satu-satunya aksi adalah memulihkan konten ke versi ini.
        return [
            Action::make('restore')
                ->label('Restore ke Versi Ini')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Pulihkan Konten ke Versi Ini')
                ->modalDescription(fn () => 'Konten induk akan diganti dengan snapshot ini. '.
                    'Kondisi konten saat ini otomatis disimpan sebagai revision baru.')
                ->visible(fn (): bool => filled($this->record->revisable))
                ->action(fn () => $this->record->restore())
                ->successNotificationTitle('Konten berhasil dipulihkan ke versi terpilih'),
        ];
    }
}
