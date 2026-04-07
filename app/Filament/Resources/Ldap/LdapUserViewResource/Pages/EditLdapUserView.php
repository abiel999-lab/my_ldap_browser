<?php

namespace App\Filament\Resources\Ldap\LdapUserViewResource\Pages;

use App\Filament\Resources\Ldap\LdapUserViewResource;
use App\Models\LdapUserView;
use App\Services\Ldap\LdapAuditTrailService;
use App\Services\Ldap\LdapRoleSyncService;
use App\Services\Ldap\LdapUserCrudService;
use App\Services\Ldap\LdapUserSyncService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditLdapUserView extends EditRecord
{
    protected static string $resource = LdapUserViewResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $uid = $record->uid;
        $dn = $record->dn;
        $before = $record->toArray();

        $ldapStatus = 'failed';
        $syncStatus = 'not_run';

        try {
            $updateResult = app(LdapUserCrudService::class)->updateByDn($dn, $data);
            $ldapStatus = 'success';

            try {
                app(LdapRoleSyncService::class)->sync();
                app(LdapUserSyncService::class)->sync();
                $syncStatus = 'success';
            } catch (\Throwable $syncException) {
                $syncStatus = 'failed';
            }

            $fresh = LdapUserView::query()
                ->where('uid', $uid)
                ->first();

            if (! $fresh) {
                $fresh = $record;
            }

            app(LdapAuditTrailService::class)->log(
                action: 'update_user',
                targetUid: $uid,
                targetDn: $updateResult['dn'],
                beforeData: $before,
                afterData: $fresh->toArray(),
                status: $syncStatus === 'success' ? 'success' : 'warning',
                ldapStatus: $ldapStatus,
                syncStatus: $syncStatus,
                message: $syncStatus === 'success'
                    ? 'LDAP user updated successfully.'
                    : 'LDAP user updated successfully, but local sync failed.',
                errorMessage: null
            );

            return $fresh;
        } catch (\Throwable $e) {
            app(LdapAuditTrailService::class)->log(
                action: 'update_user',
                targetUid: $uid,
                targetDn: $dn,
                beforeData: $before,
                afterData: $data,
                status: 'failed',
                ldapStatus: $ldapStatus,
                syncStatus: $syncStatus,
                message: 'Update user failed.',
                errorMessage: $e->getMessage()
            );

            throw ValidationException::withMessages([
                'cn' => $e->getMessage(),
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('deleteLdapUser')
                ->label('Delete User')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->record;
                    $uid = $record->uid;
                    $dn = $record->dn;
                    $before = $record->toArray();

                    $ldapStatus = 'failed';
                    $syncStatus = 'not_run';

                    try {
                        $deleteResult = app(LdapUserCrudService::class)->deleteByDn($dn);
                        $ldapStatus = 'success';

                        try {
                            app(LdapRoleSyncService::class)->sync();
                            app(LdapUserSyncService::class)->sync();
                            $syncStatus = 'success';
                        } catch (\Throwable $syncException) {
                            $syncStatus = 'failed';
                        }

                        app(LdapAuditTrailService::class)->log(
                            action: 'delete_user',
                            targetUid: $uid,
                            targetDn: $deleteResult['dn'],
                            beforeData: $before,
                            afterData: null,
                            status: $syncStatus === 'success' ? 'success' : 'warning',
                            ldapStatus: $ldapStatus,
                            syncStatus: $syncStatus,
                            message: $syncStatus === 'success'
                                ? 'LDAP user deleted successfully.'
                                : 'LDAP user deleted successfully, but local sync failed.',
                            errorMessage: null
                        );

                        Notification::make()
                            ->success()
                            ->title('User deleted successfully')
                            ->send();

                        $this->redirect(LdapUserViewResource::getUrl('index'));
                    } catch (\Throwable $e) {
                        app(LdapAuditTrailService::class)->log(
                            action: 'delete_user',
                            targetUid: $uid,
                            targetDn: $dn,
                            beforeData: $before,
                            afterData: null,
                            status: 'failed',
                            ldapStatus: $ldapStatus,
                            syncStatus: $syncStatus,
                            message: 'Delete user failed.',
                            errorMessage: $e->getMessage()
                        );

                        throw ValidationException::withMessages([
                            'cn' => $e->getMessage(),
                        ]);
                    }
                }),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('User updated successfully');
    }
}
