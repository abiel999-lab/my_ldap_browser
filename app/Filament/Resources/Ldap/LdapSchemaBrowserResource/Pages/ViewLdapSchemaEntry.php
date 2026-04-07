<?php

namespace App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages;

use App\Filament\Resources\Ldap\LdapSchemaBrowserResource;
use App\Services\Ldap\LdapSchemaBrowserService;
use Filament\Resources\Pages\Page;

class ViewLdapSchemaEntry extends Page
{
    protected static string $resource = LdapSchemaBrowserResource::class;

    protected string $view = 'filament.resources.ldap.ldap-schema-browser-resource.pages.view-ldap-schema-entry';

    public string $recordKey;

    public ?object $record = null;

    public function mount(string $recordKey): void
    {
        $this->recordKey = $recordKey;
        $this->record = app(LdapSchemaBrowserService::class)->findById($recordKey);

        abort_if(blank($this->record), 404);
    }

    public function getTitle(): string
    {
        return (string) ($this->record->name ?? 'Schema Detail');
    }
}
