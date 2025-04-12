<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'Пользователь';

    protected static ?string $pluralModelLabel = 'Пользователи';

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')->label('Имя')->searchable(),
                TextColumn::make('last_name')->label('Фамилия')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('position')->label('Должность')->searchable(),
                TextColumn::make('team.name')->label('Команда')->searchable(),
                TextColumn::make('manager.full_name')->label('Руководитель')->searchable(),
                TextColumn::make('role.name')->label('Роль')->searchable(),
                IconColumn::make('is_reviewer')->label('Ревьювер')->boolean()->sortable(),
                IconColumn::make('has_telegram')
                    ->label('Telegram')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (bool) $record->telegram_id)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->label('Роль')->relationship('role', 'name'),
                SelectFilter::make('team')->label('Команда')->relationship('team', 'name'),
                TernaryFilter::make('is_reviewer')
                    ->label('Ревьювер')
                    ->boolean()
                    ->trueLabel('Да')
                    ->falseLabel('Нет'),
                TernaryFilter::make('has_telegram')
                    ->label('Telegram')
                    ->boolean()
                    ->trueLabel('Подключен')
                    ->falseLabel('Не подключен'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Основная информация')->schema([
                TextInput::make('first_name')->label('Имя')->required()->maxLength(255),
                TextInput::make('last_name')->label('Фамилия')->required()->maxLength(255),
                TextInput::make('email')->label('Email')->email()->required()->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->required(fn ($record) => ! $record)
                    ->minLength(8)
                    ->label('Пароль'),
            ]),

            Section::make('Должность и команда')->schema([
                TextInput::make('position')->label('Должность')->maxLength(255),
                Select::make('team_id')
                    ->relationship('team', 'name')
                    ->label('Команда')
                    ->searchable()
                    ->preload(),
                Select::make('manager_id')
                    ->relationship('manager', 'first_name')
                    ->label('Руководитель')
                    ->searchable()
                    ->preload(),
                Select::make('role_id')
                    ->relationship('role', 'name')
                    ->label('Роль')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]),

            Section::make('Дополнительная информация')->schema([
                Toggle::make('is_reviewer')
                    ->label('Ревьювер')
                    ->helperText('Пользователь может проводить код-ревью'),
                Placeholder::make('has_telegram')
                    ->label('Telegram')
                    ->content(fn ($record) => $record?->telegram_id ? 'Подключен' : 'Не подключен'),
            ]),
        ]);
    }
}
