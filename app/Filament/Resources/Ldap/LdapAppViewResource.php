<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapAppViewResource\Pages;
use App\Models\LdapAppView;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class LdapAppViewResource extends Resource
{
    protected static ?string $model = LdapAppView::class;

    protected static string | \UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-window';
    protected static ?string $navigationLabel = 'App Roles';
    protected static ?string $modelLabel = 'App';
    protected static ?string $pluralModelLabel = 'App Roles';
    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('cn')->disabled(),
            TextInput::make('description')->disabled(),
            TextInput::make('role_count')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('cn')
                    ->label('App')
                    ->searchable()
                    ->sortable()
                    ->weight('600'),

                TextColumn::make('description')
                    ->label('Description')
                    ->default('-')
                    ->wrap(),

                TextColumn::make('role_count')
                    ->label('Roles')
                    ->sortable(),

                TextColumn::make('synced_at')
                    ->label('Synced')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('deleteSelectedApps')
                        ->label('Delete Selected Apps')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $service = app(\App\Services\Ldap\LdapAppRoleService::class);
                            $bulk = app(\App\Services\Ldap\LdapBulkDeleteService::class);

                            $success = 0;
                            $failed = 0;

                            foreach ($records as $record) {
                                try {
                                    $service->deleteApp($record->dn);
                                    $success++;

                                    $bulk->logSuccess(
                                        action: 'delete_app_batch',
                                        targetUid: null,
                                        targetDn: $record->dn,
                                        beforeData: $record->toArray(),
                                        afterData: null,
                                        message: 'App deleted successfully via batch delete.'
                                    );
                                } catch (\Throwable $e) {
                                    $failed++;

                                    $bulk->logFailure(
                                        action: 'delete_app_batch',
                                        targetUid: null,
                                        targetDn: $record->dn,
                                        beforeData: $record->toArray(),
                                        afterData: null,
                                        message: 'Batch delete app failed.',
                                        errorMessage: $e->getMessage(),
                                    );
                                }
                            }

                            Notification::make()
                                ->title("Delete App Roles selesai. Success: {$success}, Failed: {$failed}")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('cn')
            ->recordUrl(fn ($record) => \App\Filament\Resources\Ldap\LdapAppViewResource\Pages\ListLdapAppViews::getUrl([
                'app' => $record->cn,
            ]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLdapAppViews::route('/'),
        ];
    }
}
