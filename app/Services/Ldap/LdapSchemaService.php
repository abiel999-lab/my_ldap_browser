<?php

declare(strict_types=1);

namespace App\Services\Ldap;

use RuntimeException;

class LdapSchemaService
{
    public function __construct(
        private readonly LdapConnectionService $ldapConnectionService
    ) {
    }

    public function getRootDse(): array
    {
        return $this->ldapConnectionService->getRootDse();
    }

    public function getSchemaDn(): string
    {
        $rootDse = $this->getRootDse();
        $attributes = $rootDse['attributes'] ?? [];
        $schemaDn = $attributes['subschemasubentry'][0] ?? null;

        if (! $schemaDn) {
            throw new RuntimeException('subschemaSubentry not found in RootDSE.');
        }

        return (string) $schemaDn;
    }

    public function getSchemaEntry(): array
    {
        $schemaDn = $this->getSchemaDn();
        $connection = $this->ldapConnectionService->connect();

        $search = @ldap_read(
            $connection,
            $schemaDn,
            '(objectClass=*)',
            ['attributeTypes', 'objectClasses', 'ldapSyntaxes', 'matchingRules', 'matchingRuleUse']
        );

        if (! $search) {
            throw new RuntimeException('Failed to read LDAP schema entry: '.$this->ldapConnectionService->getLastError($connection));
        }

        $entries = ldap_get_entries($connection, $search);
        $normalizedEntries = $this->ldapConnectionService->normalizeEntries($entries);

        if (! isset($normalizedEntries[0])) {
            throw new RuntimeException('LDAP schema entry not found.');
        }

        return $normalizedEntries[0];
    }

    public function getObjectClasses(): array
    {
        $schemaEntry = $this->getSchemaEntry();
        $definitions = $schemaEntry['attributes']['objectclasses'] ?? [];

        return array_map(fn (string $definition): array => [
            'name' => $this->extractSchemaName($definition),
            'definition' => $definition,
        ], $definitions);
    }

    public function getAttributeTypes(): array
    {
        $schemaEntry = $this->getSchemaEntry();
        $definitions = $schemaEntry['attributes']['attributetypes'] ?? [];

        return array_map(fn (string $definition): array => [
            'name' => $this->extractSchemaName($definition),
            'definition' => $definition,
        ], $definitions);
    }

    private function extractSchemaName(string $definition): ?string
    {
        if (preg_match("/NAME\\s+'([^']+)'/i", $definition, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match("/NAME\\s+\\(\\s*'([^']+)'/i", $definition, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}