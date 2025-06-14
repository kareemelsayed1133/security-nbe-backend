<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
        // Supervisors can view lists of users.
        return $user->role && $user->role->name === 'supervisor';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $currentUser, User $targetUser): bool
    {
        // Supervisor can view users in their branch.
        if ($currentUser->role && $currentUser->role->name === 'supervisor') {
            return $currentUser->branch_id === $targetUser->branch_id;
        }
        // User can view their own profile.
        return $currentUser->id === $targetUser->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create users (handled by `before` method).
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $currentUser, User $targetUser): bool
    {
         // Supervisors can update guards in their branch.
        if ($currentUser->role && $currentUser->role->name === 'supervisor') {
            return $targetUser->role->name === 'guard' && $currentUser->branch_id === $targetUser->branch_id;
        }
        // Users can update their own profile (for limited fields, handled in controller).
        return $currentUser->id === $targetUser->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $currentUser, User $targetUser): bool
    {
        // Prevent users from deleting themselves and supervisors from deleting users.
        // Only admins can (handled by `before` method).
        return $currentUser->id !== $targetUser->id && false;
    }
}

