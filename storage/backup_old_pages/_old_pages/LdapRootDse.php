<?php

declare(strict_types=1);

namespace App\Filament\Clusters\AdminManagement\Pages;

use App\Filament\Clusters\AdminManagement;
use App\Services\Ldap\LdapSchemaService;
use BackedEnum;
use Filament\Pages\Page;
use Throwable;

class LdapRootDse extends Page
{
    protected static ?string $cluster = AdminManagement::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';

    protected ?string $heading = '';

    protected string $view = 'filament.admin-management.pages.ldap-root-dse';

    public array $rootDse = [];

    public ?string $errorMessage = null;

    public static function getNavigationLabel(): string
    {
        return 'RootDSE';
    }

    public function getTitle(): string
    {
        return 'RootDSE';
    }

    public function mount(LdapSchemaService $ldapSchemaService): void
    {
        $this->loadRootDse($ldapSchemaService);
    }

    public function refreshData(LdapSchemaService $ldapSchemaService): void
    {
        $this->loadRootDse($ldapSchemaService);
    }

    public function flattenAttributes(array $attributes): array
    {
        $rows = [];

        foreach ($attributes as $attributeName => $values) {
            if (! is_array($values) || $values === []) {
                $rows[] = [
                    'attribute' => $attributeName,
                    'value' => '-',
                ];
                continue;
            }

            foreach ($values as $value) {
                $rows[] = [
                    'attribute' => $attributeName,
                    'value' => (string) $value,
                ];
            }
        }

        return $rows;
    }

    private function loadRootDse(LdapSchemaService $ldapSchemaService): void
    {
        try {
            $this->errorMessage = null;
            $this->rootDse = $ldapSchemaService->getRootDse();
        } catch (Throwable $throwable) {
            $this->rootDse = [];
            $this->errorMessage = $throwable->getMessage();
        }
    }
}
