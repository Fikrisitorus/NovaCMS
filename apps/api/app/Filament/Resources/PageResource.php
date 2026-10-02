<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;


class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $slug = 'site-pages';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\Section::make('Page Settings')
                        ->schema([
                            Forms\Components\Select::make('website_id')
                                ->relationship('website', 'name')
                                ->required(),
                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                            Forms\Components\TextInput::make('slug')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            Forms\Components\Toggle::make('is_published')
                                ->default(false),
                        ]),

                    Forms\Components\Section::make('SEO')
                        ->schema([
                            Forms\Components\TextInput::make('seoMeta.meta_title')
                                ->label('Meta Title')
                                ->maxLength(60),
                            Forms\Components\Textarea::make('seoMeta.meta_description')
                                ->label('Meta Description')
                                ->maxLength(160),
                            Forms\Components\TagsInput::make('seoMeta.meta_keywords')
                                ->label('Meta Keywords'),
                            Forms\Components\FileUpload::make('seoMeta.og_image')
                                ->label('OG Image')
                                ->image()
                                ->directory('seo'),
                            Forms\Components\TextInput::make('seoMeta.canonical_url')
                                ->label('Canonical URL')
                                ->url(),
                        ])
                        ->collapsed(),
                ])->columnSpan(['lg' => 1]),

                Forms\Components\Group::make([
                    Forms\Components\Section::make('Content')
                        ->schema([
                            Forms\Components\Builder::make('blocks')
                                ->blocks([
                                    Forms\Components\Builder\Block::make('hero')
                                        ->label('Hero Section')
                                        ->icon('heroicon-m-sparkles')
                                        ->schema([
                                            Forms\Components\TextInput::make('heading')->required(),
                                            Forms\Components\Textarea::make('subheading'),
                                            Forms\Components\TextInput::make('button_label'),
                                            Forms\Components\TextInput::make('button_url')->url(),
                                            Forms\Components\FileUpload::make('background_image')->image()->directory('hero'),
                                        ]),
                                    Forms\Components\Builder\Block::make('faq')
                                        ->label('FAQ Section')
                                        ->icon('heroicon-m-question-mark-circle')
                                        ->schema([
                                            Forms\Components\Repeater::make('questions')
                                                ->schema([
                                                    Forms\Components\TextInput::make('question')->required(),
                                                    Forms\Components\Textarea::make('answer')->required(),
                                                ]),
                                        ]),
                                    Forms\Components\Builder\Block::make('gallery')
                                        ->label('Image Gallery')
                                        ->icon('heroicon-m-photo')
                                        ->schema([
                                            Forms\Components\FileUpload::make('images')
                                                ->image()
                                                ->multiple()
                                                ->directory('gallery'),
                                        ]),
                                    Forms\Components\Builder\Block::make('pricing')
                                        ->label('Pricing Section')
                                        ->icon('heroicon-m-currency-dollar')
                                        ->schema([
                                            Forms\Components\Repeater::make('plans')
                                                ->schema([
                                                    Forms\Components\TextInput::make('name')->required(),
                                                    Forms\Components\TextInput::make('price')->numeric()->required(),
                                                    Forms\Components\TagsInput::make('features'),
                                                ]),
                                        ]),
                                    Forms\Components\Builder\Block::make('contact')
                                        ->label('Contact Form')
                                        ->icon('heroicon-m-envelope')
                                        ->schema([
                                            Forms\Components\TextInput::make('recipient_email')->email()->required(),
                                            Forms\Components\Textarea::make('description'),
                                        ]),
                                ])
                                ->collapsible(),
                        ]),
                ])->columnSpan(['lg' => 2]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('website.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
