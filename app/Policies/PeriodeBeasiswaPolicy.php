<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PeriodeBeasiswa;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PeriodeBeasiswaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::MAHASISWA, UserRole::STAFF, UserRole::PENGELOLA]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PeriodeBeasiswa $periode_beasiswa): bool
    {
        if ($user->hasAnyRole([UserRole::MAHASISWA, UserRole::STAFF])) {
            return true;
        }

        // Cek apakah user adalah pengelola periode ini
        if ($user->hasRole(UserRole::PENGELOLA)) {
            return $periode_beasiswa->pengelola()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::STAFF);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PeriodeBeasiswa $periode_beasiswa): bool
    {
        if ($user->hasRole(UserRole::STAFF)) {
            return true;
        }

        if ($user->hasRole(UserRole::PENGELOLA)) {
            return $periode_beasiswa->pengelola()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PeriodeBeasiswa $periode_beasiswa): bool
    {
        if ($user->hasRole(UserRole::STAFF)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PeriodeBeasiswa $periode_beasiswa): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PeriodeBeasiswa $periode_beasiswa): bool
    {
        return false;
    }
}
