<?php

namespace App\Policies;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvitationPolicy
{
    public function accept(User $user, Invitation $invitation): bool
    {
        return $invitation->status === InvitationStatus::PENDING
            && $invitation->email === $user->email;
    }

    public function decline(User $user, Invitation $invitation): bool
    {
        return $this->accept($user, $invitation);
    }
}
