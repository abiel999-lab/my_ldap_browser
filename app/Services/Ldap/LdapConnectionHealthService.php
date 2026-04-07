<?php

namespace App\Services\Ldap;

class LdapConnectionHealthService
{
    public function check(): bool
    {
        if (! function_exists('ldap_connect')) {
            return false;
        }

        $host = env('LDAP_HOST', '127.0.0.1');
        $port = (int) env('LDAP_PORT', 389);
        $bindDn = env('LDAP_USERNAME') ?: env('LDAP_BIND_DN');
        $bindPassword = env('LDAP_PASSWORD') ?: env('LDAP_BIND_PASSWORD');
        $timeout = (int) env('LDAP_TIMEOUT', 5);
        $useSsl = filter_var(env('LDAP_SSL', false), FILTER_VALIDATE_BOOLEAN);
        $useTls = filter_var(env('LDAP_TLS', false), FILTER_VALIDATE_BOOLEAN);

        $uri = $useSsl ? "ldaps://{$host}:{$port}" : "ldap://{$host}:{$port}";

        $connection = @ldap_connect($uri);

        if (! $connection) {
            return false;
        }

        @ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        @ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
        @ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, $timeout);
        @ldap_set_option($connection, LDAP_OPT_TIMELIMIT, $timeout);

        if ($useTls) {
            if (! @ldap_start_tls($connection)) {
                return false;
            }
        }

        if (! @ldap_bind($connection, $bindDn, $bindPassword)) {
            return false;
        }

        $search = @ldap_read(
            $connection,
            '',
            '(objectClass=*)',
            ['namingContexts', 'subschemaSubentry']
        );

        if (! $search) {
            return false;
        }

        $entries = @ldap_get_entries($connection, $search);

        return is_array($entries) && (($entries['count'] ?? 0) > 0);
    }
}
