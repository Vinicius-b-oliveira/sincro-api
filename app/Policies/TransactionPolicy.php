<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }

    public function create(User $user, ?int $group_id): bool
    {
        if (is_null($group_id)) {
            return true;
        }

        $group = Group::find($group_id);

        if (! $group) {
            return false;
        }

        $isOwner = $group->owner_id === $user->id;
        $isAdmin = $group->members()->where('user_id', $user->id)->where('role', 'admin')->exists();

        if ($isOwner || $isAdmin) {
            return true;
        }

        return $group->members_can_add_transactions;
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }
}
