<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiKeyResource\Pages;
use App\Models\ApiKey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * ApiKeyResource — kelola kunci API publik dari panel admin Filament.
 *
 * Kunci tidak pernah ditampilkan lagi setelah dibuat (hanya sekali di
 * halaman create) demi keamanan; kolom "key" di tabel hanya memperlihatkan
 * potongan depannya sebagai identifikasi.
 */
class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('website_id')
                    ->relationship('website', 'domain')
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->placeholder('mis. Production'),

                // Input tersembunyi: kunci di-generate server-side saat
                // create. Field ini tidak menerima input dari user.
                Forms\Components\Hidden::make('key')
                    ->default(fn () => 'novacms_'.Str::random(40)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('website.domain')
                    ->label('Website'),

                // Hanya tampilkan 12 karakter pertama + elipsis — kunci
                // penuh tidak boleh terbaca di UI.
                Tables\Columns\TextColumn::make('key')
                    ->label('Kunci')
                    ->formatStateUsing(fn (string $state): string => Str::limit($state, 16))
                    ->copyable()
                    ->copyMessage('Kunci disalin')
                    ->searchable(false),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->getStateUsing(fn (ApiKey $record): bool => $record->isActive()),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('Terakhir dipakai')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum pernah'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Dicabut')
                    ->queries(
                        true: fn ($query) => $query->whereNull('revoked_at'),
                        false: fn ($query) => $query->whereNotNull('revoked_at'),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('revoke')
                    ->label('Cabut')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cabut kunci API ini?')
                    ->modalDescription('Frontend yang memakai kunci ini akan langsung kehilangan akses.')
                    ->visible(fn (ApiKey $record): bool => $record->isActive())
                    ->action(fn (ApiKey $record) => $record->update(['revoked_at' => now()])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiKeys::route('/'),
            'create' => Pages\CreateApiKey::route('/create'),
        ];
    }

    public static function getLabel(): string
    {
        return 'Kunci API';
    }

    public static function getPluralLabel(): string
    {
        return 'Kunci API';
    }
}
