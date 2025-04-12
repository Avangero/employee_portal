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
                Forms\Components\Select::make('team_id')
                    ->label('Команда')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('manager_id')
                    ->label('Руководитель')
                    ->relationship(
                        'manager',
                        'first_name',
                        fn(Builder $query) => $query->whereHas('role', fn($q) => $q->where('slug', 'manager')),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn($record) => $record ? "{$record->first_name} {$record->last_name}" : '',
                    )
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('role_id')
                    ->label('Роль')
                    ->relationship('role', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]),
            Forms\Components\Section::make('Дополнительная информация')->schema([
                Forms\Components\Toggle::make('is_reviewer')
                    ->label('Ревьювер')
                    ->helperText('Пользователь будет получать уведомления о новых Pull Request-ах в своей команде')
                    ->default(false),
                Forms\Components\Placeholder::make('has_telegram')
                    ->label('Telegram для Pull Request-ов')
                    ->content(fn(?User $record) => $record?->has_telegram ? '✅ Подключен' : '❌ Не подключен')
                    ->helperText('Статус подключения Telegram бота. Пользователь должен авторизоваться через бота.'),
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
                Tables\Columns\IconColumn::make('is_reviewer')->label('Ревьювер')->boolean()->sortable(),
                Tables\Columns\IconColumn::make('has_telegram')
                    ->label('Telegram')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip('Статус подключения Telegram бота')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')->label('Роль')->relationship('role', 'name'),
                Tables\Filters\SelectFilter::make('team')->label('Команда')->relationship('team', 'name'),
                Tables\Filters\TernaryFilter::make('is_reviewer')
                    ->label('Ревьювер')
                    ->boolean()
                    ->trueLabel('Да')
                    ->falseLabel('Нет')
                    ->placeholder('Все'),
                Tables\Filters\TernaryFilter::make('has_telegram')
                    ->label('Telegram подключен')
                    ->boolean()
                    ->trueLabel('Да')
                    ->falseLabel('Нет')
                    ->placeholder('Все'),
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
