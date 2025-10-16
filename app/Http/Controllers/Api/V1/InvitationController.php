<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\InvitationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Invitation\StoreInvitationRequest;
use App\Http\Resources\V1\Invitation\InvitationResource;
use App\Models\Group;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InvitationController extends Controller
{
    public function store(StoreInvitationRequest $request, Group $group)
    {
        $this->authorize('sendInvitation', $group);

        $validated = $request->validated();

        $group->invitations()->create([
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

    /**
     * @throws Throwable
     */
    public function accept(Invitation $invitation)
    {
        $this->authorize('accept', $invitation);

        DB::transaction(function () use ($invitation) {
            $invitation->group->members()->attach(Auth::user()->id);

            $invitation->update(['status' => InvitationStatus::ACCEPTED]);
        });

        return response()->noContent();
    }


    public function decline(Invitation $invitation)
    {
        $this->authorize('decline', $invitation);

        $invitation->update(['status' => InvitationStatus::DECLINED]);

        return response()->noContent();
    }
}
