<?php

declare(strict_types=1);

namespace App\Filament\Clusters\AdminManagement\Pages;

use App\Filament\Clusters\AdminManagement;
use App\Services\Ldap\LdapEntryFormatterService;
use App\Services\Ldap\LdapEntryService;
use BackedEnum;
use Filament\Pages\Page;
use Throwable;

class LdapSearch extends Page
{
    protected static ?string $cluster = AdminManagement::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected ?string $heading = '';

    protected string $view = 'filament.admin-management.pages.ldap-search';

    public string $baseDn = '';

    public string $filter = '(objectClass=*)';

    public int $sizeLimit = 100;

    public array $results = [];

    public ?string $errorMessage = null;

    public static function getNavigationLabel(): string
    {
        return 'LDAP Search';
    }

    public function getTitle(): string
    {
        return 'LDAP Search';
    }

    public function mount(): void
    {
        $this->baseDn = (string) config('ldap_admin.base_dn');
    }

    public function runSearch(
        LdapEntryService $ldapEntryService,
        LdapEntryFormatterService $ldapEntryFormatterService
    ): void {
        try {
            $this->errorMessage = null;

            $entries = $ldapEntryService->search(
                $this->baseDn,
                $this->filter !== '' ? $this->filter : '(objectClass=*)',
                $this->sizeLimit > 0 ? $this->sizeLimit : 100,
            );

            $this->results = $ldapEntryFormatterService->sanitizeEntries($entries);
        } catch (Throwable $throwable) {
            $this->results = [];
            $this->errorMessage = $throwable->getMessage();
        }
    }

    public function openInExplorer(string $encodedDn): void
    {
        $dn = base64_decode($encodedDn, true);

        if (! is_string($dn) || $dn === '') {
            return;
        }

        $this->redirect(LdapExplorer::getUrl([
            'selected' => base64_encode($dn),
        ]));
    }

    public function shortObjectClass(array $entry): string
    {
        $classes = $entry['objectClasses'] ?? [];

        if ($classes === []) {
            return '-';
        }

        return implode(', ', $classes);
    }

    public function encodeDn(string $dn): string
    {
        return base64_encode($dn);
    }
}
