<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{

    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, Group $group): bool
    {
        return $group->members->contains($user);
    }

    public function create(): bool
    {
        return true;
    }

    public function update(User $user, Group $group): bool
    {
        $role = $group->members()->where('user_id', $user->id)->first()?->pivot->role;

        return $user->id === $group->owner_id || $role === 'admin';
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }

    public function sendInvitation(User $user, Group $group): bool
    {
        $role = $group->members()->where('user_id', $user->id)->first()?->pivot->role;

        return $user->id === $group->owner_id || $role === 'admin';
    }

    public function removeMember(User $user, Group $group, User $memberToRemove): bool
    {
        if ($user->id === $memberToRemove->id) {
            return false;
        }

        if ($memberToRemove->id === $group->owner_id) {
            return false;
        }

        $role = $group->members()->where('user_id', $user->id)->first()?->pivot->role;

        return $user->id === $group->owner_id || $role === 'admin';
    }

    public function updateMemberRole(User $user, Group $group, User $memberToUpdate): bool
    {
        if ($user->id === $memberToUpdate->id) {
            return false;
        }

        if ($memberToUpdate->id === $group->owner_id) {
            return false;
        }

        $role = $group->members()->where('user_id', $user->id)->first()?->pivot->role;

        return $user->id === $group->owner_id || $role === 'admin';
    }


    public function viewGroupTransactions(User $user, Group $group): bool
    {
        return $group->members()->where('user_id', $user->id)->exists();
    }
}
