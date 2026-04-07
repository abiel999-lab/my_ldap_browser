<?php

declare(strict_types=1);

namespace App\Models\Ldap;

use LdapRecord\Models\OpenLDAP\User;

class LdapUser extends User
{
    public function getDisplayName(): string
    {
        return (string) ($this->getFirstAttribute('cn') ?? $this->getFirstAttribute('uid') ?? 'unknown');
    }

    public function getEmail(): ?string
    {
        return $this->getFirstAttribute('mail');
    }

    public function getUid(): ?string
    {
        return $this->getFirstAttribute('uid');
    }
}