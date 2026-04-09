<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | LDAP Connection
    |--------------------------------------------------------------------------
    */

    'host' => env('LDAP_HOST', '127.0.0.1'),
    'port' => (int) env('LDAP_PORT', 389),
    'timeout' => (int) env('LDAP_TIMEOUT', 5),
    'use_ssl' => filter_var(env('LDAP_SSL', false), FILTER_VALIDATE_BOOL),
    'use_tls' => filter_var(env('LDAP_TLS', false), FILTER_VALIDATE_BOOL),

    'bind_dn' => env('LDAP_USERNAME'),
    'bind_password' => env('LDAP_PASSWORD'),

    'schema_bind_dn' => env('LDAP_SCHEMA_BIND_DN', ''),
    'schema_bind_password' => env('LDAP_SCHEMA_BIND_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Base DNs
    |--------------------------------------------------------------------------
    */

    'base_dn' => env('LDAP_BASE_DN', 'dc=petra,dc=ac,dc=id'),
    'people_dn' => env('LDAP_PEOPLE_DN', 'ou=people,dc=petra,dc=ac,dc=id'),
    'groups_dn' => env('LDAP_GROUPS_DN', 'ou=groups,dc=petra,dc=ac,dc=id'),

    /*
    |--------------------------------------------------------------------------
    | UI / Visibility
    |--------------------------------------------------------------------------
    */

    'hidden_attributes' => [
        'userpassword',
        'pwdhistory',
        'pwdfailuretime',
        'pwdaccountlockedtime',
        'authpassword',
    ],

    'operational_attributes' => [
        'entryuuid',
        'creatorsname',
        'createtimestamp',
        'entrycsn',
        'modifiersname',
        'modifytimestamp',
        'subschemasubentry',
        'hassubordinates',
        'structuralobjectclass',
    ],

    /*
    |--------------------------------------------------------------------------
    | Write Protection
    |--------------------------------------------------------------------------
    */

    'protected_dns' => [
        env('LDAP_BASE_DN', 'dc=petra,dc=ac,dc=id'),
        'cn=readonly,' . env('LDAP_BASE_DN', 'dc=petra,dc=ac,dc=id'),
    ],

    'protected_dn_suffixes' => [
        'cn=config',
    ],

    'forbidden_write_attributes' => [
        'entryuuid',
        'creatorsname',
        'createtimestamp',
        'entrycsn',
        'modifiersname',
        'modifytimestamp',
        'subschemasubentry',
        'structuralobjectclass',
        'hassubordinates',
    ],
];
