<?php

namespace App\Filament\Resources\Ldap\LdapExportResource\Pages;

use App\Filament\Resources\Ldap\LdapExportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLdapExports extends ListRecords
{
    protected static string $resource = LdapExportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
