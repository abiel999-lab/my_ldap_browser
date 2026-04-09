<?php

namespace App\Filament\Resources\Ldap;

use App\Filament\Resources\Ldap\LdapUnitViewResource\Pages;
use App\Models\LdapUnitView;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LdapUnitViewResource extends Resource
{
    protected static ?string $model = LdapUnitView::class;

    protected static string | \UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Units Roles';
    protected static ?string $modelLabel = 'Unit';
    protected static ?string $pluralModelLabel = 'Units';
    protected static ?int $navigationSort = 22;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('cn')
                ->label('Unit CN')
                ->required(),

            Textarea::make('description')
                ->label('Description')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->emptyStateHeading('No units found')
            ->emptyStateDescription('OU units memang boleh kosong, tetapi fitur create/edit/delete tetap tersedia.')
            ->columns([
                TextColumn::make('cn')
                    ->label('Unit')
                    ->searchable()
                    ->sortable()
                    ->weight('600'),

                TextColumn::make('description')
                    ->label('Description')
                    ->default('-')
                    ->wrap(),

                TextColumn::make('synced_at')
                    ->label('Synced')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('deleteSelectedUnits')
                        ->label('Delete Selected Units')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $service = app(\App\Services\Ldap\LdapUnitService::class);
                            $bulk = app(\App\Services\Ldap\LdapBulkDeleteService::class);

                            $success = 0;
                            $failed = 0;

                            foreach ($records as $record) {
                                try {
                                    $service->delete($record->dn);
                                    $success++;

                                    $bulk->logSuccess(
                                        action: 'delete_unit_batch',
                                        targetUid: null,
                                        targetDn: $record->dn,
                                        beforeData: $record->toArray(),
                                        afterData: null,
                                        message: 'Unit deleted successfully via batch delete.'
                                    );
                                } catch (\Throwable $e) {
                                    $failed++;

                                    $bulk->logFailure(
                                        action: 'delete_unit_batch',
                                        targetUid: null,
                                        targetDn: $record->dn,
                                        beforeData: $record->toArray(),
                                        afterData: null,
                                        message: 'Batch delete unit failed.',
                                        errorMessage: $e->getMessage(),
                                    );
                                }
                            }

                            Notification::make()
                                ->title("Delete Units selesai. Success: {$success}, Failed: {$failed}")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('cn')
            ->recordUrl(fn ($record) => \App\Filament\Resources\Ldap\LdapUnitMemberViewResource::getUrl('index', [
                'unit' => $record->cn,
            ]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLdapUnitViews::route('/'),
        ];
    }
}
