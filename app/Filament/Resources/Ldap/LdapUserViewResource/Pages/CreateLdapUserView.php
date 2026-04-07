<?php

namespace App\Filament\Resources\Ldap\LdapUserViewResource\Pages;

use App\Filament\Resources\Ldap\LdapUserViewResource;
use App\Models\LdapUserView;
use App\Services\Ldap\LdapAuditTrailService;
use App\Services\Ldap\LdapRoleSyncService;
use App\Services\Ldap\LdapUserCrudService;
use App\Services\Ldap\LdapUserSyncService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateLdapUserView extends CreateRecord
{
    protected static string $resource = LdapUserViewResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $uid = $data['uid'] ?? null;
        $ldapStatus = 'failed';
        $syncStatus = 'not_run';

        try {
            if (blank($data['password'] ?? null)) {
                throw ValidationException::withMessages([
                    'password' => 'Password wajib diisi.',
                ]);
            }

            $createResult = app(LdapUserCrudService::class)->create($data);
            $ldapStatus = 'success';

            try {
                app(LdapRoleSyncService::class)->sync();
                app(LdapUserSyncService::class)->sync();
                $syncStatus = 'success';
            } catch (\Throwable $syncException) {
                $syncStatus = 'failed';
            }

            $record = LdapUserView::query()
                ->where('uid', $uid)
                ->first();

            if (! $record) {
                throw ValidationException::withMessages([
                    'uid' => 'LDAP create berhasil, tetapi local view belum tersinkron. Jalankan Sync LDAP.',
                ]);
            }

            app(LdapAuditTrailService::class)->log(
                action: 'create_user',
                targetUid: $uid,
                targetDn: $createResult['dn'],
                beforeData: null,
                afterData: $record->toArray(),
                status: $syncStatus === 'success' ? 'success' : 'warning',
                ldapStatus: $ldapStatus,
                syncStatus: $syncStatus,
                message: $syncStatus === 'success'
                    ? 'LDAP user created successfully.'
                    : 'LDAP user created successfully, but local sync failed.',
                errorMessage: null
            );

            return $record;
        } catch (\Throwable $e) {
            app(LdapAuditTrailService::class)->log(
                action: 'create_user',
                targetUid: $uid,
                targetDn: null,
                beforeData: null,
                afterData: $data,
                status: 'failed',
                ldapStatus: $ldapStatus,
                syncStatus: $syncStatus,
                message: 'Create user failed.',
                errorMessage: $e->getMessage()
            );

            throw ValidationException::withMessages([
                'uid' => $e->getMessage(),
            ]);
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('User created successfully');
    }
}
