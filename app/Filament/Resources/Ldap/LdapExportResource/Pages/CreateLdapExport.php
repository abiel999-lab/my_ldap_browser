<?php

namespace App\Filament\Resources\Ldap\LdapExportResource\Pages;

use App\Filament\Resources\Ldap\LdapExportResource;
use App\Services\Ldap\LdapExportService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLdapExport extends CreateRecord
{
    protected static string $resource = LdapExportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = Auth::user()?->email ?: Auth::user()?->name ?: 'system';
        $data['status'] = 'pending';

        return $data;
    }

    protected function afterCreate(): void
    {
        app(LdapExportService::class)->run($this->record);
    }
}
