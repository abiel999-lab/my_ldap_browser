<?php

namespace App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages\Concerns;

use App\Services\Ldap\LdapSchemaBrowserService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait HandlesSchemaTypeTable
{
    abstract protected function getSchemaType(): string;

    public function getTableRecords(): EloquentCollection | LengthAwarePaginator | Collection
    {
        $service = app(LdapSchemaBrowserService::class);

        return $service->getAll($this->getTableSearch(), $this->getSchemaType());
    }
}
