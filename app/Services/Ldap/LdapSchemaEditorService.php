<?php

namespace App\Services\Ldap;

use App\Models\LdapEditableAttributeType;
use App\Models\LdapEditableObjectClass;
use RuntimeException;

class LdapSchemaEditorService
{
    public function __construct(
        protected LdapConnectionService $connectionService,
    ) {
    }

    public function getEditableSchemaDn(): string
    {
        $dn = trim((string) env('LDAP_SCHEMA_EDIT_DN', ''));

        if ($dn === '') {
            throw new RuntimeException('LDAP_SCHEMA_EDIT_DN belum di-set di .env');
        }

        return $dn;
    }

    protected function getObjectClassAttributeName(): string
    {
        $dn = strtolower($this->getEditableSchemaDn());

        return str_contains($dn, 'cn=config')
            ? 'olcObjectClasses'
            : 'objectClasses';
    }

    protected function getAttributeTypeAttributeName(): string
    {
        $dn = strtolower($this->getEditableSchemaDn());

        return str_contains($dn, 'cn=config')
            ? 'olcAttributeTypes'
            : 'attributeTypes';
    }

    public function syncSnapshots(): void
    {
        $entry = $this->getEditableSchemaEntry();

        $objectClasses = $this->extractValues(
            $entry,
            strtolower($this->getObjectClassAttributeName())
        );

        $attributeTypes = $this->extractValues(
            $entry,
            strtolower($this->getAttributeTypeAttributeName())
        );

        $activeObjectClassRaws = [];
        $activeAttributeTypeRaws = [];

        foreach ($objectClasses as $raw) {
            $parsed = $this->parseObjectClass($raw);

            LdapEditableObjectClass::updateOrCreate(
                ['raw_definition' => $raw],
                [
                    'oid' => $parsed['oid'] ?? '',
                    'primary_name' => $parsed['primary_name'] ?? '',
                    'aliases_text' => implode(', ', $parsed['aliases'] ?? []),
                    'description' => $parsed['description'] ?? null,
                    'sup_text' => implode(', ', $parsed['sup'] ?? []),
                    'class_type' => $parsed['class_type'] ?? 'STRUCTURAL',
                    'obsolete' => (bool) ($parsed['obsolete'] ?? false),
                    'must_text' => implode(', ', $parsed['must'] ?? []),
                    'may_text' => implode(', ', $parsed['may'] ?? []),
                ]
            );

            $activeObjectClassRaws[] = $raw;
        }

        foreach ($attributeTypes as $raw) {
            $parsed = $this->parseAttributeType($raw);

            LdapEditableAttributeType::updateOrCreate(
                ['raw_definition' => $raw],
                [
                    'oid' => $parsed['oid'] ?? '',
                    'primary_name' => $parsed['primary_name'] ?? '',
                    'aliases_text' => implode(', ', $parsed['aliases'] ?? []),
                    'description' => $parsed['description'] ?? null,
                    'sup' => $parsed['sup'] ?? null,
                    'equality' => $parsed['equality'] ?? null,
                    'ordering' => $parsed['ordering'] ?? null,
                    'substr' => $parsed['substr'] ?? null,
                    'syntax' => $parsed['syntax'] ?? null,
                    'usage' => $parsed['usage'] ?? null,
                    'single_value' => (bool) ($parsed['single_value'] ?? false),
                    'no_user_modification' => (bool) ($parsed['no_user_modification'] ?? false),
                    'obsolete' => (bool) ($parsed['obsolete'] ?? false),
                ]
            );

            $activeAttributeTypeRaws[] = $raw;
        }

        if (! empty($activeObjectClassRaws)) {
            LdapEditableObjectClass::query()
                ->whereNotIn('raw_definition', $activeObjectClassRaws)
                ->delete();
        } else {
            LdapEditableObjectClass::query()->delete();
        }

        if (! empty($activeAttributeTypeRaws)) {
            LdapEditableAttributeType::query()
                ->whereNotIn('raw_definition', $activeAttributeTypeRaws)
                ->delete();
        } else {
            LdapEditableAttributeType::query()->delete();
        }
    }

    public function addObjectClass(array $data): void
    {
        $definition = $this->buildObjectClassDefinition($data);
        $this->modAdd($this->getObjectClassAttributeName(), $definition);
        $this->syncSnapshots();
    }

    public function updateObjectClass(LdapEditableObjectClass $record, array $data): void
    {
        $newDefinition = $this->buildObjectClassDefinition($data);
        $this->replaceOneValue($this->getObjectClassAttributeName(), $record->raw_definition, $newDefinition);
        $this->syncSnapshots();
    }

    public function deleteObjectClass(LdapEditableObjectClass $record): void
    {
        $this->modDelete($this->getObjectClassAttributeName(), $record->raw_definition);
        $this->syncSnapshots();
    }

    public function addAttributeType(array $data): void
    {
        $definition = $this->buildAttributeTypeDefinition($data);
        $this->modAdd($this->getAttributeTypeAttributeName(), $definition);
        $this->syncSnapshots();
    }

    public function updateAttributeType(LdapEditableAttributeType $record, array $data): void
    {
        $newDefinition = $this->buildAttributeTypeDefinition($data);
        $this->replaceOneValue($this->getAttributeTypeAttributeName(), $record->raw_definition, $newDefinition);
        $this->syncSnapshots();
    }

    public function deleteAttributeType(LdapEditableAttributeType $record): void
    {
        $this->modDelete($this->getAttributeTypeAttributeName(), $record->raw_definition);
        $this->syncSnapshots();
    }

    protected function getEditableSchemaEntry(): array
    {
        $connection = $this->connectionService->connectSchema();
        $dn = $this->getEditableSchemaDn();

        $search = @ldap_read(
            $connection,
            $dn,
            '(objectClass=*)',
            [
                $this->getObjectClassAttributeName(),
                $this->getAttributeTypeAttributeName(),
            ]
        );

        if (! $search) {
            throw new RuntimeException('Gagal membaca editable schema entry.');
        }

        $entries = @ldap_get_entries($connection, $search);

        if (! isset($entries[0])) {
            throw new RuntimeException('Editable schema entry tidak ditemukan.');
        }

        return $entries[0];
    }

    protected function modAdd(string $attribute, string $value): void
    {
        $connection = $this->connectionService->connectSchema();
        $dn = $this->getEditableSchemaDn();

        $ok = @ldap_mod_add($connection, $dn, [
            $attribute => [$value],
        ]);

        if (! $ok) {
            throw new RuntimeException("Gagal menambah {$attribute}: " . ldap_error($connection));
        }
    }

    protected function modDelete(string $attribute, string $value): void
    {
        $connection = $this->connectionService->connectSchema();
        $dn = $this->getEditableSchemaDn();

        $ok = @ldap_mod_del($connection, $dn, [
            $attribute => [$value],
        ]);

        if (! $ok) {
            throw new RuntimeException("Gagal menghapus {$attribute}: " . ldap_error($connection));
        }
    }

    protected function replaceOneValue(string $attribute, string $oldValue, string $newValue): void
    {
        $entry = $this->getEditableSchemaEntry();

        $values = match (strtolower($attribute)) {
            'objectclasses', 'olcobjectclasses' => $this->extractValues($entry, strtolower($attribute)),
            'attributetypes', 'olcattributetypes' => $this->extractValues($entry, strtolower($attribute)),
            default => [],
        };

        $found = false;
        $newValues = [];

        $oldOid = $this->match('/\(\s*([0-9.]+)/', $oldValue);

        foreach ($values as $value) {
            $currentOid = $this->match('/\(\s*([0-9.]+)/', (string) $value);

            if (
                $oldOid !== null &&
                $currentOid !== null &&
                trim($currentOid) === trim($oldOid)
            ) {
                $newValues[] = $newValue;
                $found = true;
            } else {
                $newValues[] = $value;
            }
        }

        if (! $found) {
            throw new RuntimeException('Definition lama tidak ditemukan berdasarkan OID.');
        }

        $connection = $this->connectionService->connectSchema();
        $dn = $this->getEditableSchemaDn();

        $ok = @ldap_modify($connection, $dn, [
            $attribute => array_values($newValues),
        ]);

        if (! $ok) {
            throw new RuntimeException("Gagal update {$attribute}: " . ldap_error($connection));
        }
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

    protected function parseObjectClass(string $raw): array
    {
        $aliases = $this->extractNames($raw);

        return [
            'raw' => $raw,
            'oid' => $this->match('/\(\s*([0-9.]+)/', $raw),
            'primary_name' => $aliases[0] ?? '',
            'aliases' => $aliases,
            'description' => $this->match("/DESC\s+'([^']+)'/i", $raw),
            'sup' => $this->extractListAfterKeyword($raw, 'SUP'),
            'class_type' => $this->extractObjectClassType($raw),
            'obsolete' => preg_match('/\bOBSOLETE\b/i', $raw) === 1,
            'must' => $this->extractAttributeList($raw, 'MUST'),
            'may' => $this->extractAttributeList($raw, 'MAY'),
        ];
    }

    protected function parseAttributeType(string $raw): array
    {
        $aliases = $this->extractNames($raw);

        return [
            'raw' => $raw,
            'oid' => $this->match('/\(\s*([0-9.]+)/', $raw),
            'primary_name' => $aliases[0] ?? '',
            'aliases' => $aliases,
            'description' => $this->match("/DESC\s+'([^']+)'/i", $raw),
            'sup' => $this->match('/SUP\s+([a-zA-Z0-9._-]+)/i', $raw),
            'equality' => $this->match('/EQUALITY\s+([a-zA-Z0-9._-]+)/i', $raw),
            'ordering' => $this->match('/ORDERING\s+([a-zA-Z0-9._-]+)/i', $raw),
            'substr' => $this->match('/SUBSTR\s+([a-zA-Z0-9._-]+)/i', $raw),
            'syntax' => $this->match('/SYNTAX\s+([a-zA-Z0-9.{}-]+)/i', $raw),
            'usage' => $this->match('/USAGE\s+([a-zA-Z0-9_-]+)/i', $raw),
            'single_value' => preg_match('/\bSINGLE-VALUE\b/i', $raw) === 1,
            'no_user_modification' => preg_match('/\bNO-USER-MODIFICATION\b/i', $raw) === 1,
            'obsolete' => preg_match('/\bOBSOLETE\b/i', $raw) === 1,
        ];
    }

    protected function extractNames(string $raw): array
    {
        if (preg_match("/NAME\s+'([^']+)'/i", $raw, $matches) === 1) {
            return [$matches[1]];
        }

        if (preg_match("/NAME\s+\(\s*([^)]+)\)/i", $raw, $matches) === 1) {
            preg_match_all("/'([^']+)'/", $matches[1], $nameMatches);
            return $nameMatches[1] ?? [];
        }

        return [];
    }

    protected function extractObjectClassType(string $raw): string
    {
        if (preg_match('/\bABSTRACT\b/i', $raw)) {
            return 'ABSTRACT';
        }

        if (preg_match('/\bAUXILIARY\b/i', $raw)) {
            return 'AUXILIARY';
        }

        return 'STRUCTURAL';
    }

    protected function extractAttributeList(string $raw, string $keyword): array
    {
        if (preg_match('/' . preg_quote($keyword, '/') . '\s+\(\s*([^)]+)\)/i', $raw, $matches) === 1) {
            return $this->normalizeDollarList($matches[1]);
        }

        if (preg_match('/' . preg_quote($keyword, '/') . '\s+([a-zA-Z0-9._-]+)/i', $raw, $matches) === 1) {
            return [$matches[1]];
        }

        return [];
    }

    protected function extractListAfterKeyword(string $raw, string $keyword): array
    {
        if (preg_match('/' . preg_quote($keyword, '/') . '\s+\(\s*([^)]+)\)/i', $raw, $matches) === 1) {
            return $this->normalizeDollarList($matches[1]);
        }

        if (preg_match('/' . preg_quote($keyword, '/') . '\s+([a-zA-Z0-9._-]+)/i', $raw, $matches) === 1) {
            return [$matches[1]];
        }

        return [];
    }

    protected function normalizeDollarList(string $value): array
    {
        return collect(explode('$', $value))
            ->map(fn ($item) => trim(str_replace(["'", '"'], '', $item)))
            ->filter()
            ->values()
            ->all();
    }

    protected function match(string $pattern, string $raw): ?string
    {
        return preg_match($pattern, $raw, $matches) === 1
            ? trim($matches[1])
            : null;
    }

    public function buildObjectClassDefinition(array $data): string
    {
        $oid = trim((string) ($data['oid'] ?? ''));
        $aliases = $this->normalizeCsv((string) ($data['aliases_text'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $sup = $this->normalizeCsv((string) ($data['sup_text'] ?? ''));
        $classType = strtoupper(trim((string) ($data['class_type'] ?? 'STRUCTURAL')));
        $obsolete = (bool) ($data['obsolete'] ?? false);
        $must = $this->normalizeCsv((string) ($data['must_text'] ?? ''));
        $may = $this->normalizeCsv((string) ($data['may_text'] ?? ''));

        if ($oid === '') {
            throw new RuntimeException('OID wajib diisi.');
        }

        if (empty($aliases)) {
            throw new RuntimeException('Alias / NAME wajib diisi.');
        }

        $parts = ["( {$oid}"];

        if (count($aliases) === 1) {
            $parts[] = "NAME '{$aliases[0]}'";
        } else {
            $nameList = implode(' ', array_map(fn ($name) => "'{$name}'", $aliases));
            $parts[] = "NAME ( {$nameList} )";
        }

        if ($description !== '') {
            $parts[] = "DESC '" . str_replace("'", "\\'", $description) . "'";
        }

        if (! empty($sup)) {
            $parts[] = count($sup) === 1
                ? "SUP {$sup[0]}"
                : "SUP ( " . implode(' $ ', $sup) . " )";
        }

        if ($obsolete) {
            $parts[] = "OBSOLETE";
        }

        $parts[] = in_array($classType, ['ABSTRACT', 'STRUCTURAL', 'AUXILIARY'], true)
            ? $classType
            : 'STRUCTURAL';

        if (! empty($must)) {
            $parts[] = count($must) === 1
                ? "MUST {$must[0]}"
                : "MUST ( " . implode(' $ ', $must) . " )";
        }

        if (! empty($may)) {
            $parts[] = count($may) === 1
                ? "MAY {$may[0]}"
                : "MAY ( " . implode(' $ ', $may) . " )";
        }

        $parts[] = ")";

        return implode(' ', $parts);
    }

    public function buildAttributeTypeDefinition(array $data): string
    {
        $oid = trim((string) ($data['oid'] ?? ''));
        $aliases = $this->normalizeCsv((string) ($data['aliases_text'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $sup = trim((string) ($data['sup'] ?? ''));
        $equality = trim((string) ($data['equality'] ?? ''));
        $ordering = trim((string) ($data['ordering'] ?? ''));
        $substr = trim((string) ($data['substr'] ?? ''));
        $syntax = trim((string) ($data['syntax'] ?? ''));
        $usage = trim((string) ($data['usage'] ?? ''));
        $singleValue = (bool) ($data['single_value'] ?? false);
        $noUserModification = (bool) ($data['no_user_modification'] ?? false);
        $obsolete = (bool) ($data['obsolete'] ?? false);

        if ($oid === '') {
            throw new RuntimeException('OID wajib diisi.');
        }

        if (empty($aliases)) {
            throw new RuntimeException('Alias / NAME wajib diisi.');
        }

        $parts = ["( {$oid}"];

        if (count($aliases) === 1) {
            $parts[] = "NAME '{$aliases[0]}'";
        } else {
            $nameList = implode(' ', array_map(fn ($name) => "'{$name}'", $aliases));
            $parts[] = "NAME ( {$nameList} )";
        }

        if ($description !== '') {
            $parts[] = "DESC '" . str_replace("'", "\\'", $description) . "'";
        }

        if ($obsolete) {
            $parts[] = "OBSOLETE";
        }

        if ($sup !== '') {
            $parts[] = "SUP {$sup}";
        }

        if ($equality !== '') {
            $parts[] = "EQUALITY {$equality}";
        }

        if ($ordering !== '') {
            $parts[] = "ORDERING {$ordering}";
        }

        if ($substr !== '') {
            $parts[] = "SUBSTR {$substr}";
        }

        if ($syntax !== '') {
            $parts[] = "SYNTAX {$syntax}";
        }

        if ($singleValue) {
            $parts[] = "SINGLE-VALUE";
        }

        if ($noUserModification) {
            $parts[] = "NO-USER-MODIFICATION";
        }

        if ($usage !== '') {
            $parts[] = "USAGE {$usage}";
        }

        $parts[] = ")";

        return implode(' ', $parts);
    }

    protected function normalizeCsv(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
