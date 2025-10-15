<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GroupPolicy
{
    /**
     * Determina se o usuário pode visualizar a lista de grupos.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina se o usuário pode visualizar um grupo específico.
     */
    public function view(User $user, Group $group): bool
    {
        return $group->members->contains($user);
    }

    /**
     * Determina se o usuário pode criar um novo grupo.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determina se o usuário pode atualizar um grupo.
     */
    public function update(User $user, Group $group): bool
    {
        $role = $group->members()->where('user_id', $user->id)->first()?->pivot->role;

        return $user->id === $group->owner_id || $role === 'admin';
    }

    /**
     * Determina se o usuário pode deletar um grupo.
     */
    public function delete(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }
}
