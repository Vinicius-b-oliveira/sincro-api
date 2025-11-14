<?php

namespace App\Http\Resources\V1\Group;

use App\Http\Resources\V1\User\UserResource;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Group */
class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'owner' => new UserResource($this->whenLoaded('owner')),

            'members_can_add_transactions' => $this->members_can_add_transactions,
            'members_can_invite' => $this->members_can_invite,

            'members_count' => $this->whenCounted('members'),

            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
