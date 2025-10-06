<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    private ?string $accessToken;
    private ?string $refreshToken;

    public function __construct($resource, string $accessToken = null, string $refreshToken = null)
    {
        parent::__construct($resource);
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

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
            ],
        ];
    }
}
