<?php

namespace App\Services\Ldap;

use App\Models\LdapManualEntry;

class LdapUserManualService
{
    public function seedIfEmpty(): void
    {
        if (LdapManualEntry::query()->exists()) {
            return;
        }

        $rows = [
            [
                'section_key' => 'overview',
                'title' => 'Overview',
                'content' => 'Dashboard LDAP Petra untuk pengelolaan fitur LDAP.',
                'sort_order' => 1,
                'is_active' => true,
                'language' => 'id',
            ],
            [
                'section_key' => 'schema-browser',
                'title' => 'Schema Browser',
                'content' => 'Schema Browser digunakan untuk melihat schema LDAP.',
                'sort_order' => 2,
                'is_active' => true,
                'language' => 'id',
            ],
        ];

        foreach ($rows as $row) {
            LdapManualEntry::query()->create($row);
        }
    }
}
