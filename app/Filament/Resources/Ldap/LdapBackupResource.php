<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapBackupResource\Pages\CreateLdapBackup;
use App\Filament\Resources\Ldap\LdapBackupResource\Pages\ListLdapBackups;
use App\Models\LdapBackup;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class LdapBackupResource extends Resource
{
    protected static ?string $model = LdapBackup::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cloud-arrow-down';
    protected static string | UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static ?string $navigationLabel = 'Backup LDAP';
    protected static ?string $modelLabel = 'Backup LDAP';
    protected static ?string $pluralModelLabel = 'Backup LDAP';
    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Title'),

            Select::make('scope')
                ->label('Scope')
                ->options([
                    'full' => 'Full LDAP',
                    'custom' => 'Custom Base DN',
                ])
                ->default('full')
                ->required(),

            TextInput::make('base_dn')
                ->label('Custom Base DN')
                ->placeholder('ou=people,dc=petra,dc=ac,dc=id'),

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
            'index' => ListLdapBackups::route('/'),
            'create' => CreateLdapBackup::route('/create'),
        ];
    }
}
