<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages\ListLdapSchemaAttributeTypes;
use App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages\ListLdapSchemaBrowser;
use App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages\ListLdapSchemaMatchingRules;
use App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages\ListLdapSchemaMatchingRuleUse;
use App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages\ListLdapSchemaObjectClasses;
use App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages\ListLdapSchemaSyntaxes;
use App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages\ViewLdapSchemaEntry;
use App\Models\LdapSchemaEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class LdapSchemaBrowserResource extends Resource
{
    protected static ?string $model = LdapSchemaEntry::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';
    protected static string | UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static ?string $navigationLabel = 'Schema';
    protected static ?string $modelLabel = 'Schema Browser';
    protected static ?string $pluralModelLabel = 'Schema';
    protected static ?int $navigationSort = 40;

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
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('oid')
                    ->label('OID')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('description')
                    ->wrap()
                    ->limit(80)
                    ->searchable(),

                Tables\Columns\TextColumn::make('sup')
                    ->label('SUP')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->recordUrl(fn ($record) => static::getUrl('detail', ['recordKey' => $record->id]))
            ->actions([])
            ->bulkActions([])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLdapSchemaBrowser::route('/'),
            'object-classes' => ListLdapSchemaObjectClasses::route('/object-classes'),
            'attribute-types' => ListLdapSchemaAttributeTypes::route('/attribute-types'),
            'matching-rules' => ListLdapSchemaMatchingRules::route('/matching-rules'),
            'matching-rule-use' => ListLdapSchemaMatchingRuleUse::route('/matching-rule-use'),
            'syntaxes' => ListLdapSchemaSyntaxes::route('/syntaxes'),
            'detail' => ViewLdapSchemaEntry::route('/detail/{recordKey}'),
        ];
    }
}
