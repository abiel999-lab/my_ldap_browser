<?php

declare(strict_types=1);

namespace App\Filament\Clusters\AdminManagement\Pages;

use App\Filament\Clusters\AdminManagement;
use App\Services\Ldap\LdapEntryFormatterService;
use App\Services\Ldap\LdapEntryService;
use BackedEnum;
use Filament\Pages\Page;
use Throwable;

class LdapExplorer extends Page
{
    protected static ?string $cluster = AdminManagement::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected ?string $heading = '';

    protected string $view = 'filament.admin-management.pages.ldap-explorer';

    public string $baseDn = '';

    public string $selectedDn = '';

    public string $browserDn = '';

    public array $selectedEntry = [];

    public array $loadedChildren = [];

    public array $expandedDns = [];

    public string $activeTab = 'attributes';

    public string $centerView = 'list';

    public string $quickFilter = '';

    public ?string $errorMessage = null;

    public static function getNavigationLabel(): string
    {
        return 'LDAP Explorer';
    }

    public function getTitle(): string
    {
        return 'LDAP Explorer';
    }

    public function mount(
        LdapEntryService $ldapEntryService,
        LdapEntryFormatterService $ldapEntryFormatterService
    ): void {
        $this->baseDn = (string) config('ldap_admin.base_dn');
        $this->selectedDn = $this->baseDn;
        $this->browserDn = $this->baseDn;
        $this->expandedDns[$this->baseDn] = true;

        $requestedSelected = request()->query('selected');

        if (is_string($requestedSelected) && $requestedSelected !== '') {
            $decoded = base64_decode($requestedSelected, true);

            if (is_string($decoded) && $decoded !== '') {
                $this->selectedDn = $decoded;
                $this->browserDn = $this->getParentDn($decoded) ?: $this->baseDn;
                $this->expandPathToDn($this->browserDn, $ldapEntryService);
            } else {
                $this->loadChildrenByDn($this->baseDn, $ldapEntryService);
            }
        } else {
            $this->loadChildrenByDn($this->baseDn, $ldapEntryService);
        }

        if (! isset($this->loadedChildren[$this->browserDn])) {
            $this->loadChildrenByDn($this->browserDn, $ldapEntryService);
        }

        $this->loadSelectedEntry($this->selectedDn, $ldapEntryService, $ldapEntryFormatterService);
    }

    public function refreshExplorer(
        LdapEntryService $ldapEntryService,
        LdapEntryFormatterService $ldapEntryFormatterService
    ): void {
        $this->errorMessage = null;
        $this->loadedChildren = [];
        $this->expandedDns = [
            $this->baseDn => true,
        ];

        $this->expandPathToDn($this->browserDn ?: $this->baseDn, $ldapEntryService);
        $this->loadSelectedEntry($this->selectedDn ?: $this->baseDn, $ldapEntryService, $ldapEntryFormatterService);
    }

    public function toggleNode(string $encodedDn, LdapEntryService $ldapEntryService): void
    {
        $dn = $this->decodeDn($encodedDn);

        if ($dn === '') {
            return;
        }

        if (isset($this->expandedDns[$dn])) {
            unset($this->expandedDns[$dn]);
            return;
        }

        $this->expandedDns[$dn] = true;

        if (! array_key_exists($dn, $this->loadedChildren)) {
            $this->loadChildrenByDn($dn, $ldapEntryService);
        }
    }

    public function selectDn(
        string $encodedDn,
        LdapEntryService $ldapEntryService,
        LdapEntryFormatterService $ldapEntryFormatterService
    ): void {
        $dn = $this->decodeDn($encodedDn);

        if ($dn === '') {
            return;
        }

        $this->selectedDn = $dn;

        $this->loadSelectedEntry($dn, $ldapEntryService, $ldapEntryFormatterService);

        $isContainer = (bool) ($this->selectedEntry['isContainer'] ?? false);

        if ($isContainer) {
            $this->browserDn = $dn;

            if (! isset($this->loadedChildren[$dn])) {
                $this->loadChildrenByDn($dn, $ldapEntryService);
            }
        } else {
            $this->browserDn = $this->getParentDn($dn) ?: $this->baseDn;

            if (! isset($this->loadedChildren[$this->browserDn])) {
                $this->loadChildrenByDn($this->browserDn, $ldapEntryService);
            }
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['attributes', 'operational', 'raw'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function setCenterView(string $view): void
    {
        if (in_array($view, ['list', 'grid'], true)) {
            $this->centerView = $view;
        }
    }

    public function getRootNode(): array
    {
        return [
            'dn' => $this->baseDn,
            'rdn' => $this->extractRdn($this->baseDn),
            'label' => $this->baseDn,
            'isContainer' => true,
            'objectClasses' => ['top'],
            'attributes' => [],
        ];
    }

    public function getLoadedChildren(string $dn): array
    {
        return $this->loadedChildren[$dn] ?? [];
    }

    public function isExpanded(string $dn): bool
    {
        return isset($this->expandedDns[$dn]);
    }

    public function getBrowserChildren(): array
    {
        $children = $this->loadedChildren[$this->browserDn] ?? [];

        if ($this->quickFilter !== '') {
            $query = mb_strtolower($this->quickFilter);

            $children = array_values(array_filter($children, function (array $child) use ($query): bool {
                $label = mb_strtolower((string) ($child['label'] ?? ''));
                $rdn = mb_strtolower((string) ($child['rdn'] ?? ''));
                $dn = mb_strtolower((string) ($child['dn'] ?? ''));
                $classes = implode(' ', array_map('strtolower', $child['objectClasses'] ?? []));

                return str_contains($label, $query)
                    || str_contains($rdn, $query)
                    || str_contains($dn, $query)
                    || str_contains($classes, $query);
            }));
        }

        usort($children, function (array $a, array $b): int {
            $aContainer = (bool) ($a['isContainer'] ?? false);
            $bContainer = (bool) ($b['isContainer'] ?? false);

            if ($aContainer !== $bContainer) {
                return $aContainer ? -1 : 1;
            }

            return strcmp(
                mb_strtolower((string) ($a['label'] ?? $a['rdn'] ?? '')),
                mb_strtolower((string) ($b['label'] ?? $b['rdn'] ?? ''))
            );
        });

        return $children;
    }

    public function getBrowserPath(): string
    {
        return $this->browserDn !== '' ? $this->browserDn : $this->baseDn;
    }

    public function getBrowserItemCount(): int
    {
        return count($this->getBrowserChildren());
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

    public function encodeDn(string $dn): string
    {
        return base64_encode($dn);
    }

    private function loadChildrenByDn(string $dn, LdapEntryService $ldapEntryService): void
    {
        try {
            $this->errorMessage = null;
            $this->loadedChildren[$dn] = $ldapEntryService->listChildren($dn);
        } catch (Throwable $throwable) {
            $this->errorMessage = $throwable->getMessage();
            $this->loadedChildren[$dn] = [];
        }
    }

    private function loadSelectedEntry(
        string $dn,
        LdapEntryService $ldapEntryService,
        LdapEntryFormatterService $ldapEntryFormatterService
    ): void {
        try {
            $this->errorMessage = null;

            $entry = $ldapEntryService->getEntry($dn);
            $this->selectedEntry = $ldapEntryFormatterService->sanitizeEntry($entry);
        } catch (Throwable $throwable) {
            $this->errorMessage = $throwable->getMessage();
            $this->selectedEntry = [];
        }
    }

    private function decodeDn(string $encodedDn): string
    {
        $decoded = base64_decode($encodedDn, true);

        return is_string($decoded) ? $decoded : '';
    }

    private function extractRdn(string $dn): string
    {
        $parts = ldap_explode_dn($dn, 0);

        if (! is_array($parts) || ! isset($parts[0])) {
            return $dn;
        }

        return (string) $parts[0];
    }

    private function getParentDn(string $dn): string
    {
        $parts = explode(',', $dn, 2);

        return $parts[1] ?? '';
    }

    private function expandPathToDn(string $targetDn, LdapEntryService $ldapEntryService): void
    {
        $path = [];
        $cursor = $targetDn;

        while ($cursor !== '') {
            $path[] = $cursor;

            if (mb_strtolower($cursor) === mb_strtolower($this->baseDn)) {
                break;
            }

            $cursor = $this->getParentDn($cursor);
        }

        $path = array_reverse($path);

        foreach ($path as $dn) {
            $this->expandedDns[$dn] = true;

            if (! isset($this->loadedChildren[$dn])) {
                $this->loadChildrenByDn($dn, $ldapEntryService);
            }
        }
    }
}
