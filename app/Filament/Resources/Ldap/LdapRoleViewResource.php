<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapRoleViewResource\Pages;
use App\Models\LdapRoleView;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class LdapRoleViewResource extends Resource
{
    protected static ?string $model = LdapRoleView::class;

    protected static string | \UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'User Roles';
    protected static ?string $modelLabel = 'User Role';
    protected static ?string $pluralModelLabel = 'User Roles';
    protected static ?int $navigationSort = 20;

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
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('deleteSelectedRoles')
                        ->label('Delete Selected Roles')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $service = app(\App\Services\Ldap\LdapUserRoleService::class);
                            $bulk = app(\App\Services\Ldap\LdapBulkDeleteService::class);

                            $success = 0;
                            $failed = 0;

                            foreach ($records as $record) {
                                try {
                                    $service->deleteByDn($record->dn);
                                    $success++;

                                    $bulk->logSuccess(
                                        action: 'delete_user_role_batch',
                                        targetUid: null,
                                        targetDn: $record->dn,
                                        beforeData: $record->toArray(),
                                        afterData: null,
                                        message: 'User role deleted successfully via batch delete.'
                                    );
                                } catch (\Throwable $e) {
                                    $failed++;

                                    $bulk->logFailure(
                                        action: 'delete_user_role_batch',
                                        targetUid: null,
                                        targetDn: $record->dn,
                                        beforeData: $record->toArray(),
                                        afterData: null,
                                        message: 'Batch delete user role failed.',
                                        errorMessage: $e->getMessage(),
                                    );
                                }
                            }

                            Notification::make()
                                ->title("Delete User Roles selesai. Success: {$success}, Failed: {$failed}")
                                ->success()
                                ->send();
                        }),
                ]),
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
