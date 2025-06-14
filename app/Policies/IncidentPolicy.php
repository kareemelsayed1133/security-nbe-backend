<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IncidentPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks. Admins can do anything.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Supervisors can view lists of incidents.
        return $user->role && $user->role->name === 'supervisor';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Incident $incident): bool
    {
        // Supervisor can view incidents in their branch.
        if ($user->role && $user->role->name === 'supervisor') {
            return $user->branch_id === $incident->branch_id;
        }

        // Guard can view an incident they reported.
        if ($user->role && $user->role->name === 'guard') {
            return $user->id === $incident->reported_by_user_id;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only guards can create incidents.
        return $user->role && $user->role->name === 'guard';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Incident $incident): bool
    {
        // Only supervisors of the same branch can update.
        if ($user->role && $user->role->name === 'supervisor') {
            return $user->branch_id === $incident->branch_id;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Incident $incident): bool
    {
        // Only admins can delete (handled by `before` method).
        return false;
    }
}

