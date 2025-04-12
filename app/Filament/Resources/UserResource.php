<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource {
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'Пользователь';

    protected static ?string $pluralModelLabel = 'Пользователи';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Основная информация')->schema([
                Forms\Components\TextInput::make('first_name')->label('Имя')->required()->maxLength(255),
                Forms\Components\TextInput::make('last_name')->label('Фамилия')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->label('Email')->email()->required()->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),
            ]),
            Forms\Components\Section::make('Должность и команда')->schema([
                Forms\Components\TextInput::make('position')->label('Должность')->maxLength(255),
                Forms\Components\Select::make('team_id')->label('Команда')->relationship('team', 'name'),
                Forms\Components\Select::make('manager_id')
                    ->label('Руководитель')
                    ->relationship(
                        'manager',
                        'first_name',
                        fn(Builder $query) => $query->whereHas('role', fn($q) => $q->where('slug', 'manager')),
                    )
                    ->getOptionLabelFromRecordUsing(fn(User $record) => "{$record->first_name} {$record->last_name}"),
                Forms\Components\Select::make('role_id')->label('Роль')->relationship('role', 'name')->required(),
            ]),
            Forms\Components\Section::make('Проекты')->schema([
                Forms\Components\Select::make('projects')
                    ->label('Проекты')
                    ->relationship('projects', 'name')
                    ->multiple()
                    ->preload(),
            ]),
        ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label('Имя')->searchable(),
                Tables\Columns\TextColumn::make('last_name')->label('Фамилия')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('position')->label('Должность')->searchable(),
                Tables\Columns\TextColumn::make('team.name')->label('Команда')->searchable(),
                Tables\Columns\TextColumn::make('manager.full_name')->label('Руководитель')->searchable(),
                Tables\Columns\TextColumn::make('role.name')->label('Роль')->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')->label('Роль')->relationship('role', 'name'),
                Tables\Filters\SelectFilter::make('team')->label('Команда')->relationship('team', 'name'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
