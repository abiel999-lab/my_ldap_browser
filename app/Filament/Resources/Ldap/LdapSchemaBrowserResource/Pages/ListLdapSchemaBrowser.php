<?php

namespace App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages;

use App\Filament\Resources\Ldap\LdapSchemaBrowserResource;
use Filament\Resources\Pages\Page;

class ListLdapSchemaBrowser extends Page
{
    protected static string $resource = LdapSchemaBrowserResource::class;

    protected string $view = 'filament.resources.ldap.ldap-schema-browser-resource.pages.list-ldap-schema-browser';

    public function getTitle(): string
    {
        return 'Schema Browser';
    }
}
