<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamResource extends Resource {
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string {
        return __('filament/resources/team.single');
    }

    public static function getPluralModelLabel(): string {
        return __('filament/resources/team.plural');
    }

    public static function getNavigationLabel(): string {
        return __('filament/resources/team.navigation_label');
    }

    public static function getNavigationGroup(): ?string {
        return __('filament/resources/team.navigation_group');
    }

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('filament/resources/team.fields.name'))
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('leader_id')
                ->label(__('filament/resources/team.fields.leader'))
                ->relationship('leader', 'id')
                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}")
                ->required(),
        ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('filament/resources/team.fields.name'))->searchable(),
                Tables\Columns\TextColumn::make('leader.first_name')
                    ->label(__('filament/resources/team.fields.leader'))
                    ->formatStateUsing(fn($record) => "{$record->leader->first_name} {$record->leader->last_name}")
                    ->searchable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('filament/resources/team.actions.edit')),
                Tables\Actions\DeleteAction::make()->label(__('filament/resources/team.actions.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label(__('filament/resources/team.actions.delete')),
                ]),
            ]);
    }

    public static function getRelations(): array {
        return [
                //
            ];
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
