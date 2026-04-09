<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapAppRoleViewResource\Pages;
use App\Models\LdapAppRoleView;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


class LdapAppRoleViewResource extends Resource
{
    protected static ?string $model = LdapAppRoleView::class;

    protected static string | \UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'App Role Members';
    protected static ?string $modelLabel = 'App Role';
    protected static ?string $pluralModelLabel = 'App Roles';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('app_cn')
                    ->label('App')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role_cn')
                    ->label('Role')
                    ->searchable()
                    ->sortable()
                    ->weight('600'),

                TextColumn::make('member_count')
                    ->label('Members')
                    ->sortable(),

                TextColumn::make('synced_at')
                    ->label('Synced')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('role_cn');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLdapAppRoleViews::route('/'),
        ];
    }
}
