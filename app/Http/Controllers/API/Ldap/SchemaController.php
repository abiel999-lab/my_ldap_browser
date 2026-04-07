<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Ldap;

use App\Http\Controllers\Controller;
use App\Services\Ldap\LdapSchemaService;
use Illuminate\Http\JsonResponse;
use Throwable;

class SchemaController extends Controller
{
    public function rootDse(LdapSchemaService $ldapSchemaService): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'rootDse' => $ldapSchemaService->getRootDse(),
            ]);
        } catch (Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => $throwable->getMessage(),
            ], 500);
        }
    }

    public function objectClasses(LdapSchemaService $ldapSchemaService): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'objectClasses' => $ldapSchemaService->getObjectClasses(),
            ]);
        } catch (Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => $throwable->getMessage(),
            ], 500);
        }
    }

    public function attributeTypes(LdapSchemaService $ldapSchemaService): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'attributeTypes' => $ldapSchemaService->getAttributeTypes(),
            ]);
        } catch (Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => $throwable->getMessage(),
            ], 500);
        }
    }
}