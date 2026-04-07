<?php

namespace App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages;

use App\Filament\Resources\Ldap\LdapSchemaBrowserResource;
use App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages\Concerns\HandlesSchemaTypeTable;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLdapSchemaSyntaxes extends ListRecords
{
    use HandlesSchemaTypeTable;

    protected static string $resource = LdapSchemaBrowserResource::class;

    protected function getSchemaType(): string
    {
        return 'ldapSyntax';
    }

    public function getTitle(): string
    {
        return 'Syntaxes';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->url(LdapSchemaBrowserResource::getUrl('index')),
        ];
    }
}
