<?php

namespace App\Filament\Resources\LdapAppRoleViews\Pages;

use App\Filament\Resources\LdapAppRoleViews\LdapAppRoleViewResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLdapAppRoleView extends EditRecord
{
    protected static string $resource = LdapAppRoleViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
