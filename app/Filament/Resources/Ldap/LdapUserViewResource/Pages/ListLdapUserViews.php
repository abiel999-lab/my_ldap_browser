<?php

namespace App\Filament\Resources\Ldap\LdapUserViewResource\Pages;

use App\Filament\Resources\Ldap\LdapUserViewResource;
use App\Services\Ldap\LdapBulkDeleteService;
use App\Services\Ldap\LdapRoleSyncService;
use App\Services\Ldap\LdapUserCrudService;
use App\Services\Ldap\LdapUserSyncService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection;

class ListLdapUserViews extends ListRecords
{
    protected static string $resource = LdapUserViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createLdapUser')
                ->label('Create User')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(LdapUserViewResource::getUrl('create')),

            Actions\Action::make('syncLdap')
                ->label('Sync LDAP')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    app(LdapRoleSyncService::class)->sync();
                    app(LdapUserSyncService::class)->sync();

                    Notification::make()
                        ->title('Users and roles synced successfully')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Actions\BulkAction::make('deleteSelectedUsers')
                ->label('Delete Selected Users')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion()
                ->modalHeading('Delete selected users')
                ->modalDescription('User yang dipilih akan dihapus dari LDAP dan local snapshot.')
                ->action(function (Collection $records) {
                    $successCount = 0;
                    $failedCount = 0;
                    $failedItems = [];

                    $crud = app(LdapUserCrudService::class);
                    $bulk = app(LdapBulkDeleteService::class);

                    foreach ($records as $record) {
                        $uid = (string) $record->uid;
                        $dn = (string) $record->dn;
                        $before = $record->toArray();

                        try {
                            $bulk->guardAgainstDeletingCurrentUser($uid, $record->mail);

                            $crud->deleteByDn($dn);
                            $successCount++;

                            $bulk->logSuccess(
                                action: 'delete_user_batch',
                                targetUid: $uid,
                                targetDn: $dn,
                                beforeData: $before,
                                afterData: null,
                                message: 'LDAP user deleted successfully via batch delete.'
                            );
                        } catch (\Throwable $e) {
                            $failedCount++;
                            $failedItems[] = $uid !== '' ? $uid : $dn;

                            $bulk->logFailure(
                                action: 'delete_user_batch',
                                targetUid: $uid,
                                targetDn: $dn,
                                beforeData: $before,
                                afterData: null,
                                message: 'Batch delete user failed.',
                                errorMessage: $e->getMessage(),
                            );
                        }
                    }

                    $syncStatus = 'success';

                    try {
                        app(LdapRoleSyncService::class)->sync();
                        app(LdapUserSyncService::class)->sync();
                    } catch (\Throwable $e) {
                        $syncStatus = 'failed';
                    }

                    if ($syncStatus === 'failed') {
                        Notification::make()
                            ->title("Batch delete selesai: {$successCount} sukses, {$failedCount} gagal")
                            ->body('Delete LDAP berhasil, tetapi sync local view gagal. Jalankan Sync LDAP.')
                            ->warning()
                            ->send();

                        return;
                    }

                    if ($failedCount > 0) {
                        $body = "Success: {$successCount}, Failed: {$failedCount}";
                        if (! empty($failedItems)) {
                            $body .= '. Failed users: ' . implode(', ', array_slice($failedItems, 0, 10));
                        }

                        Notification::make()
                            ->title('Batch delete completed with some failures')
                            ->body($body)
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title("Batch delete successful: {$successCount} users deleted")
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }
}
