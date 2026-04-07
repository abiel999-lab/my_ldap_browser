<?php

namespace App\Filament\Resources\Ldap\LdapRoleMemberViewResource\Pages;

use App\Filament\Resources\Ldap\LdapRoleMemberViewResource;
use App\Models\LdapRoleMemberView;
use App\Models\LdapUserView;
use App\Services\Ldap\LdapAuditTrailService;
use App\Services\Ldap\LdapRoleAssignmentService;
use App\Services\Ldap\LdapRoleSyncService;
use App\Services\Ldap\LdapUserSyncService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLdapRoleMemberViews extends ListRecords
{
    protected static string $resource = LdapRoleMemberViewResource::class;

    public ?string $role = null;

    public function mount(): void
    {
        $this->role = request()->query('role');
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->role) {
            $query->where('role_cn', $this->role);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addUserToRole')
                ->label('Add User to Role')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    Select::make('uid')
                        ->label('User')
                        ->searchable()
                        ->required()
                        ->options(function () {
                            return LdapUserView::query()
                                ->orderBy('uid')
                                ->get()
                                ->mapWithKeys(fn ($user) => [
                                    $user->uid => $user->uid . ' - ' . ($user->cn ?? '-'),
                                ])
                                ->toArray();
                        }),
                ])
                ->action(function (array $data): void {
                    if (! $this->role) {
                        throw new \Exception('Role tidak ditemukan dari URL.');
                    }

                    $ldapStatus = 'failed';
                    $syncStatus = 'not_run';

                    try {
                        app(LdapRoleAssignmentService::class)->assignRole($data['uid'], $this->role);
                        $ldapStatus = 'success';

                        try {
                            app(LdapRoleSyncService::class)->sync();
                            app(LdapUserSyncService::class)->sync();
                            $syncStatus = 'success';
                        } catch (\Throwable $syncException) {
                            $syncStatus = 'failed';
                        }

                        app(LdapAuditTrailService::class)->log(
                            action: 'add_user_to_role',
                            targetUid: $data['uid'],
                            targetDn: "uid={$data['uid']},ou=people,dc=petra,dc=ac,dc=id",
                            beforeData: null,
                            afterData: [
                                'role' => $this->role,
                                'uid' => $data['uid'],
                            ],
                            status: $syncStatus === 'success' ? 'success' : 'warning',
                            ldapStatus: $ldapStatus,
                            syncStatus: $syncStatus,
                            message: $syncStatus === 'success'
                                ? 'User added to role successfully.'
                                : 'User added to role successfully, but local sync failed.',
                            errorMessage: null
                        );

                        Notification::make()
                            ->success()
                            ->title('User added to role')
                            ->send();

                        $this->redirect(
                            LdapRoleMemberViewResource::getUrl('index', ['role' => $this->role]),
                            navigate: true
                        );
                    } catch (\Throwable $e) {
                        app(LdapAuditTrailService::class)->log(
                            action: 'add_user_to_role',
                            targetUid: $data['uid'] ?? null,
                            targetDn: null,
                            beforeData: null,
                            afterData: [
                                'role' => $this->role,
                                'uid' => $data['uid'] ?? null,
                            ],
                            status: 'failed',
                            ldapStatus: $ldapStatus,
                            syncStatus: $syncStatus,
                            message: 'Add user to role failed.',
                            errorMessage: $e->getMessage()
                        );

                        throw $e;
                    }
                }),

            Actions\Action::make('removeUserFromRole')
                ->label('Remove User from Role')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->form([
                    Select::make('uid')
                        ->label('User in This Role')
                        ->searchable()
                        ->required()
                        ->options(function () {
                            if (! $this->role) {
                                return [];
                            }

                            return LdapRoleMemberView::query()
                                ->where('role_cn', $this->role)
                                ->orderBy('uid')
                                ->get()
                                ->mapWithKeys(fn ($row) => [
                                    $row->uid => $row->uid . ' - ' . ($row->member_dn ?? '-'),
                                ])
                                ->toArray();
                        }),
                ])
                ->action(function (array $data): void {
                    if (! $this->role) {
                        throw new \Exception('Role tidak ditemukan dari URL.');
                    }

                    $ldapStatus = 'failed';
                    $syncStatus = 'not_run';

                    try {
                        app(LdapRoleAssignmentService::class)->removeRole($data['uid'], $this->role);
                        $ldapStatus = 'success';

                        try {
                            app(LdapRoleSyncService::class)->sync();
                            app(LdapUserSyncService::class)->sync();
                            $syncStatus = 'success';
                        } catch (\Throwable $syncException) {
                            $syncStatus = 'failed';
                        }

                        app(LdapAuditTrailService::class)->log(
                            action: 'remove_user_from_role',
                            targetUid: $data['uid'],
                            targetDn: "uid={$data['uid']},ou=people,dc=petra,dc=ac,dc=id",
                            beforeData: [
                                'role' => $this->role,
                                'uid' => $data['uid'],
                            ],
                            afterData: null,
                            status: $syncStatus === 'success' ? 'success' : 'warning',
                            ldapStatus: $ldapStatus,
                            syncStatus: $syncStatus,
                            message: $syncStatus === 'success'
                                ? 'User removed from role successfully.'
                                : 'User removed from role successfully, but local sync failed.',
                            errorMessage: null
                        );

                        Notification::make()
                            ->success()
                            ->title('User removed from role')
                            ->send();

                        $this->redirect(
                            LdapRoleMemberViewResource::getUrl('index', ['role' => $this->role]),
                            navigate: true
                        );
                    } catch (\Throwable $e) {
                        app(LdapAuditTrailService::class)->log(
                            action: 'remove_user_from_role',
                            targetUid: $data['uid'] ?? null,
                            targetDn: null,
                            beforeData: [
                                'role' => $this->role,
                                'uid' => $data['uid'] ?? null,
                            ],
                            afterData: null,
                            status: 'failed',
                            ldapStatus: $ldapStatus,
                            syncStatus: $syncStatus,
                            message: 'Remove user from role failed.',
                            errorMessage: $e->getMessage()
                        );

                        throw $e;
                    }
                }),

            Actions\Action::make('syncRoleMembers')
                ->label('Sync Role Members')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (): void {
                    app(LdapRoleSyncService::class)->sync();
                    app(LdapUserSyncService::class)->sync();

                    Notification::make()
                        ->title('Role members synced successfully')
                        ->success()
                        ->send();

                    $this->redirect(
                        LdapRoleMemberViewResource::getUrl('index', ['role' => $this->role]),
                        navigate: true
                    );
                }),
        ];
    }

    public function getTitle(): string
    {
        return $this->role
            ? 'Role Members - ' . $this->role
            : 'Role Members';
    }
}
