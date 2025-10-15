<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\InvitationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Invitation\StoreInvitationRequest;
use App\Http\Resources\V1\Invitation\InvitationResource;
use App\Models\Group;
use App\Models\Invitation;
use Illuminate\Http\Request;
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

    public function pending(Request $request)
    {
        $user = $request->user();

        $invitations = Invitation::where('email', $user->email)
            ->where('status', InvitationStatus::PENDING)
            ->with(['group', 'inviter'])
            ->latest()
            ->get();

        return InvitationResource::collection($invitations);
    }
}
