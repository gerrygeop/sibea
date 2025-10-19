<?php

namespace App\Policies;

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
        return $user->hasAnyRole(['mahasiswa', 'staf']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PeriodeBeasiswa $periode_beasiswa): bool
    {
        return $user->hasAnyRole(['mahasiswa', 'staf']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('staf');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PeriodeBeasiswa $periode_beasiswa): bool
    {
        return $user->hasRole('staf');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PeriodeBeasiswa $periode_beasiswa): bool
    {
        return $user->hasRole('staf');
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
