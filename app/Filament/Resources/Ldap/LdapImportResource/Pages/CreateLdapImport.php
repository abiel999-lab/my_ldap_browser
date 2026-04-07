<?php

namespace App\Filament\Resources\Ldap\LdapImportResource\Pages;

use App\Filament\Resources\Ldap\LdapImportResource;
use App\Services\Ldap\LdapImportService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLdapImport extends CreateRecord
{
    protected static string $resource = LdapImportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = Auth::user()?->email ?: Auth::user()?->name ?: 'system';
        $data['status'] = 'pending';
        $data['original_name'] = basename((string) $data['file_path']);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(LdapImportService::class)->run($this->record);
    }
}
