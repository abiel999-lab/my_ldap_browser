<?php

namespace App\Filament\Resources\Ldap\LdapUnitMemberViewResource\Pages;

use App\Filament\Resources\Ldap\LdapUnitMemberViewResource;
use App\Models\LdapUserView;
use App\Services\Ldap\LdapAuditTrailService;
use App\Services\Ldap\LdapUnitMemberService;
use App\Services\Ldap\LdapUnitSyncService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLdapUnitMemberViews extends ListRecords
{
    protected static string $resource = LdapUnitMemberViewResource::class;

    public ?string $unit = null;

    public function mount(): void
    {
        $this->unit = request()->query('unit');
    }

    public function getTitle(): string
    {
        return 'Unit Members - ' . ($this->unit ?: '-');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backToUnits')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(\App\Filament\Resources\Ldap\LdapUnitViewResource::getUrl('index')),

            Actions\Action::make('addUserToUnit')
                ->label('Add User to Unit')
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
                        app(LdapUnitMemberService::class)->assignUserToUnit(
                            (string) $this->unit,
                            (string) $data['uid']
                        );

                        Notification::make()
                            ->title('User added to unit successfully')
                            ->success()
                            ->send();

                        $this->redirect(request()->fullUrl());
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to add user to unit')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('batchAddUsersToUnit')
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
                            app(LdapUnitMemberService::class)->assignUserToUnit(
                                (string) $this->unit,
                                (string) $uid
                            );

                            $success++;

                            $audit->log(
                                action: 'add_user_to_unit_batch',
                                targetUid: (string) $uid,
                                targetDn: null,
                                beforeData: null,
                                afterData: ['unit' => $this->unit],
                                status: 'success',
                                ldapStatus: 'success',
                                syncStatus: 'not_run',
                                message: 'User added to unit successfully via batch add.',
                                errorMessage: null
                            );
                        } catch (\Throwable $e) {
                            $failed++;

                            $audit->log(
                                action: 'add_user_to_unit_batch',
                                targetUid: (string) $uid,
                                targetDn: null,
                                beforeData: null,
                                afterData: ['unit' => $this->unit],
                                status: 'failed',
                                ldapStatus: 'failed',
                                syncStatus: 'not_run',
                                message: 'Batch add user to unit failed.',
                                errorMessage: $e->getMessage()
                            );
                        }
                    }

                    app(LdapUnitSyncService::class)->sync();

                    Notification::make()
                        ->title("Batch add selesai. Success: {$success}, Failed: {$failed}")
                        ->success()
                        ->send();

                    $this->redirect(request()->fullUrl());
                }),

            Actions\Action::make('removeUserFromUnit')
                ->label('Remove User from Unit')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Select::make('uid')
                        ->label('User')
                        ->options(fn () => $this->getCurrentMemberOptions())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        app(LdapUnitMemberService::class)->removeUserFromUnit(
                            (string) $this->unit,
                            (string) $data['uid']
                        );

                        Notification::make()
                            ->title('User removed from unit successfully')
                            ->success()
                            ->send();

                        $this->redirect(request()->fullUrl());
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to remove user from unit')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('syncUnitMembers')
                ->label('Sync Unit Members')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    app(LdapUnitSyncService::class)->sync();

                    Notification::make()
                        ->title('Unit members synced successfully')
                        ->success()
                        ->send();

                    $this->redirect(request()->fullUrl());
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $memberOptions = $this->getCurrentMemberOptions();
        $uids = array_keys($memberOptions);

        if (empty($uids)) {
            return LdapUserView::query()->whereRaw('1 = 0');
        }

        return LdapUserView::query()->whereIn('uid', $uids);
    }

    protected function getTableBulkActions(): array
    {
        return [
            Actions\BulkAction::make('removeSelectedUsersFromUnit')
                ->label('Remove Selected Users from Unit')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion()
                ->modalHeading('Remove selected users from unit')
                ->modalDescription('User yang dipilih akan dihapus dari unit LDAP ini.')
                ->action(function ($records) {
                    $success = 0;
                    $failed = 0;
                    $audit = app(LdapAuditTrailService::class);

                    foreach ($records as $record) {
                        $uid = (string) $record->uid;

                        try {
                            app(LdapUnitMemberService::class)->removeUserFromUnit(
                                (string) $this->unit,
                                $uid
                            );

                            $success++;

                            $audit->log(
                                action: 'remove_user_from_unit_batch',
                                targetUid: $uid,
                                targetDn: $record->dn ?? null,
                                beforeData: $record->toArray(),
                                afterData: null,
                                status: 'success',
                                ldapStatus: 'success',
                                syncStatus: 'not_run',
                                message: 'User removed from unit successfully via batch action.',
                                errorMessage: null
                            );
                        } catch (\Throwable $e) {
                            $failed++;

                            $audit->log(
                                action: 'remove_user_from_unit_batch',
                                targetUid: $uid,
                                targetDn: $record->dn ?? null,
                                beforeData: $record->toArray(),
                                afterData: null,
                                status: 'failed',
                                ldapStatus: 'failed',
                                syncStatus: 'not_run',
                                message: 'Batch remove user from unit failed.',
                                errorMessage: $e->getMessage()
                            );
                        }
                    }

                    app(LdapUnitSyncService::class)->sync();

                    Notification::make()
                        ->title("Batch remove selesai. Success: {$success}, Failed: {$failed}")
                        ->success()
                        ->send();

                    $this->redirect(request()->fullUrl());
                }),
        ];
    }

    protected function getCurrentMemberOptions(): array
    {
        $ldap = app(\App\Services\Ldap\LdapNativeService::class);
        $unitDn = "cn={$this->unit},ou=units,ou=groups,dc=petra,dc=ac,dc=id";
        $entry = $ldap->read($unitDn, ['member']);

        if (! $entry) {
            return [];
        }

        $memberDns = collect($ldap->extractMany($entry, 'member'));

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

        if ($uids->isEmpty()) {
            return [];
        }

        return LdapUserView::query()
            ->whereIn('uid', $uids->all())
            ->orderBy('uid')
            ->get()
            ->mapWithKeys(fn ($user) => [
                $user->uid => "{$user->uid} - {$user->cn}"
            ])
            ->toArray();
    }
}
