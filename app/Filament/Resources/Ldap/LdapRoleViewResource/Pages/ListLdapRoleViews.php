<?php

namespace App\Filament\Resources\Ldap\LdapRoleViewResource\Pages;

use App\Filament\Resources\Ldap\LdapRoleViewResource;
use App\Services\Ldap\LdapRoleSyncService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLdapRoleViews extends ListRecords
{
    protected static string $resource = LdapRoleViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('syncRoles')
                ->label('Sync Roles')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(function () {
                    app(LdapRoleSyncService::class)->sync();

                    Notification::make()
                        ->title('Roles synced successfully')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }
}
