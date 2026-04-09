<?php

namespace App\Filament\Resources\Ldap\LdapRoleViewResource\Pages;

use App\Filament\Resources\Ldap\LdapRoleViewResource;
use App\Services\Ldap\LdapAuditTrailService;
use App\Services\Ldap\LdapNativeService;
use App\Services\Ldap\LdapRoleSyncService;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLdapRoleViews extends ListRecords
{
    protected static string $resource = LdapRoleViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createRole')
                ->label('Create Role')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    TextInput::make('cn')
                        ->label('Role CN')
                        ->required()
                        ->placeholder('role-staff-baru'),
                ])
                ->action(function (array $data) {
                    $cn = trim((string) ($data['cn'] ?? ''));
                    $rolesDn = 'ou=roles,ou=groups,dc=petra,dc=ac,dc=id';
                    $dummyDn = 'uid=dummy,ou=services,dc=petra,dc=ac,dc=id';
                    $dn = "cn={$cn},{$rolesDn}";

                    try {
                        /** @var LdapNativeService $ldap */
                        $ldap = app(LdapNativeService::class);

                        if ($ldap->read($dn, ['cn'])) {
                            throw new \RuntimeException("Role {$cn} sudah ada.");
                        }

                        $ldap->add($dn, [
                            'objectClass' => ['top', 'groupOfNames'],
                            'cn' => $cn,
                            'member' => [$dummyDn],
                        ]);

                        app(LdapRoleSyncService::class)->sync();

                        app(LdapAuditTrailService::class)->log(
                            action: 'create_user_role',
                            targetUid: null,
                            targetDn: $dn,
                            beforeData: null,
                            afterData: ['cn' => $cn],
                            status: 'success',
                            ldapStatus: 'success',
                            syncStatus: 'success',
                            message: 'User role created successfully.',
                            errorMessage: null
                        );

                        Notification::make()
                            ->title('User role created successfully')
                            ->success()
                            ->send();

                        $this->redirect(static::getResource()::getUrl('index'));
                    } catch (\Throwable $e) {
                        app(LdapAuditTrailService::class)->log(
                            action: 'create_user_role',
                            targetUid: null,
                            targetDn: $dn,
                            beforeData: null,
                            afterData: ['cn' => $cn],
                            status: 'failed',
                            ldapStatus: 'failed',
                            syncStatus: 'not_run',
                            message: 'Failed to create user role.',
                            errorMessage: $e->getMessage()
                        );

                        Notification::make()
                            ->title('Failed to create user role')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('syncRoles')
                ->label('Sync User Roles')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    app(LdapRoleSyncService::class)->sync();

                    Notification::make()
                        ->title('User roles synced successfully')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }
}
