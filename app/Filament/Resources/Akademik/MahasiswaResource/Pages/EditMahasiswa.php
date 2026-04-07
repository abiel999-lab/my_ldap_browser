<?php

namespace App\Filament\Resources\Akademik\MahasiswaResource\Pages;

use App\Filament\Resources\Akademik\MahasiswaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMahasiswa extends EditRecord
{
    protected static string $resource = MahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
