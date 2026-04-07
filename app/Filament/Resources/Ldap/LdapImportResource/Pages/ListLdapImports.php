<?php

namespace App\Filament\Resources\Ldap\LdapImportResource\Pages;

use App\Filament\Resources\Ldap\LdapImportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLdapImports extends ListRecords
{
    protected static string $resource = LdapImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
