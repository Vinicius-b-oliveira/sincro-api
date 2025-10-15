<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\LogoutRequest;
use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\V1\AuthResource;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        $accessToken = $user->createToken('auth_token');
        $refreshToken = RefreshToken::create([
            'user_id' => $user->id,
            'token' => Str::random(60),
            'expires_at' => now()->addDays(30),
        ]);

        $accessTokenExpiresIn = config('sanctum.expiration') * 60;
        $refreshTokenExpiresIn = now()->diffInSeconds($refreshToken->expires_at);

        return new AuthResource(
            $user,
            accessToken: $accessToken->plainTextToken,
            refreshToken: $refreshToken->token,
            accessTokenExpiresIn: $accessTokenExpiresIn,
            refreshTokenExpiresIn: $refreshTokenExpiresIn
        );
    }

    public function login(LoginRequest $request): AuthResource
    {
        if (!Auth::attempt($request->validated())) {
            throw new AuthenticationException('Credenciais inválidas.');
        }

        $user = $request->user();

        $user->tokens()->delete();
        RefreshToken::where('user_id', $user->id)->delete();

        $accessToken = $user->createToken('auth_token');
        $refreshToken = RefreshToken::create([
            'user_id' => $user->id,
            'token' => Str::random(60),
            'expires_at' => now()->addDays(30),
        ]);

        $accessTokenExpiresIn = config('sanctum.expiration') * 60;
        $refreshTokenExpiresIn = now()->diffInSeconds($refreshToken->expires_at);

        return new AuthResource(
            $user,
            accessToken: $accessToken->plainTextToken,
            refreshToken: $refreshToken->token,
            accessTokenExpiresIn: $accessTokenExpiresIn,
            refreshTokenExpiresIn: $refreshTokenExpiresIn
        );
    }

    public function refreshToken(RefreshTokenRequest $request): AuthResource
    {
        $refreshTokenString = $request->validated('refresh_token');

        $refreshToken = RefreshToken::where('token', $refreshTokenString)->first();

        if (!$refreshToken || $refreshToken->expires_at < now()) {
            throw new AuthenticationException('Token de atualização inválido ou expirado.');
        }

        $user = $refreshToken->user;
        $refreshToken->delete();

        $accessToken = $user->createToken('auth_token');
        $newRefreshToken = RefreshToken::create([
            'user_id' => $user->id,
            'token' => Str::random(60),
            'expires_at' => now()->addDays(30),
        ]);

        $accessTokenExpiresIn = config('sanctum.expiration') * 60;
        $refreshTokenExpiresIn = now()->diffInSeconds($newRefreshToken->expires_at);

        return new AuthResource(
            $user,
            accessToken: $accessToken->plainTextToken,
            refreshToken: $newRefreshToken->token,
            accessTokenExpiresIn: $accessTokenExpiresIn,
            refreshTokenExpiresIn: $refreshTokenExpiresIn
        );
    }

    public function logout(LogoutRequest $request): \Illuminate\Http\Response
    {
        $request->user()->currentAccessToken()->delete();

        $refreshToken = RefreshToken::where('token', $request->validated('refresh_token'));

        if ($refreshToken) {
            $refreshToken->delete();
        }

        return response()->noContent();
    }
}
