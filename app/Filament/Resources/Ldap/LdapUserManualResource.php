<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapUserManualResource\Pages\ListLdapUserManuals;
use App\Models\LdapManualEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LdapUserManualResource extends Resource
{
    protected static ?string $model = LdapManualEntry::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-book-open';
    protected static string | UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static ?string $navigationLabel = 'User Manual';
    protected static ?string $modelLabel = 'User Manual';
    protected static ?string $pluralModelLabel = 'User Manual';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLdapUserManuals::route('/'),
        ];
    }
}
