<?php

namespace App\Filament\Resources\Ldap\LdapBackupResource\Pages;

use App\Filament\Resources\Ldap\LdapBackupResource;
use App\Services\Ldap\LdapBackupService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLdapBackup extends CreateRecord
{
    protected static string $resource = LdapBackupResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = Auth::user()?->email ?: Auth::user()?->name ?: 'system';
        $data['status'] = 'pending';

        return $data;
    }

    protected function afterCreate(): void
    {
        app(LdapBackupService::class)->run($this->record);
    }
}
