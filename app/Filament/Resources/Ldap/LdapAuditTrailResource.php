<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapAuditTrailResource\Pages;
use App\Models\LdapAuditTrail;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LdapAuditTrailResource extends Resource
{
    protected static ?string $model = LdapAuditTrail::class;

    protected static string | \UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Audit Logs';
    protected static ?string $modelLabel = 'Audit Log';
    protected static ?string $pluralModelLabel = 'Audit Logs';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('action')->disabled(),
            TextInput::make('target_uid')->disabled(),
            TextInput::make('status')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),

                TextColumn::make('actor_name')
                    ->label('Actor')
                    ->searchable(),

                TextColumn::make('actor_email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('action')
                    ->label('Action')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('target_uid')
                    ->label('Target UID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('ldap_status')
                    ->label('LDAP')
                    ->badge()
                    ->sortable()
                    ->default('-'),

                TextColumn::make('sync_status')
                    ->label('Sync')
                    ->badge()
                    ->sortable()
                    ->default('-'),

                TextColumn::make('message')
                    ->label('Message')
                    ->wrap(),

                TextColumn::make('error_message')
                    ->label('Error')
                    ->wrap()
                    ->default('-'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLdapAuditTrails::route('/'),
        ];
    }
}
