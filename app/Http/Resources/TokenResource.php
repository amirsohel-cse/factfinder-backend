<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'auth_type' => 'Bearer',
            'token' => $this->accessToken,
            'expires_at' => $this->token->expires_at->format('Y-m-d H:i:s'),
        ];
    }
}
