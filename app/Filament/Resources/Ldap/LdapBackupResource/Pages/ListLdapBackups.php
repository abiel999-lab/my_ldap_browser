<?php

namespace App\Filament\Resources\Ldap\LdapBackupResource\Pages;

use App\Filament\Resources\Ldap\LdapBackupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLdapBackups extends ListRecords
{
    protected static string $resource = LdapBackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
