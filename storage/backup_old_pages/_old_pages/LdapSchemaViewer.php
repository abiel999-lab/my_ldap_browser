<?php

declare(strict_types=1);

namespace App\Filament\Clusters\AdminManagement\Pages;

use App\Filament\Clusters\AdminManagement;
use App\Services\Ldap\LdapSchemaService;
use BackedEnum;
use Filament\Pages\Page;
use Throwable;

class LdapSchemaViewer extends Page
{
    protected static ?string $cluster = AdminManagement::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected ?string $heading = '';

    protected string $view = 'filament.admin-management.pages.ldap-schema-viewer';

    public string $activeTab = 'objectClasses';

    public string $search = '';

    public string $selectedName = '';

    public array $objectClasses = [];

    public array $attributeTypes = [];

    public ?string $errorMessage = null;

    public static function getNavigationLabel(): string
    {
        return 'Schema Viewer';
    }

    public function getTitle(): string
    {
        return 'Schema Viewer';
    }

    public function mount(LdapSchemaService $ldapSchemaService): void
    {
        $this->loadSchema($ldapSchemaService);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['objectClasses', 'attributeTypes'], true)) {
            return;
        }

        $this->activeTab = $tab;

        $items = $this->getCurrentItems();
        $this->selectedName = $items[0]['name'] ?? '';
    }

    public function selectItem(string $name): void
    {
        $this->selectedName = $name;
    }

    public function refreshData(LdapSchemaService $ldapSchemaService): void
    {
        $this->loadSchema($ldapSchemaService);
    }

    public function getCurrentItems(): array
    {
        return $this->activeTab === 'objectClasses'
            ? $this->objectClasses
            : $this->attributeTypes;
    }

    public function getFilteredItems(): array
    {
        $items = $this->getCurrentItems();

        if ($this->search === '') {
            return $items;
        }

        $query = mb_strtolower($this->search);

        return array_values(array_filter($items, function (array $item) use ($query): bool {
            $name = mb_strtolower((string) ($item['name'] ?? ''));
            $definition = mb_strtolower((string) ($item['definition'] ?? ''));

            return str_contains($name, $query) || str_contains($definition, $query);
        }));
    }

    public function getSelectedItem(): ?array
    {
        $items = $this->getFilteredItems();

        if ($items === []) {
            return null;
        }

        foreach ($items as $item) {
            if (($item['name'] ?? '') === $this->selectedName) {
                return $item;
            }
        }

        return $items[0] ?? null;
    }

    public function getSelectedParsedItem(): ?array
    {
        $item = $this->getSelectedItem();

        if (! $item) {
            return null;
        }

        $definition = (string) ($item['definition'] ?? '');

        if ($this->activeTab === 'objectClasses') {
            return [
                'name' => $item['name'] ?? null,
                'type' => $this->extractObjectClassType($definition),
                'sup' => $this->extractSingleOrList($definition, 'SUP'),
                'must' => $this->extractSingleOrList($definition, 'MUST'),
                'may' => $this->extractSingleOrList($definition, 'MAY'),
                'definition' => $definition,
            ];
        }

        return [
            'name' => $item['name'] ?? null,
            'syntax' => $this->extractToken($definition, 'SYNTAX'),
            'equality' => $this->extractToken($definition, 'EQUALITY'),
            'substr' => $this->extractToken($definition, 'SUBSTR'),
            'singleValue' => str_contains(strtoupper($definition), 'SINGLE-VALUE'),
            'usage' => $this->extractToken($definition, 'USAGE'),
            'definition' => $definition,
        ];
    }

    private function loadSchema(LdapSchemaService $ldapSchemaService): void
    {
        try {
            $this->errorMessage = null;
            $this->objectClasses = $ldapSchemaService->getObjectClasses();
            $this->attributeTypes = $ldapSchemaService->getAttributeTypes();

            $items = $this->getCurrentItems();
            $this->selectedName = $items[0]['name'] ?? '';
        } catch (Throwable $throwable) {
            $this->objectClasses = [];
            $this->attributeTypes = [];
            $this->selectedName = '';
            $this->errorMessage = $throwable->getMessage();
        }
    }

    private function extractObjectClassType(string $definition): ?string
    {
        if (preg_match('/\b(STRUCTURAL|AUXILIARY|ABSTRACT)\b/i', $definition, $matches) === 1) {
            return strtoupper($matches[1]);
        }

        return null;
    }

    private function extractToken(string $definition, string $keyword): ?string
    {
        if (preg_match('/\b' . preg_quote($keyword, '/') . '\s+([^\s\)]+)/i', $definition, $matches) === 1) {
            return trim($matches[1], "'\"");
        }

        return null;
    }

    private function extractSingleOrList(string $definition, string $keyword): array
    {
        if (preg_match('/\b' . preg_quote($keyword, '/') . '\s+\((.*?)\)/i', $definition, $matches) === 1) {
            $content = $matches[1];
            $parts = preg_split('/\$/', $content) ?: [];

            return array_values(array_filter(array_map(function (string $item): string {
                return trim(trim($item), " '\"");
            }, $parts)));
        }

        if (preg_match('/\b' . preg_quote($keyword, '/') . '\s+([^\s\)]+)/i', $definition, $matches) === 1) {
            return [trim($matches[1], "'\"")];
        }

        return [];
    }
}
