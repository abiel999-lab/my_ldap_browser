<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapExportResource\Pages\CreateLdapExport;
use App\Filament\Resources\Ldap\LdapExportResource\Pages\ListLdapExports;
use App\Models\LdapExport;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class LdapExportResource extends Resource
{
    protected static ?string $model = LdapExport::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static string | UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static ?string $navigationLabel = 'Export LDIF ZIP';
    protected static ?string $modelLabel = 'Export LDIF ZIP';
    protected static ?string $pluralModelLabel = 'Export LDIF ZIP';
    protected static ?int $navigationSort = 51;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Title'),

            Select::make('scope')
                ->label('Scope')
                ->options([
                    'people' => 'People',
                    'groups' => 'Groups',
                    'roles' => 'Roles',
                    'custom' => 'Custom Base DN',
                ])
                ->default('people')
                ->required(),

            TextInput::make('base_dn')
                ->label('Custom Base DN')
                ->placeholder('ou=people,dc=petra,dc=ac,dc=id'),

            TextInput::make('filter')
                ->label('LDAP Filter')
                ->default('(objectClass=*)')
                ->required(),

            Textarea::make('notes')
                ->label('Notes'),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('scope')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('total_entries')
                    ->label('Entries'),

                Tables\Columns\TextColumn::make('zip_path')
                    ->label('ZIP Path')
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLdapExports::route('/'),
            'create' => CreateLdapExport::route('/create'),
        ];
    }
}
