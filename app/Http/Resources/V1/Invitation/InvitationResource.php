<?php

namespace App\Http\Resources\V1\Invitation;

use App\Http\Resources\V1\Group\GroupResource;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'group' => new GroupResource($this->whenLoaded('group')),
            'inviter' => new UserResource($this->whenLoaded('inviter')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
