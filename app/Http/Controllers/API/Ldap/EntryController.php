<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Ldap;

use App\Http\Controllers\Controller;
use App\Services\Ldap\LdapAuditLogService;
use App\Services\Ldap\LdapEntryFormatterService;
use App\Services\Ldap\LdapEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EntryController extends Controller
{
    public function show(
        Request $request,
        LdapEntryService $ldapEntryService,
        LdapEntryFormatterService $ldapEntryFormatterService
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'dn' => ['required', 'string'],
            ]);

            $entry = $ldapEntryService->getEntry($validated['dn']);

            return response()->json([
                'success' => true,
                'entry' => $ldapEntryFormatterService->sanitizeEntry($entry),
            ]);
        } catch (Throwable $throwable) {
            return $this->errorResponse($throwable);
        }
    }

    public function search(
        Request $request,
        LdapEntryService $ldapEntryService,
        LdapEntryFormatterService $ldapEntryFormatterService
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'base_dn' => ['required', 'string'],
                'filter' => ['nullable', 'string'],
                'size_limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
            ]);

            $entries = $ldapEntryService->search(
                $validated['base_dn'],
                $validated['filter'] ?? '(objectClass=*)',
                (int) ($validated['size_limit'] ?? 200),
            );

            return response()->json([
                'success' => true,
                'entries' => $ldapEntryFormatterService->sanitizeEntries($entries),
            ]);
        } catch (Throwable $throwable) {
            return $this->errorResponse($throwable);
        }
    }

    public function store(
        Request $request,
        LdapEntryService $ldapEntryService,
        LdapAuditLogService $ldapAuditLogService
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'dn' => ['required', 'string'],
                'attributes' => ['required', 'array'],
            ]);

            $ldapEntryService->createEntry(
                $validated['dn'],
                $validated['attributes']
            );

            $ldapAuditLogService->log(
                action: 'create_entry',
                targetDn: $validated['dn'],
                actor: $this->resolveActor($request),
                payload: [
                    'attributes' => $validated['attributes'],
                ],
                result: [
                    'success' => true,
                ],
            );

            return response()->json([
                'success' => true,
                'message' => 'LDAP entry created successfully.',
            ]);
        } catch (Throwable $throwable) {
            return $this->errorResponse($throwable);
        }
    }

    public function replaceAttributes(
        Request $request,
        LdapEntryService $ldapEntryService,
        LdapAuditLogService $ldapAuditLogService
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'dn' => ['required', 'string'],
                'attributes' => ['required', 'array'],
            ]);

            $ldapEntryService->replaceAttributes(
                $validated['dn'],
                $validated['attributes']
            );

            $ldapAuditLogService->log(
                action: 'replace_attributes',
                targetDn: $validated['dn'],
                actor: $this->resolveActor($request),
                payload: [
                    'attributes' => $validated['attributes'],
                ],
                result: [
                    'success' => true,
                ],
            );

            return response()->json([
                'success' => true,
                'message' => 'LDAP attributes replaced successfully.',
            ]);
        } catch (Throwable $throwable) {
            return $this->errorResponse($throwable);
        }
    }

    public function setAttribute(
        Request $request,
        LdapEntryService $ldapEntryService,
        LdapAuditLogService $ldapAuditLogService
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'dn' => ['required', 'string'],
                'attribute' => ['required', 'string'],
                'values' => ['required', 'array'],
            ]);

            $ldapEntryService->setAttribute(
                $validated['dn'],
                $validated['attribute'],
                $validated['values']
            );

            $ldapAuditLogService->log(
                action: 'set_attribute',
                targetDn: $validated['dn'],
                actor: $this->resolveActor($request),
                payload: [
                    'attribute' => $validated['attribute'],
                    'values' => $validated['values'],
                ],
                result: [
                    'success' => true,
                ],
            );

            return response()->json([
                'success' => true,
                'message' => 'LDAP attribute set successfully.',
            ]);
        } catch (Throwable $throwable) {
            return $this->errorResponse($throwable);
        }
    }

    public function addAttributeValues(
        Request $request,
        LdapEntryService $ldapEntryService,
        LdapAuditLogService $ldapAuditLogService
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'dn' => ['required', 'string'],
                'attribute' => ['required', 'string'],
                'values' => ['required', 'array'],
            ]);

            $ldapEntryService->addAttributeValues(
                $validated['dn'],
                $validated['attribute'],
                $validated['values']
            );

            $ldapAuditLogService->log(
                action: 'add_attribute_values',
                targetDn: $validated['dn'],
                actor: $this->resolveActor($request),
                payload: [
                    'attribute' => $validated['attribute'],
                    'values' => $validated['values'],
                ],
                result: [
                    'success' => true,
                ],
            );

            return response()->json([
                'success' => true,
                'message' => 'LDAP attribute values added successfully.',
            ]);
        } catch (Throwable $throwable) {
            return $this->errorResponse($throwable);
        }
    }

    public function deleteAttribute(
        Request $request,
        LdapEntryService $ldapEntryService,
        LdapAuditLogService $ldapAuditLogService
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'dn' => ['required', 'string'],
                'attribute' => ['required', 'string'],
                'values' => ['nullable', 'array'],
            ]);

            $ldapEntryService->deleteAttribute(
                $validated['dn'],
                $validated['attribute'],
                $validated['values'] ?? null
            );

            $ldapAuditLogService->log(
                action: 'delete_attribute',
                targetDn: $validated['dn'],
                actor: $this->resolveActor($request),
                payload: [
                    'attribute' => $validated['attribute'],
                    'values' => $validated['values'] ?? null,
                ],
                result: [
                    'success' => true,
                ],
            );

            return response()->json([
                'success' => true,
                'message' => 'LDAP attribute deleted successfully.',
            ]);
        } catch (Throwable $throwable) {
            return $this->errorResponse($throwable);
        }
    }

    public function addObjectClasses(
        Request $request,
        LdapEntryService $ldapEntryService,
        LdapAuditLogService $ldapAuditLogService
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'dn' => ['required', 'string'],
                'object_classes' => ['required', 'array'],
            ]);

            $ldapEntryService->addObjectClasses(
                $validated['dn'],
                $validated['object_classes']
            );

            $ldapAuditLogService->log(
                action: 'add_object_classes',
                targetDn: $validated['dn'],
                actor: $this->resolveActor($request),
                payload: [
                    'object_classes' => $validated['object_classes'],
                ],
                result: [
                    'success' => true,
                ],
            );

            return response()->json([
                'success' => true,
                'message' => 'LDAP objectClass added successfully.',
            ]);
        } catch (Throwable $throwable) {
            return $this->errorResponse($throwable);
        }
    }

    public function removeObjectClasses(
        Request $request,
        LdapEntryService $ldapEntryService,
        LdapAuditLogService $ldapAuditLogService
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'dn' => ['required', 'string'],
                'object_classes' => ['required', 'array'],
            ]);

            $ldapEntryService->removeObjectClasses(
                $validated['dn'],
                $validated['object_classes']
            );

            $ldapAuditLogService->log(
                action: 'remove_object_classes',
                targetDn: $validated['dn'],
                actor: $this->resolveActor($request),
                payload: [
                    'object_classes' => $validated['object_classes'],
                ],
                result: [
                    'success' => true,
                ],
            );

            return response()->json([
                'success' => true,
                'message' => 'LDAP objectClass removed successfully.',
            ]);
        } catch (Throwable $throwable) {
            return $this->errorResponse($throwable);
        }
    }

    public function rename(
        Request $request,
        LdapEntryService $ldapEntryService,
        LdapAuditLogService $ldapAuditLogService
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'dn' => ['required', 'string'],
                'new_rdn' => ['required', 'string'],
                'new_parent_dn' => ['nullable', 'string'],
                'delete_old_rdn' => ['nullable', 'boolean'],
            ]);

            $ldapEntryService->renameEntry(
                $validated['dn'],
                $validated['new_rdn'],
                $validated['new_parent_dn'] ?? null,
                (bool) ($validated['delete_old_rdn'] ?? true),
            );

            $ldapAuditLogService->log(
                action: 'rename_entry',
                targetDn: $validated['dn'],
                actor: $this->resolveActor($request),
                payload: [
                    'new_rdn' => $validated['new_rdn'],
                    'new_parent_dn' => $validated['new_parent_dn'] ?? null,
                    'delete_old_rdn' => (bool) ($validated['delete_old_rdn'] ?? true),
                ],
                result: [
                    'success' => true,
                ],
            );

            return response()->json([
                'success' => true,
                'message' => 'LDAP entry renamed successfully.',
            ]);
        } catch (Throwable $throwable) {
            return $this->errorResponse($throwable);
        }
    }

    public function destroy(
        Request $request,
        LdapEntryService $ldapEntryService,
        LdapAuditLogService $ldapAuditLogService
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'dn' => ['required', 'string'],
                'recursive' => ['nullable', 'boolean'],
            ]);

            $ldapEntryService->deleteEntry(
                $validated['dn'],
                (bool) ($validated['recursive'] ?? false),
            );

            $ldapAuditLogService->log(
                action: 'delete_entry',
                targetDn: $validated['dn'],
                actor: $this->resolveActor($request),
                payload: [
                    'recursive' => (bool) ($validated['recursive'] ?? false),
                ],
                result: [
                    'success' => true,
                ],
            );

            return response()->json([
                'success' => true,
                'message' => 'LDAP entry deleted successfully.',
            ]);
        } catch (Throwable $throwable) {
            return $this->errorResponse($throwable);
        }
    }

    private function resolveActor(Request $request): ?string
    {
        if (method_exists($request, 'user') && $request->user()) {
            $user = $request->user();

            return $user->email
                ?? $user->username
                ?? $user->name
                ?? 'authenticated-user';
        }

        return 'local-dev';
    }

    private function errorResponse(Throwable $throwable): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $throwable->getMessage(),
        ], 500);
    }
}