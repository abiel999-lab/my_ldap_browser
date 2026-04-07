<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Ldap;

use App\Http\Controllers\Controller;
use App\Services\Ldap\LdapEntryFormatterService;
use App\Services\Ldap\LdapEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TreeController extends Controller
{
    public function index(
        Request $request,
        LdapEntryService $ldapEntryService,
        LdapEntryFormatterService $ldapEntryFormatterService
    ): JsonResponse {
        try {
            $baseDn = (string) ($request->query('dn') ?: config('ldap_admin.base_dn'));

            return response()->json([
                'success' => true,
                'baseDn' => $baseDn,
                'children' => $ldapEntryFormatterService->sanitizeEntries(
                    $ldapEntryService->listChildren($baseDn)
                ),
            ]);
        } catch (Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => $throwable->getMessage(),
            ], 500);
        }
    }
}