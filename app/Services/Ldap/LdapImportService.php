<?php

namespace App\Services\Ldap;

use App\Models\LdapImport;
use App\Models\LdapImportRow;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class LdapImportService
{
    public function __construct(
        protected LdapDirectoryService $ldapDirectoryService,
    ) {
    }

    public function run(LdapImport $import): void
    {
        try {
            $import->update([
                'status' => 'running',
                'error_message' => null,
            ]);

            $rows = $this->readRows(storage_path('app/' . $import->file_path));

            $success = 0;
            $failed = 0;
            $rowNumber = 1;

            foreach ($rows as $row) {
                $payload = $this->ldapDirectoryService->normalizeImportRow($row);

                $uid = $payload['uid'] ?? null;
                $dn = null;
                $status = 'success';
                $message = 'OK';

                try {
                    $attributes = $this->ldapDirectoryService->rowToLdapAttributes($payload);
                    $dn = $this->ldapDirectoryService->buildDnFromRow($payload);

                    if ($import->mode === 'create_only') {
                        $this->ldapDirectoryService->add($dn, $attributes);
                    } else {
                        $existing = $this->ldapDirectoryService->findByUid((string) $uid);

                        if ($existing) {
                            $this->ldapDirectoryService->modify($existing['dn'], $attributes);
                            $dn = $existing['dn'];
                        } else {
                            $this->ldapDirectoryService->add($dn, $attributes);
                        }
                    }

                    $success++;
                } catch (Throwable $e) {
                    $status = 'failed';
                    $message = $e->getMessage();
                    $failed++;
                }

                LdapImportRow::query()->create([
                    'ldap_import_id' => $import->id,
                    'row_number' => $rowNumber,
                    'uid' => $uid,
                    'dn' => $dn,
                    'status' => $status,
                    'message' => $message,
                    'payload_json' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ]);

                $rowNumber++;
            }

            $import->update([
                'status' => $failed > 0 ? 'partial' : 'success',
                'total_rows' => count($rows),
                'success_rows' => $success,
                'failed_rows' => $failed,
            ]);
        } catch (Throwable $e) {
            $import->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    protected function readRows(string $fullPath): array
    {
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        return match ($extension) {
            'csv' => $this->readCsv($fullPath),
            'xlsx', 'xls' => $this->readSpreadsheet($fullPath),
            default => throw new \RuntimeException('Format file tidak didukung. Gunakan CSV/XLSX/XLS.'),
        };
    }

    protected function readCsv(string $fullPath): array
    {
        $handle = fopen($fullPath, 'r');

        if (! $handle) {
            throw new \RuntimeException('Gagal membaca file CSV.');
        }

        $headers = null;
        $rows = [];

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if ($headers === null) {
                $headers = $data;
                continue;
            }

            if (count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $rows[] = array_combine($headers, $data);
        }

        fclose($handle);

        return $rows;
    }

    protected function readSpreadsheet(string $fullPath): array
    {
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $array = $sheet->toArray(null, true, true, true);

        $headers = null;
        $rows = [];

        foreach ($array as $row) {
            $values = array_values($row);

            if ($headers === null) {
                $headers = $values;
                continue;
            }

            if (count(array_filter($values, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $rows[] = array_combine($headers, $values);
        }

        return $rows;
    }
}
