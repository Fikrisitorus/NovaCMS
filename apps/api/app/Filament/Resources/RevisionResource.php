<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RevisionResource\Pages;
use App\Models\Revision;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * RevisionResource: halaman read-only version history untuk Page & Post.
 *
 * Tidak ada halaman create/edit karena revision adalah audit log — record
 * hanya dibuat otomatis oleh trait HasRevisions setiap kali konten induk
 * diupdate. Admin bisa melihat daftar revisi per record dan memulihkan
 * (restore) konten ke versi tertentu.
 */
class RevisionResource extends Resource
{
    protected static ?string $model = Revision::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock-rewind';

    protected static ?string $slug = 'content-revisions';

    protected static ?int $navigationSort = 90;

    /**
     * Read-only: form hanya dipakai untuk menampilkan snapshot JSON dalam
     * format pretty-print, tidak untuk menyimpan perubahan.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('snapshot')
                    ->label('Snapshot Konten')
                    ->formatStateUsing(fn (Revision $record): string => $record->formattedContent())
                    ->rows(20)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('revisable_label')
                    ->label('Tipe Konten')
                    ->badge()
                    ->colors([
                        'info' => 'Page',
                        'success' => 'Post',
                    ]),
                Tables\Columns\TextColumn::make('revisable.title')
                    ->label('Judul Konten')
                    ->url(fn (Revision $record): ?string => $record->revisable?->id
                        ? static::getParentUrl($record)
                        : null)
                    ->searchable()
                    ->placeholder('Konten sudah dihapus'),
                Tables\Columns\TextColumn::make('summary')
                    ->label('Pesan Commit')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Diubah Oleh')
                    ->searchable()
                    ->placeholder('Sistem'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('revisable_type')
                    ->label('Tipe Konten')
                    ->options([
                        'App\Models\Page' => 'Page',
                        'App\Models\Post' => 'Post',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Pulihkan Konten ke Versi Ini')
                    ->modalDescription(fn (Revision $record): string => sprintf(
                        'Konten %s "%s" akan diganti dengan snapshot pada %s. '.
                        'Kondisi konten saat ini otomatis disimpan sebagai revision baru.',
                        $record->revisableLabel(),
                        $record->revisable?->title ?? '(dihapus)',
                        $record->created_at?->format('d M Y H:i') ?? '-'
                    ))
                    ->form([
                        Textarea::make('summary')
                            ->label('Pesan Commit (opsional)')
                            ->placeholder('Contoh: rollback ke versi sebelum banner diganti')
                            ->maxLength(255),
                    ])
                    ->visible(fn (Revision $record): bool => filled($record->revisable))
                    ->action(function (Revision $record, array $data): void {
                        $record->restore($data['summary'] ?? null);
                    })
                    ->successNotificationTitle('Konten berhasil dipulihkan ke versi terpilih'),
            ])
            ->bulkActions([]);
    }

    /**
     * URL ke halaman edit record induk (Page/Post) di Filament panel.
     */
    protected static function getParentUrl(Revision $record): ?string
    {
        $revisable = $record->revisable;

        if (blank($revisable?->id)) {
            return null;
        }

        $resource = match ($record->revisable_type) {
            'App\Models\Page' => 'site-pages',
            'App\Models\Post' => 'posts',
            default => null,
        };

        return filled($resource)
            ? "/admin/{$resource}/{$revisable->id}/edit"
            : null;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRevisions::route('/'),
            'view' => Pages\ViewRevision::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(mixed $record): bool
    {
        return false;
    }

    public static function canDelete(mixed $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return 'Version History';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Konten';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Revision';
    }

    public static function getModelLabel(): string
    {
        return 'Revision';
    }
}
