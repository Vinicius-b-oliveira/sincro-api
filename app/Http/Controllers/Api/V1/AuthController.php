<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\LogoutRequest;
use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\V1\Auth\AuthResource;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Response;
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

        $tokens = $this->generateTokens($user);

        return new AuthResource($user, ...$tokens);
    }

    /**
     * @throws AuthenticationException
     */
    public function login(LoginRequest $request): AuthResource
    {
        if (!Auth::attempt($request->validated())) {
            throw new AuthenticationException('Credenciais inválidas.');
        }

        $user = $request->user();

        $tokens = $this->generateTokens($user);

        return new AuthResource($user, ...$tokens);
    }

    /**
     * @throws AuthenticationException
     */
    public function refreshToken(RefreshTokenRequest $request): AuthResource
    {
        $refreshTokenString = $request->validated('refresh_token');
        $refreshToken = RefreshToken::where('token', $refreshTokenString)->first();

        if (!$refreshToken || $refreshToken->expires_at < now()) {
            throw new AuthenticationException('Token de atualização inválido ou expirado.');
        }

        $user = $refreshToken->user;
        $refreshToken->delete();

        $tokens = $this->generateTokens($user);

        return new AuthResource($user, ...$tokens);
    }

    public function logout(LogoutRequest $request): Response
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->currentAccessToken()->delete();

        RefreshToken::where('token', $validated['refresh_token'])?->delete();

        return response()->noContent();
    }

    private function generateTokens(User $user): array
    {
        $accessToken = $user->createToken('auth_token');
        $refreshToken = RefreshToken::create([
            'user_id' => $user->id,
            'token' => Str::random(60),
            'expires_at' => now()->addDays(30),
        ]);

        return [
            'accessToken' => $accessToken->plainTextToken,
            'refreshToken' => $refreshToken->token,
            'accessTokenExpiresIn' => config('sanctum.expiration') * 60,
            'refreshTokenExpiresIn' => now()->diffInSeconds($refreshToken->expires_at),
        ];
    }
}
