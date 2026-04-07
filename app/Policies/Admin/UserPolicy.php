<?php

namespace App\Policies\Admin;

use App\Helpers\Auth\UserHelper;
use App\Models\Auth\User;
use App\Models\Gate\User as UserGate;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $userAuth): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $userAuth, UserGate $user): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $userAuth): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $userAuth, UserGate $user): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $userAuth, UserGate $user): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }

    public function deleteAny(User $userAuth): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $userAuth, UserGate $user): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $userAuth, UserGate $user): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }
}
