<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    // The model hashes the password via its 'hashed' cast, so
                    // this field must hand the plain text through unchanged.
                    ->dehydrateStateUsing(fn (?string $state): ?string => $state)
                    // Required when creating a user, optional when editing one:
                    // an empty submit on the edit page must not wipe the stored
                    // hash. Only persist the attribute when the admin actually
                    // typed a new value.
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->placeholder(fn (string $operation): string => $operation === 'edit' ? 'Leave blank to keep the current password' : '')
                    ->visibleOn(['create', 'edit']),

                // Replaces the raw DateTimePicker on email_verified_at, which
                // let an admin silently bypass email verification by picking a
                // date. Read-only status + an explicit confirmed action instead.
                Forms\Components\Placeholder::make('email_verified_at')
                    ->label('Email verified')
                    ->visibleOn(['edit'])
                    ->content(function (Model $record): string {
                        return $record->email_verified_at?->format('d M Y H:i') ?? 'Not verified';
                    }),
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('mark_email_verified')
                        ->label('Mark email as verified')
                        ->color('gray')
                        ->visibleOn(['edit'])
                        ->visible(fn (Model $record): bool => $record->email_verified_at === null)
                        ->requiresConfirmation()
                        ->action(function (Model $record, Forms\Set $set): void {
                            $record->forceFill(['email_verified_at' => now()])->save();
                            $set('email_verified_at', now()->format('Y-m-d\TH:i'));
                        }),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
