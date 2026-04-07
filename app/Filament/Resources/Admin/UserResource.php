<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Clusters\Admin;
use App\Filament\Resources\Admin\UserResource\Pages\ListUserUnits;
use App\Models\Gate\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $cluster = Admin::class;

    public static function getNavigationLabel(): string
    {
        return __('User Unit'); // Label for navigation
    }

    public static function getModelLabel(): string
    {
        return __('User Unit'); // Label tunggal
    }

    public static function getPluralModelLabel(): string
    {
        return __('User Unit'); // Label jamak
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserUnits::route('/'),
        ];
    }
}
