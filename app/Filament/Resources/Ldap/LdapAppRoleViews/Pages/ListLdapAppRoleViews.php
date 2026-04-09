<?php

namespace App\Filament\Resources\Ldap\LdapAppRoleViewResource\Pages;

use App\Filament\Resources\Ldap\LdapAppRoleViewResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLdapAppRoleViews extends ListRecords
{
    protected static string $resource = LdapAppRoleViewResource::class;

    public ?string $app = null;

    public function mount(): void
    {
        $this->app = request()->query('app');
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->app) {
            $query->where('app_cn', $this->app);
        }

        return $query;
    }

    public function getTitle(): string
    {
        return $this->app
            ? 'App Roles - ' . $this->app
            : 'App Roles';
    }
}
