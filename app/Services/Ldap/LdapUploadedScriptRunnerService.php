<?php

namespace App\Services\Ldap;

use App\Models\LdapScriptRun;
use App\Models\LdapUploadedScript;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Symfony\Component\Process\Process;

class LdapUploadedScriptRunnerService
{
    public function __construct(
        protected LdapAuditTrailService $auditTrailService,
    ) {
    }

    public function run(int $scriptId): LdapScriptRun
    {
        $script = LdapUploadedScript::query()->findOrFail($scriptId);

        if (! $script->is_active) {
            throw new RuntimeException('Script ini sedang nonaktif.');
        }

        $absolutePath = storage_path('app/' . $script->stored_path);

        if (! file_exists($absolutePath)) {
            throw new RuntimeException("File script tidak ditemukan: {$absolutePath}");
        }

        $extension = strtolower((string) $script->extension);
        $user = Auth::user();

        $run = LdapScriptRun::create([
            'script_key' => 'uploaded-script-' . $script->id,
            'script_label' => $script->name,
            'script_path' => $absolutePath,
            'status' => 'running',
            'actor_name' => $user->name ?? null,
            'actor_email' => $user->email ?? null,
        ]);

        try {
            $process = $this->buildProcess($absolutePath, $extension);
            $process->setTimeout(120);
            $process->run();

            $run->update([
                'status' => $process->isSuccessful() ? 'success' : 'failed',
                'stdout' => $process->getOutput(),
                'stderr' => $process->getErrorOutput(),
                'exit_code' => $process->getExitCode(),
            ]);

            $this->auditTrailService->log(
                action: 'run_uploaded_ldap_script',
                targetUid: null,
                targetDn: null,
                beforeData: null,
                afterData: [
                    'script_id' => $script->id,
                    'script_name' => $script->name,
                    'stored_path' => $script->stored_path,
                    'exit_code' => $process->getExitCode(),
                ],
                status: $process->isSuccessful() ? 'success' : 'failed',
                ldapStatus: null,
                syncStatus: null,
                message: $process->isSuccessful()
                    ? 'Uploaded LDAP script executed successfully.'
                    : 'Uploaded LDAP script execution failed.',
                errorMessage: $process->isSuccessful() ? null : $process->getErrorOutput(),
            );

            return $run;
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'stdout' => null,
                'stderr' => $e->getMessage(),
                'exit_code' => 1,
            ]);

            $this->auditTrailService->log(
                action: 'run_uploaded_ldap_script',
                targetUid: null,
                targetDn: null,
                beforeData: null,
                afterData: [
                    'script_id' => $script->id,
                    'script_name' => $script->name,
                    'stored_path' => $script->stored_path,
                    'exit_code' => 1,
                ],
                status: 'failed',
                ldapStatus: null,
                syncStatus: null,
                message: 'Uploaded LDAP script execution failed.',
                errorMessage: $e->getMessage(),
            );

            throw $e;
        }
    }

    protected function buildProcess(string $absolutePath, string $extension): Process
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return match ($extension) {
                'bat', 'cmd' => new Process(['cmd', '/c', $absolutePath]),
                'ps1' => new Process([
                    'powershell',
                    '-ExecutionPolicy',
                    'Bypass',
                    '-File',
                    $absolutePath,
                ]),
                'sh' => new Process(['bash', $absolutePath]),
                default => throw new RuntimeException("Extension script tidak didukung di Windows: {$extension}"),
            };
        }

        return match ($extension) {
            'sh' => new Process(['bash', $absolutePath]),
            default => throw new RuntimeException("Extension script tidak didukung di Linux/Unix: {$extension}"),
        };
    }
}
