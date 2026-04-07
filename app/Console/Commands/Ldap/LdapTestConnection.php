<?php

declare(strict_types=1);

namespace App\Console\Commands\Ldap;

use App\Services\Ldap\LdapUserService;
use Illuminate\Console\Command;
use Throwable;

class LdapTestConnection extends Command
{
    protected $signature = 'ldap:test-connection';

    protected $description = 'Test LDAP bind and show connection summary';

    public function handle(LdapUserService $ldapUserService): int
    {
        try {
            $result = $ldapUserService->testConnection();

            $this->info('LDAP connection success.');
            $this->newLine();

            $this->table(
                ['Key', 'Value'],
                [
                    ['status', (string) $result['status']],
                    ['host', (string) $result['host']],
                    ['port', (string) $result['port']],
                    ['baseDn', (string) $result['baseDn']],
                    ['peopleDn', (string) $result['peopleDn']],
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error('LDAP connection failed.');
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }
    }
}