<?php

declare(strict_types=1);

namespace App\Models\Ldap;

use LdapRecord\Models\OpenLDAP\Group;

class LdapGroup extends Group
{
    public function getDisplayName(): string
    {
        return (string) ($this->getFirstAttribute('cn') ?? 'unknown-group');
    }
}