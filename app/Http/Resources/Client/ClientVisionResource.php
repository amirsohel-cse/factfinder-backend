<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ClientVisionResource extends JsonResource
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
            'id' => $this->id,
            'thumbnailImage' => asset('uploads').'/'. Auth::id() .'/'.$this->thumbnail_image,
            'originalImage' => asset('uploads').'/'. Auth::id() .'/'.$this->original_image,
        ];
    }
}
