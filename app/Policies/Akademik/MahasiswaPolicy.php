<?php

namespace App\Policies\Akademik;

use App\Helpers\Auth\UserHelper;
use App\Models\Akademik\Mahasiswa;
use App\Models\User;

class MahasiswaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC', 'KAPRODI', 'KOORDINATOR']);
    }

    public function view(User $user, Mahasiswa $mahasiswa): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC', 'KAPRODI', 'KOORDINATOR']);
    }

    public function create(User $user): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }

    public function update(User $user, Mahasiswa $mahasiswa): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }

    public function delete(User $user, Mahasiswa $mahasiswa): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }

    public function deleteAny(User $user): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }
}
