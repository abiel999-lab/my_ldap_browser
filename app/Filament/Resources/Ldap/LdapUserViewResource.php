<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapUserViewResource\Pages;
use App\Models\LdapUserView;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LdapUserViewResource extends Resource
{
    protected static ?string $model = LdapUserView::class;

    protected static string | \UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('uid')
                ->label('UID')
                ->required()
                ->disabledOn('edit')
                ->dehydrated(fn (string $operation): bool => $operation === 'create'),

            TextInput::make('cn')
                ->label('Common Name')
                ->required(),

            TextInput::make('display_name')
                ->label('Display Name'),

            TextInput::make('given_name')
                ->label('Given Name'),

            TextInput::make('sn')
                ->label('Surname')
                ->required(),

            TextInput::make('mail')
                ->label('Email')
                ->email(),

            TextInput::make('employee_number')
                ->label('Employee Number'),

            TextInput::make('user_nik')
                ->label('User NIK'),

            TextInput::make('petra_account_status')
                ->label('Petra Account Status'),

            TextInput::make('student_number')
                ->label('Student Number'),

            Textarea::make('mail_alternate_address_text')
                ->label('Mail Alternate Address')
                ->rows(4)
                ->helperText('Satu baris satu value.')
                ->formatStateUsing(function ($record) {
                    $values = is_array($record?->mail_alternate_address) ? $record->mail_alternate_address : [];
                    return implode("\n", $values);
                }),

            Textarea::make('student_number_history_text')
                ->label('Student Number History')
                ->rows(4)
                ->helperText('Satu baris satu value.')
                ->formatStateUsing(function ($record) {
                    $values = is_array($record?->student_number_history) ? $record->student_number_history : [];
                    return implode("\n", $values);
                }),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->helperText(fn (string $operation): string => $operation === 'create'
                    ? 'Password wajib diisi saat create user.'
                    : 'Kosongkan jika tidak ingin mengubah password.'),
        ]);
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
                    ->sortable()
                    ->weight('600'),

                TextColumn::make('display_name')
                    ->label('Display Name')
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                TextColumn::make('given_name')
                    ->label('Given Name')
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                TextColumn::make('mail')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                TextColumn::make('roles')
                    ->label('Roles')
                    ->state(function ($record) {
                        $roles = is_array($record->roles) ? $record->roles : [];
                        return empty($roles) ? 'unknown' : implode(', ', $roles);
                    })
                    ->searchable()
                    ->wrap(),

                TextColumn::make('synced_at')
                    ->label('Synced')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('cn')
            ->recordUrl(fn ($record) => static::getUrl('edit', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLdapUserViews::route('/'),
            'create' => Pages\CreateLdapUserView::route('/create'),
            'edit' => Pages\EditLdapUserView::route('/{record}/edit'),
        ];
    }
}
