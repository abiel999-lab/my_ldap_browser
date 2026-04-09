<?php

namespace App\Filament\Resources\LdapUnitViews\Pages;

use App\Filament\Resources\LdapUnitViews\LdapUnitViewResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLdapUnitView extends EditRecord
{
    protected static string $resource = LdapUnitViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
