<?php

namespace App\Filament\Resources\Ldap\LdapRoleMemberViewResource\Pages;

use App\Filament\Resources\Ldap\LdapRoleMemberViewResource;
use App\Models\LdapRoleMemberView;
use App\Models\LdapUserView;
use App\Services\Ldap\LdapAuditTrailService;
use App\Services\Ldap\LdapRoleAssignmentService;
use App\Services\Ldap\LdapRoleSyncService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as SupportCollection;

class ListLdapRoleMemberViews extends ListRecords
{
    protected static string $resource = LdapRoleMemberViewResource::class;

    public ?string $role = null;

    public function mount(): void
    {
        $this->role = request()->query('role');
    }

    public function getTitle(): string
    {
        return 'Role Members - ' . ($this->role ?: '-');
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
                        app(LdapRoleAssignmentService::class)->assignRole(
                            (string) $data['uid'],
                            (string) $this->role
                        );

                        app(LdapRoleSyncService::class)->sync();

                        Notification::make()
                            ->title('User added to role successfully')
                            ->success()
                            ->send();

                        $this->redirect(request()->fullUrl());
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to add user to role')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('batchAddUsersToRole')
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
                            app(LdapRoleAssignmentService::class)->assignRole(
                                (string) $uid,
                                (string) $this->role
                            );

                            $success++;

                            $audit->log(
                                action: 'add_user_to_role_batch',
                                targetUid: (string) $uid,
                                targetDn: null,
                                beforeData: null,
                                afterData: ['role' => $this->role],
                                status: 'success',
                                ldapStatus: 'success',
                                syncStatus: 'not_run',
                                message: 'User added to role successfully via batch add.',
                                errorMessage: null
                            );
                        } catch (\Throwable $e) {
                            $failed++;

                            $audit->log(
                                action: 'add_user_to_role_batch',
                                targetUid: (string) $uid,
                                targetDn: null,
                                beforeData: null,
                                afterData: ['role' => $this->role],
                                status: 'failed',
                                ldapStatus: 'failed',
                                syncStatus: 'not_run',
                                message: 'Batch add user to role failed.',
                                errorMessage: $e->getMessage()
                            );
                        }
                    }

                    app(LdapRoleSyncService::class)->sync();

                    Notification::make()
                        ->title("Batch add selesai. Success: {$success}, Failed: {$failed}")
                        ->success()
                        ->send();

                    $this->redirect(request()->fullUrl());
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
                            return LdapRoleMemberView::query()
                                ->where('role_cn', $this->role)
                                ->where('uid', '!=', 'dummy')
                                ->orderBy('uid')
                                ->pluck('uid', 'uid')
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        app(LdapRoleAssignmentService::class)->removeRole(
                            (string) $data['uid'],
                            (string) $this->role
                        );

                        app(LdapRoleSyncService::class)->sync();

                        Notification::make()
                            ->title('User removed from role successfully')
                            ->success()
                            ->send();

                        $this->redirect(request()->fullUrl());
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to remove user from role')
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
                    app(LdapRoleSyncService::class)->sync();

                    Notification::make()
                        ->title('Role members synced successfully')
                        ->success()
                        ->send();

                    $this->redirect(request()->fullUrl());
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = LdapRoleMemberView::query();

        if ($this->role) {
            $query->where('role_cn', $this->role);
        }

        return $query;
    }

    protected function getTableBulkActions(): array
    {
        return [
            Actions\BulkAction::make('removeSelectedUsersFromRole')
                ->label('Remove Selected Users from Role')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion()
                ->modalHeading('Remove selected users from role')
                ->modalDescription('User yang dipilih akan dihapus dari role LDAP ini.')
                ->action(function ($records) {
                    $success = 0;
                    $failed = 0;
                    $audit = app(LdapAuditTrailService::class);

                    foreach ($records as $record) {
                        $uid = (string) $record->uid;

                        if (strtolower($uid) === 'dummy') {
                            $failed++;
                            continue;
                        }

                        try {
                            app(LdapRoleAssignmentService::class)->removeRole(
                                $uid,
                                (string) $this->role
                            );

                            $success++;

                            $audit->log(
                                action: 'remove_user_from_role_batch',
                                targetUid: $uid,
                                targetDn: $record->member_dn ?? $record->dn ?? null,
                                beforeData: $record->toArray(),
                                afterData: null,
                                status: 'success',
                                ldapStatus: 'success',
                                syncStatus: 'not_run',
                                message: 'User removed from role successfully via batch action.',
                                errorMessage: null
                            );
                        } catch (\Throwable $e) {
                            $failed++;

                            $audit->log(
                                action: 'remove_user_from_role_batch',
                                targetUid: $uid,
                                targetDn: $record->member_dn ?? $record->dn ?? null,
                                beforeData: $record->toArray(),
                                afterData: null,
                                status: 'failed',
                                ldapStatus: 'failed',
                                syncStatus: 'not_run',
                                message: 'Batch remove user from role failed.',
                                errorMessage: $e->getMessage()
                            );
                        }
                    }

                    app(LdapRoleSyncService::class)->sync();

                    Notification::make()
                        ->title("Batch remove selesai. Success: {$success}, Failed: {$failed}")
                        ->success()
                        ->send();

                    $this->redirect(request()->fullUrl());
                }),
        ];
    }
}
