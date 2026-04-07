<?php

namespace App\Filament\Resources\Ldap\LdapUserViewResource\Pages;

use App\Filament\Resources\Ldap\LdapUserViewResource;
use App\Services\Ldap\LdapRoleSyncService;
use App\Services\Ldap\LdapUserSyncService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

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
}
