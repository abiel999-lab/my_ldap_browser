<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapRoleMemberViewResource\Pages;
use App\Models\LdapRoleMemberView;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LdapRoleMemberViewResource extends Resource
{
    protected static ?string $model = LdapRoleMemberView::class;

    protected static string | \UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Role Members';
    protected static ?string $modelLabel = 'Role Member';
    protected static ?string $pluralModelLabel = 'Role Members';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('role_cn')->label('Role')->disabled(),
            TextInput::make('uid')->label('UID')->disabled(),
            TextInput::make('member_dn')->label('DN')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('role_cn')
                    ->label('Role')
                    ->searchable()
                    ->sortable()
                    ->weight('600'),

                TextColumn::make('uid')
                    ->label('UID')
                    ->searchable()
                    ->sortable()
                    ->default('unknown'),

                TextColumn::make('member_dn')
                    ->label('DN')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('synced_at')
                    ->label('Synced')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('uid');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLdapRoleMemberViews::route('/'),
        ];
    }
}
