<?php

namespace App\Http\Resources\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * @var string|null
     */
    private ?string $accessToken;

    /**
     * @var string|null
     */
    private ?string $refreshToken;

    /**
     * @var int|null
     */
    private ?int $accessTokenExpiresIn;

    /**
     * @var int|null
     */
    private ?int $refreshTokenExpiresIn;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  string|null  $accessToken
     * @param  string|null  $refreshToken
     * @param  int|null  $accessTokenExpiresIn
     * @param  int|null  $refreshTokenExpiresIn
     * @return void
     */
    public function __construct(
        $resource,
        string $accessToken = null,
        string $refreshToken = null,
        int $accessTokenExpiresIn = null,
        int $refreshTokenExpiresIn = null
    ) {
        parent::__construct($resource);
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->accessTokenExpiresIn = $accessTokenExpiresIn;
        $this->refreshTokenExpiresIn = $refreshTokenExpiresIn;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
            ],
            'tokens' => [
                'access_token' => $this->accessToken,
                'refresh_token' => $this->refreshToken,
                'access_token_expires_in' => $this->accessTokenExpiresIn,
                'refresh_token_expires_in' => $this->refreshTokenExpiresIn,
            ],
        ];
    }
}
