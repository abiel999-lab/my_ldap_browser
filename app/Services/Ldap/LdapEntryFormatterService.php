<?php

declare(strict_types=1);

namespace App\Services\Ldap;

class LdapEntryFormatterService
{
    public function sanitizeEntry(array $entry): array
    {
        $hiddenAttributes = $this->normalizeList(config('ldap_admin.hidden_attributes', []));
        $operationalAttributes = $this->normalizeList(config('ldap_admin.operational_attributes', []));

        $attributes = $entry['attributes'] ?? [];

        $filteredAttributes = [];
        $filteredOperationalAttributes = [];

        foreach ($attributes as $attributeName => $values) {
            $attributeKey = mb_strtolower((string) $attributeName);

            if (in_array($attributeKey, $hiddenAttributes, true)) {
                continue;
            }

            if (in_array($attributeKey, $operationalAttributes, true)) {
                $filteredOperationalAttributes[$attributeName] = $values;
                continue;
            }

            $filteredAttributes[$attributeName] = $values;
        }

        return [
            'dn' => $entry['dn'] ?? null,
            'rdn' => $entry['rdn'] ?? null,
            'label' => $entry['label'] ?? null,
            'isContainer' => (bool) ($entry['isContainer'] ?? false),
            'objectClasses' => $entry['objectClasses'] ?? [],
            'attributes' => $filteredAttributes,
            'operationalAttributes' => $filteredOperationalAttributes,
        ];
    }

    public function sanitizeEntries(array $entries): array
    {
        return array_map(
            fn (array $entry): array => $this->sanitizeEntry($entry),
            $entries
        );
    }

    private function normalizeList(array $items): array
    {
        return array_map(
            fn ($item): string => mb_strtolower((string) $item),
            $items
        );
    }
}