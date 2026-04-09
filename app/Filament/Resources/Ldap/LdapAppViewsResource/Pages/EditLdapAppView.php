<?php

namespace App\Filament\Resources\LdapAppViews\Pages;

use App\Filament\Resources\LdapAppViews\LdapAppViewResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLdapAppView extends EditRecord
{
    protected static string $resource = LdapAppViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
