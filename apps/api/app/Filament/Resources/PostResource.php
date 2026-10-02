<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;


class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Blog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\Section::make('Post Settings')
                        ->schema([
                            Forms\Components\Select::make('website_id')
                                ->relationship('website', 'name')
                                ->required(),
                            Forms\Components\Select::make('author_id')
                                ->relationship('author', 'name')
                                ->default(auth()->id())
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
                            Forms\Components\Select::make('categories')
                                ->relationship('categories', 'name')
                                ->multiple()
                                ->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                    Forms\Components\TextInput::make('slug')->required(),
                                ]),
                            Forms\Components\Toggle::make('is_published')
                                ->default(false),
                            Forms\Components\DateTimePicker::make('published_at'),
                        ]),

                    Forms\Components\Section::make('Featured Image')
                        ->schema([
                            Forms\Components\FileUpload::make('featured_image')
                                ->image()
                                ->directory('posts'),
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
                            Forms\Components\Textarea::make('excerpt')
                                ->rows(3),
                            Forms\Components\RichEditor::make('content')
                                ->columnSpanFull(),
                        ]),
                ])->columnSpan(['lg' => 2]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('website.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('categories.name')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('website_id')
                    ->relationship('website', 'name')
                    ->label('Website'),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
