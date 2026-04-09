<?php

namespace App\Services\Ldap;

use App\Models\LdapSchemaEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LdapSchemaBrowserService
{
    protected ?array $schemaCache = null;

    public function getAll(?string $search = null, ?string $type = null): Collection
    {
        $schema = $this->readSchema();

        $entries = collect()
            ->merge($this->mapSchemaValuesFromSchema($schema, 'objectClasses', 'objectClass'))
            ->merge($this->mapSchemaValuesFromSchema($schema, 'attributeTypes', 'attributeType'))
            ->merge($this->mapSchemaValuesFromSchema($schema, 'matchingRules', 'matchingRule'))
            ->merge($this->mapSchemaValuesFromSchema($schema, 'matchingRuleUse', 'matchingRuleUse'))
            ->merge($this->mapSchemaValuesFromSchema($schema, 'ldapSyntaxes', 'ldapSyntax'));

        if ($type) {
            $entries = $entries->filter(fn ($entry) => $entry->type === $type);
        }

        if ($search) {
            $needle = mb_strtolower($search);

            $entries = $entries->filter(function ($entry) use ($needle) {
                return str_contains(mb_strtolower((string) $entry->name), $needle)
                    || str_contains(mb_strtolower((string) $entry->oid), $needle)
                    || str_contains(mb_strtolower((string) $entry->description), $needle)
                    || str_contains(mb_strtolower((string) $entry->sup), $needle)
                    || str_contains(mb_strtolower((string) $entry->raw), $needle);
            });
        }

        return $entries->values();
    }

    public function findById(string $id): ?LdapSchemaEntry
    {
        return $this->getAll()->first(fn ($entry) => $entry->id === $id);
    }

    public function getObjectClasses(): Collection
    {
        return $this->mapSchemaValuesFromSchema($this->readSchema(), 'objectClasses', 'objectClass');
    }

    public function getAttributeTypes(): Collection
    {
        return $this->mapSchemaValuesFromSchema($this->readSchema(), 'attributeTypes', 'attributeType');
    }

    public function getMatchingRules(): Collection
    {
        return $this->mapSchemaValuesFromSchema($this->readSchema(), 'matchingRules', 'matchingRule');
    }

    public function getMatchingRuleUse(): Collection
    {
        return $this->mapSchemaValuesFromSchema($this->readSchema(), 'matchingRuleUse', 'matchingRuleUse');
    }

    public function getSyntaxes(): Collection
    {
        return $this->mapSchemaValuesFromSchema($this->readSchema(), 'ldapSyntaxes', 'ldapSyntax');
    }

    protected function mapSchemaValuesFromSchema(array $schema, string $attribute, string $type): Collection
    {
        $values = $schema[$attribute] ?? [];

        return collect($values)
            ->map(fn (string $raw) => $this->makeEntry($raw, $type))
            ->filter()
            ->values();
    }

    protected function readSchema(): array
    {
        if ($this->schemaCache !== null) {
            return $this->schemaCache;
        }

        $cacheKey = 'ldap_schema_browser_full';

        $this->schemaCache = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            if (! function_exists('ldap_connect')) {
                return [];
            }

            $host = env('LDAP_HOST', '127.0.0.1');
            $port = (int) env('LDAP_PORT', 389);

            $bindDn = env('LDAP_USERNAME') ?: env('LDAP_BIND_DN');
            $bindPassword = env('LDAP_PASSWORD') ?: env('LDAP_BIND_PASSWORD');

            $connection = @ldap_connect($host, $port);

            if (! $connection) {
                return [];
            }

            ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

            if (! @ldap_bind($connection, $bindDn, $bindPassword)) {
                return [];
            }

            $rootDseSearch = @ldap_read(
                $connection,
                '',
                '(objectClass=*)',
                ['subschemaSubentry']
            );

            $subschemaDn = 'cn=subschema';

            if ($rootDseSearch) {
                $rootEntries = @ldap_get_entries($connection, $rootDseSearch);

                if (
                    isset($rootEntries[0]['subschemasubentry'][0]) &&
                    filled($rootEntries[0]['subschemasubentry'][0])
                ) {
                    $subschemaDn = $rootEntries[0]['subschemasubentry'][0];
                }
            }

            $schemaSearch = @ldap_read(
                $connection,
                $subschemaDn,
                '(objectClass=*)',
                [
                    'objectClasses',
                    'attributeTypes',
                    'matchingRules',
                    'matchingRuleUse',
                    'ldapSyntaxes',
                ]
            );

            if (! $schemaSearch) {
                return [];
            }

            $entries = @ldap_get_entries($connection, $schemaSearch);

            if (! isset($entries[0])) {
                return [];
            }

            return [
                'objectClasses'   => $this->extractValues($entries[0], 'objectclasses'),
                'attributeTypes'  => $this->extractValues($entries[0], 'attributetypes'),
                'matchingRules'   => $this->extractValues($entries[0], 'matchingrules'),
                'matchingRuleUse' => $this->extractValues($entries[0], 'matchingruleuse'),
                'ldapSyntaxes'    => $this->extractValues($entries[0], 'ldapsyntaxes'),
            ];
        });

        return $this->schemaCache;
    }

    protected function extractValues(array $entry, string $key): array
    {
        if (! isset($entry[$key]) || ! isset($entry[$key]['count'])) {
            return [];
        }

        $result = [];

        for ($i = 0; $i < $entry[$key]['count']; $i++) {
            if (isset($entry[$key][$i])) {
                $result[] = $entry[$key][$i];
            }
        }

        return $result;
    }

    protected function makeEntry(string $raw, string $type): ?LdapSchemaEntry
    {
        $name = $this->extractName($raw);
        $oid = $this->extractOid($raw);

        if (blank($name) && blank($oid)) {
            return null;
        }

        $entry = new LdapSchemaEntry();

        $entry->id = md5($type . '|' . $raw);
        $entry->type = $type;
        $entry->name = $name;
        $entry->oid = $oid;
        $entry->description = $this->extractDescription($raw);
        $entry->sup = $this->extractSup($raw);
        $entry->raw = $raw;

        return $entry;
    }

    protected function extractName(string $raw): ?string
    {
        if (preg_match("/NAME\\s+'([^']+)'/i", $raw, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match("/NAME\\s+\\(\\s*'([^']+)'/i", $raw, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    protected function extractOid(string $raw): ?string
    {
        if (preg_match('/\\(\\s*([0-9.]+)/', $raw, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    protected function extractDescription(string $raw): ?string
    {
        if (preg_match("/DESC\\s+'([^']+)'/i", $raw, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    protected function extractSup(string $raw): ?string
    {
        if (preg_match('/SUP\\s+([a-zA-Z0-9_-]+)/i', $raw, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    public function clearCache(): void
    {
        $this->schemaCache = null;
        Cache::forget('ldap_schema_browser_full');
    }
}
