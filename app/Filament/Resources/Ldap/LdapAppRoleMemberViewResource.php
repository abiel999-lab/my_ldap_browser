<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapAppRoleMemberViewResource\Pages;
use App\Models\LdapUserView;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


class LdapAppRoleMemberViewResource extends Resource
{
    protected static ?string $model = LdapUserView::class;

    protected static string | \UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'App Role Members';
    protected static ?string $modelLabel = 'App Role Member';
    protected static ?string $pluralModelLabel = 'App Role Members';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('uid')
                    ->label('UID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cn')
                    ->label('Common Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mail')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('dn')
                    ->label('DN')
                    ->wrap(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLdapAppRoleMemberViews::route('/'),
        ];
    }
}
