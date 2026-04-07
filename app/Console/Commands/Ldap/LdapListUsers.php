<?php

declare(strict_types=1);

namespace App\Console\Commands\Ldap;

use App\Services\Ldap\LdapUserService;
use Illuminate\Console\Command;
use Throwable;

class LdapListUsers extends Command
{
    protected $signature = 'ldap:list-users {--limit=20 : Number of LDAP users to fetch}';

    protected $description = 'Read LDAP users from people DN';

    public function handle(LdapUserService $ldapUserService): int
    {
        try {
            $limit = (int) $this->option('limit');

            $users = $ldapUserService->listUsers($limit);

            if ($users->isEmpty()) {
                $this->warn('No LDAP users found.');

                return self::SUCCESS;
            }

            $this->info("LDAP users fetched: {$users->count()}");
            $this->newLine();

            $this->table(
                [
                    'uid',
                    'cn',
                    'mail',
                    'employeeNumber',
                    'studentNumber',
                    'petraAffiliation',
                ],
                $users->map(fn (array $user): array => [
                    $user['uid'] ?? '',
                    $user['cn'] ?? '',
                    $user['mail'] ?? '',
                    $user['employeeNumber'] ?? '',
                    $user['studentNumber'] ?? '',
                    $user['petraAffiliation'] ?? '',
                ])->toArray()
            );

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error('Failed to list LDAP users.');
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }
    }
}