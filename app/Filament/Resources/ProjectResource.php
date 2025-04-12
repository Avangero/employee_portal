<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource {
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Проект';

    protected static ?string $pluralModelLabel = 'Проекты';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Информация о проекте')->schema([
                Forms\Components\TextInput::make('name')->label('Название')->required()->maxLength(255),
                Forms\Components\Textarea::make('description')->label('Описание')->maxLength(65535)->columnSpanFull(),
                Forms\Components\Select::make('team_id')->label('Команда')->relationship('team', 'name')->searchable(),
                Forms\Components\Select::make('leader_id')
                    ->label('Руководитель')
                    ->relationship(
                        'leader',
                        'first_name',
                        fn(Builder $query) => $query->whereHas('role', fn($q) => $q->where('slug', 'manager')),
                    )
                    ->getOptionLabelFromRecordUsing(fn(User $record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(),
            ]),
            Forms\Components\Section::make('Участники')->schema([
                Forms\Components\Select::make('members')
                    ->label('Участники проекта')
                    ->relationship('members', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn(User $record) => "{$record->first_name} {$record->last_name}")
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Название')->searchable(),
                Tables\Columns\TextColumn::make('description')->label('Описание')->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('team.name')->label('Команда')->searchable(),
                Tables\Columns\TextColumn::make('leader.full_name')->label('Руководитель')->searchable(),
                Tables\Columns\TextColumn::make('members_count')->label('Количество участников')->counts('members'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('team')->label('Команда')->relationship('team', 'name'),
                Tables\Filters\SelectFilter::make('leader')
                    ->label('Руководитель')
                    ->relationship('leader', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn(User $record) => "{$record->first_name} {$record->last_name}"),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array {
        return [
                //
            ];
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
