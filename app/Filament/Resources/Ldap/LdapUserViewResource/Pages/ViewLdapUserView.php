<?php

namespace App\Filament\Resources\Ldap\LdapUserViewResource\Pages;

use App\Filament\Resources\Ldap\LdapUserViewResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLdapUserView extends ViewRecord
{
    protected static string $resource = LdapUserViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->url(LdapUserViewResource::getUrl('index')),
        ];
    }
}
