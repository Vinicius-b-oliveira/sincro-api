<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\User\UpdatePreferencesRequest;
use App\Http\Requests\Api\V1\User\UpdateProfileRequest;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

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
     * Update user profile (name)
     *
     * @group User Profile
     *
     * @authenticated
     *
     * @responseFromApiResource App\Http\Resources\V1\User\UserResource
     */
    public function updateProfile(UpdateProfileRequest $request): UserResource
    {
        $validated = $request->validated();
        $user = $request->user();

        $user->update([
            'name' => $validated['name'],
        ]);

        return new UserResource($user);
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

    /**
     * @group User Profile
     * @authenticated
     *
     * @response 204
     */
    public function updatePassword(UpdatePasswordRequest $request): Response
    {

        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->validated('new_password')),
        ]);

        return response()->noContent();
    }
}
