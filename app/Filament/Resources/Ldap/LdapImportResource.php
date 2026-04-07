<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapImportResource\Pages\CreateLdapImport;
use App\Filament\Resources\Ldap\LdapImportResource\Pages\ListLdapImports;
use App\Models\LdapImport;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class LdapImportResource extends Resource
{
    protected static ?string $model = LdapImport::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static string | UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static ?string $navigationLabel = 'Import CSV/Excel';
    protected static ?string $modelLabel = 'Import CSV/Excel';
    protected static ?string $pluralModelLabel = 'Import CSV/Excel';
    protected static ?int $navigationSort = 52;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Title'),

            FileUpload::make('file_path')
                ->label('CSV/XLSX File')
                ->disk('local')
                ->directory('ldap-imports')
                ->required(),

            Select::make('mode')
                ->label('Mode')
                ->options([
                    'create_only' => 'Create Only',
                    'upsert' => 'Create or Update',
                ])
                ->default('upsert')
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

                Tables\Columns\TextColumn::make('original_name')
                    ->label('File'),

                Tables\Columns\TextColumn::make('mode')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('total_rows')
                    ->label('Total'),

                Tables\Columns\TextColumn::make('success_rows')
                    ->label('Success'),

                Tables\Columns\TextColumn::make('failed_rows')
                    ->label('Failed'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLdapImports::route('/'),
            'create' => CreateLdapImport::route('/create'),
        ];
    }
}
