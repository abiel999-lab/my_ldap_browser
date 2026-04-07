<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapRoleViewResource\Pages;
use App\Models\LdapRoleView;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LdapRoleViewResource extends Resource
{
    protected static ?string $model = LdapRoleView::class;

    protected static string | \UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Roles';
    protected static ?string $modelLabel = 'Role';
    protected static ?string $pluralModelLabel = 'Roles';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('cn')
                ->label('Role')
                ->disabled(),

            TextInput::make('member_count')
                ->label('Members')
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('cn')
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
            ->defaultSort('cn')
            ->recordUrl(fn ($record) => \App\Filament\Resources\Ldap\LdapRoleMemberViewResource::getUrl('index', [
                'role' => $record->cn,
            ]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLdapRoleViews::route('/'),
        ];
    }
}
