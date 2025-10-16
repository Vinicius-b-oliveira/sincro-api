<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Group $group): bool
    {
        return $this->isMember($user, $group);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Group $group): bool
    {
        return $this->isAdmin($user, $group) || $this->isOwner($user, $group);
    }

    public function delete(User $user, Group $group): bool
    {
        return $this->isOwner($user, $group);
    }

    public function sendInvitation(User $user, Group $group): bool
    {
        return $this->isAdmin($user, $group) || $this->isOwner($user, $group);
    }

    public function removeMember(User $user, Group $group, User $memberToRemove): bool
    {
        if ($user->id === $memberToRemove->id || $this->isOwner($memberToRemove, $group)) {
            return false;
        }

        return $this->isAdmin($user, $group) || $this->isOwner($user, $group);
    }

    public function updateMemberRole(User $user, Group $group, User $memberToUpdate): bool
    {
        if ($user->id === $memberToUpdate->id || $this->isOwner($memberToUpdate, $group)) {
            return false;
        }

        return $this->isAdmin($user, $group) || $this->isOwner($user, $group);
    }

    public function viewGroupTransactions(User $user, Group $group): bool
    {
        return $this->isMember($user, $group);
    }

    private function isOwner(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }

    private function isAdmin(User $user, Group $group): bool
    {
        return $group->members()->where('user_id', $user->id)->where('role', 'admin')->exists();
    }

    private function isMember(User $user, Group $group): bool
    {
        return $group->members()->where('user_id', $user->id)->exists();
    }
}
