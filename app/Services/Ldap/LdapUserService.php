<?php

namespace App\Services\Ldap;

use App\Models\Ldap\LdapUser;

class LdapUserService
{
    public function getAll()
    {
        return LdapUser::all();
    }

    public function create(array $data)
    {
        return LdapUser::create($data);
    }

    public function update($dn, array $data)
    {
        $user = LdapUser::find($dn);
        $user->fill($data);
        $user->save();

        return $user;
    }

    public function delete($dn)
    {
        $user = LdapUser::find($dn);
        return $user->delete();
    }
}
