<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\UpdatePreferencesRequest;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get authenticated user's profile
     *
     * @group User Profile
     *
     * @authenticated
     *
     * @responseFromApiResource App\Http\Resources\V1\User\UserResource
     */
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Update user preferences
     *
     * @group User Profile
     *
     * @authenticated
     *
     * @responseFromApiResource App\Http\Resources\V1\User\UserResource
     */
    public function updatePreferences(UpdatePreferencesRequest $request): UserResource
    {
        $validated = $request->validated();
        $user = $request->user();

        $user->update([
            'favorite_group_id' => $validated['favorite_group_id'] ?? null,
        ]);

        return new UserResource($user);
    }
}
