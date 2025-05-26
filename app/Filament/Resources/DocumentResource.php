<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Services\DocumentsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\MarkdownEditor;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Документы';

    protected static ?string $navigationLabel = 'Документы';

    protected static ?string $modelLabel = 'Документ';

    protected static ?string $pluralModelLabel = 'Документы';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, Forms\Set $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('parent_category')
                            ->label('Родительская категория')
                            ->options(DocumentCategory::whereNull('parent_id')->pluck('name', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('subcategory_id', null);
                                $set('category_id', fn ($state) => $state);
                            })
                            ->searchable(),

                        Forms\Components\Select::make('subcategory_id')
                            ->label('Подкатегория')
                            ->options(function (callable $get) {
                                $parentId = $get('parent_category');
                                if (!$parentId) {
                                    return [];
                                }

                                return DocumentCategory::where('parent_id', $parentId)
                                    ->pluck('name', 'id');
                            })
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $subcategory = DocumentCategory::find($state);
                                    if ($subcategory) {
                                        $set('category_id', $subcategory->parent_id);
                                    }
                                }
                            })
                            ->reactive()
                            ->searchable(),

                        Forms\Components\Hidden::make('category_id'),

                        MarkdownEditor::make('content')
                            ->label('Содержание')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->description(function (Document $record): ?string {
                        if ($record->subcategory) {
                            return "Подкатегория: " . $record->subcategory->name;
                        }

                        return null;
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subcategory.name')
                    ->label('Подкатегория')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('subcategory_id')
                    ->label('Подкатегория')
                    ->relationship('subcategory', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->using(function (Document $record) {
                        app(DocumentsService::class)->delete($record);
                        return true;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->using(function (array $records) {
                            $service = app(DocumentsService::class);
                            foreach ($records as $record) {
                                $service->delete($record);
                            }
                            return true;
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
