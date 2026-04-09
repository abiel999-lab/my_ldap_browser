<?php

namespace App\Services\Ldap;

use App\Models\LdapScriptRun;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Symfony\Component\Process\Process;

class LdapScriptRunnerService
{
    public function __construct(
        protected LdapAuditTrailService $auditTrailService,
    ) {
    }

    public function run(string $scriptKey): LdapScriptRun
    {
        $allowed = config('ldap_scripts.allowed', []);

        if (! isset($allowed[$scriptKey])) {
            throw new RuntimeException('Script tidak diizinkan.');
        }

        $script = $allowed[$scriptKey];
        $path = (string) ($script['path'] ?? '');
        $label = (string) ($script['label'] ?? $scriptKey);
        $timeout = (int) ($script['timeout'] ?? 120);

        if ($path === '') {
            throw new RuntimeException('Path script kosong.');
        }

        if (! file_exists($path)) {
            throw new RuntimeException("Script tidak ditemukan: {$path}");
        }

        if (! is_executable($path)) {
            throw new RuntimeException("Script tidak executable: {$path}");
        }

        $user = Auth::user();

        $run = LdapScriptRun::create([
            'script_key' => $scriptKey,
            'script_label' => $label,
            'script_path' => $path,
            'status' => 'running',
            'actor_name' => $user->name ?? null,
            'actor_email' => $user->email ?? null,
        ]);

        $process = new Process(['bash', $path]);
        $process->setTimeout($timeout);
        $process->run();

        $run->update([
            'status' => $process->isSuccessful() ? 'success' : 'failed',
            'stdout' => $process->getOutput(),
            'stderr' => $process->getErrorOutput(),
            'exit_code' => $process->getExitCode(),
        ]);

        $this->auditTrailService->log(
            action: 'run_ldap_script',
            targetUid: null,
            targetDn: null,
            beforeData: null,
            afterData: [
                'script_key' => $scriptKey,
                'script_label' => $label,
                'script_path' => $path,
                'exit_code' => $process->getExitCode(),
            ],
            status: $process->isSuccessful() ? 'success' : 'failed',
            ldapStatus: null,
            syncStatus: null,
            message: $process->isSuccessful()
                ? 'LDAP script executed successfully.'
                : 'LDAP script execution failed.',
            errorMessage: $process->isSuccessful() ? null : $process->getErrorOutput(),
        );

        return $run;
    }
}
