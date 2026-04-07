<?php

namespace App\Filament\Resources\Admin\LoginAsResource\Pages;

use App\Filament\Resources\Admin\LoginAsResource;
use Filament\Resources\Pages\ManageRecords;

class ManageLoginAs extends ManageRecords
{
    protected static string $resource = LoginAsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
