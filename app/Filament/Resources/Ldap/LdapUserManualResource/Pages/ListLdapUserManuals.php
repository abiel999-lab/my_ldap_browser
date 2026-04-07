<?php

namespace App\Filament\Resources\Ldap\LdapUserManualResource\Pages;

use App\Filament\Resources\Ldap\LdapUserManualResource;
use Filament\Resources\Pages\Page;

class ListLdapUserManuals extends Page
{
    protected static string $resource = LdapUserManualResource::class;

    protected string $view = 'filament.resources.ldap.ldap-user-manual-resource.pages.list-ldap-user-manuals';

    public function getTitle(): string
    {
        return 'User Manual';
    }
}
