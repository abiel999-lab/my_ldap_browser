<?php

namespace App\Policies\Admin;

use App\Helpers\Auth\UserHelper;
use App\Models\Ref\Bahasa;
use App\Models\User;

class BahasaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return UserHelper::userHasRole(['ADMIN']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bahasa $bahasa): bool
    {
        return UserHelper::userHasRole(['ADMIN']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return UserHelper::userHasRole(['ADMIN']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bahasa $bahasa): bool
    {
        return UserHelper::userHasRole(['ADMIN']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bahasa $bahasa): bool
    {
        return UserHelper::userHasRole(['ADMIN']);
    }

    public function deleteAny(User $user): bool
    {
        return UserHelper::userHasRole(['ADMIN']);
    }
}
