<?php

namespace App\Filament\Resources\Ldap\LdapAppRoleMemberViewResource\Pages;

use App\Filament\Resources\Ldap\LdapAppRoleMemberViewResource;
use App\Models\LdapAppRoleView;
use App\Models\LdapUserView;
use App\Services\Ldap\LdapAppRoleMemberService;
use App\Services\Ldap\LdapAppSyncService;
use App\Services\Ldap\LdapAuditTrailService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLdapAppRoleMemberViews extends ListRecords
{
    protected static string $resource = LdapAppRoleMemberViewResource::class;

    public ?string $app = null;
    public ?string $role = null;

    public function mount(): void
    {
        $this->app = request()->query('app');
        $this->role = request()->query('role');
    }

    public function getTitle(): string
    {
        return 'App Role Members - ' . ($this->app ?: '-') . ' - ' . ($this->role ?: '-');
    }

    protected function currentPageUrl(): string
    {
        return static::getResource()::getUrl('index', [
            'app' => $this->app,
            'role' => $this->role,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backToAppRoles')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(\App\Filament\Resources\Ldap\LdapAppViewResource::getUrl('index', [
                    'app' => $this->app,
                ])),

            Actions\Action::make('addUserToRole')
                ->label('Add User to Role')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    Select::make('uid')
                        ->label('User')
                        ->options(fn () => LdapUserView::query()
                            ->orderBy('uid')
                            ->get()
                            ->mapWithKeys(fn ($user) => [
                                $user->uid => "{$user->uid} - {$user->cn}"
                            ])
                            ->toArray()
                        )
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        app(LdapAppRoleMemberService::class)->assignUserToAppRole(
                            (string) $this->app,
                            (string) $this->role,
                            (string) $data['uid']
                        );

                        Notification::make()
                            ->title('User added to app role successfully')
                            ->success()
                            ->send();

                        $this->redirect($this->currentPageUrl());
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to add user to app role')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('batchAddUsersToAppRole')
                ->label('Batch Add Users')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->form([
                    Select::make('uids')
                        ->label('Users')
                        ->multiple()
                        ->options(fn () => LdapUserView::query()
                            ->orderBy('uid')
                            ->get()
                            ->mapWithKeys(fn ($user) => [
                                $user->uid => "{$user->uid} - {$user->cn}"
                            ])
                            ->toArray()
                        )
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $uids = collect($data['uids'] ?? [])->filter()->values();
                    $success = 0;
                    $failed = 0;
                    $audit = app(LdapAuditTrailService::class);

                    foreach ($uids as $uid) {
                        try {
                            app(LdapAppRoleMemberService::class)->assignUserToAppRole(
                                (string) $this->app,
                                (string) $this->role,
                                (string) $uid
                            );

                            $success++;

                            $audit->log(
                                action: 'add_user_to_app_role_batch',
                                targetUid: (string) $uid,
                                targetDn: null,
                                beforeData: null,
                                afterData: [
                                    'app' => $this->app,
                                    'role' => $this->role,
                                ],
                                status: 'success',
                                ldapStatus: 'success',
                                syncStatus: 'not_run',
                                message: 'User added to app role successfully via batch add.',
                                errorMessage: null
                            );
                        } catch (\Throwable $e) {
                            $failed++;

                            $audit->log(
                                action: 'add_user_to_app_role_batch',
                                targetUid: (string) $uid,
                                targetDn: null,
                                beforeData: null,
                                afterData: [
                                    'app' => $this->app,
                                    'role' => $this->role,
                                ],
                                status: 'failed',
                                ldapStatus: 'failed',
                                syncStatus: 'not_run',
                                message: 'Batch add user to app role failed.',
                                errorMessage: $e->getMessage()
                            );
                        }
                    }

                    app(LdapAppSyncService::class)->sync();

                    Notification::make()
                        ->title("Batch add selesai. Success: {$success}, Failed: {$failed}")
                        ->success()
                        ->send();

                    $this->redirect($this->currentPageUrl());
                }),

            Actions\Action::make('removeUserFromRole')
                ->label('Remove User from Role')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Select::make('uid')
                        ->label('User')
                        ->options(function () {
                            $roleView = LdapAppRoleView::query()
                                ->where('app_cn', $this->app)
                                ->where('role_cn', $this->role)
                                ->first();

                            if (! $roleView) {
                                return [];
                            }

                            $memberDns = collect($roleView->members ?? []);

                            $uids = $memberDns
                                ->map(function ($dn) {
                                    if (preg_match('/uid=([^,]+)/i', (string) $dn, $matches)) {
                                        return $matches[1];
                                    }

                                    return null;
                                })
                                ->filter()
                                ->reject(fn ($uid) => strtolower((string) $uid) === 'dummy')
                                ->values();

                            return LdapUserView::query()
                                ->whereIn('uid', $uids)
                                ->orderBy('uid')
                                ->get()
                                ->mapWithKeys(fn ($user) => [
                                    $user->uid => "{$user->uid} - {$user->cn}"
                                ])
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        app(LdapAppRoleMemberService::class)->removeUserFromAppRole(
                            (string) $this->app,
                            (string) $this->role,
                            (string) $data['uid']
                        );

                        Notification::make()
                            ->title('User removed from app role successfully')
                            ->success()
                            ->send();

                        $this->redirect($this->currentPageUrl());
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to remove user from app role')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('syncRoleMembers')
                ->label('Sync Role Members')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    app(LdapAppSyncService::class)->sync();

                    Notification::make()
                        ->title('App role members synced successfully')
                        ->success()
                        ->send();

                    $this->redirect($this->currentPageUrl());
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $roleView = LdapAppRoleView::query()
            ->where('app_cn', $this->app)
            ->where('role_cn', $this->role)
            ->first();

        if (! $roleView) {
            return LdapUserView::query()->whereRaw('1 = 0');
        }

        $memberDns = collect($roleView->members ?? []);

        $uids = $memberDns
            ->map(function ($dn) {
                if (preg_match('/uid=([^,]+)/i', (string) $dn, $matches)) {
                    return $matches[1];
                }

                return null;
            })
            ->filter()
            ->reject(fn ($uid) => strtolower((string) $uid) === 'dummy')
            ->values()
            ->all();

        if (empty($uids)) {
            return LdapUserView::query()->whereRaw('1 = 0');
        }

        return LdapUserView::query()->whereIn('uid', $uids);
    }

    protected function getTableBulkActions(): array
    {
        return [
            Actions\BulkAction::make('removeSelectedUsersFromAppRole')
                ->label('Remove Selected Users from App Role')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion()
                ->modalHeading('Remove selected users from app role')
                ->modalDescription('User yang dipilih akan dihapus dari app role LDAP ini.')
                ->action(function ($records) {
                    $success = 0;
                    $failed = 0;
                    $audit = app(LdapAuditTrailService::class);

                    foreach ($records as $record) {
                        $uid = (string) $record->uid;

                        try {
                            app(LdapAppRoleMemberService::class)->removeUserFromAppRole(
                                (string) $this->app,
                                (string) $this->role,
                                $uid
                            );

                            $success++;

                            $audit->log(
                                action: 'remove_user_from_app_role_batch',
                                targetUid: $uid,
                                targetDn: $record->dn ?? null,
                                beforeData: $record->toArray(),
                                afterData: null,
                                status: 'success',
                                ldapStatus: 'success',
                                syncStatus: 'not_run',
                                message: 'User removed from app role successfully via batch action.',
                                errorMessage: null
                            );
                        } catch (\Throwable $e) {
                            $failed++;

                            $audit->log(
                                action: 'remove_user_from_app_role_batch',
                                targetUid: $uid,
                                targetDn: $record->dn ?? null,
                                beforeData: $record->toArray(),
                                afterData: null,
                                status: 'failed',
                                ldapStatus: 'failed',
                                syncStatus: 'not_run',
                                message: 'Batch remove user from app role failed.',
                                errorMessage: $e->getMessage()
                            );
                        }
                    }

                    app(LdapAppSyncService::class)->sync();

                    Notification::make()
                        ->title("Batch remove selesai. Success: {$success}, Failed: {$failed}")
                        ->success()
                        ->send();

                    $this->redirect($this->currentPageUrl());
                }),
        ];
    }
}
