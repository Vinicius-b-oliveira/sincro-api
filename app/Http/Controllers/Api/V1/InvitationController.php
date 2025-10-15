<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Invitation\StoreInvitationRequest;
use App\Models\Group;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class InvitationController extends Controller
{
    /**
     * Armazena um novo convite para um grupo.
     */
    public function store(StoreInvitationRequest $request, Group $group)
    {
        $this->authorize('sendInvitation', $group);

        $validated = $request->validated();

        $invitation = $group->invitations()->create([
            'inviter_id' => $request->user()->id,
            'email'      => $validated['email'],
            'token'      => Str::uuid(),
        ]);

        return response(null, Response::HTTP_CREATED);
    }
}
