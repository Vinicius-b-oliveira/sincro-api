<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\V1\AuthResource;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): AuthResource
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $accessToken = $user->createToken('auth_token')->plainTextToken;
        $refreshToken = RefreshToken::create([
            'user_id' => $user->id,
            'token' => Str::random(60),
            'expires_at' => now()->addDays(30),
        ]);

        return new AuthResource($user, $accessToken, $refreshToken->token);
    }

    public function login(LoginRequest $request): AuthResource
    {
        $validated = $request->validated();

        if (!Auth::attempt($validated)) {
            abort(Response::HTTP_UNAUTHORIZED, 'Credenciais inválidas');
        }

        $user = $request->user();

        $user->tokens()->delete();
        RefreshToken::where('user_id', $user->id)->delete();

        $accessToken = $user->createToken('auth_token')->plainTextToken;
        $refreshToken = RefreshToken::create([
            'user_id' => $user->id,
            'token' => Str::random(60),
            'expires_at' => now()->addDays(30),
        ]);

        return new AuthResource($user, $accessToken, $refreshToken->token);
    }
}
