<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
        'firstName' => $this->first_name,
        'lastName' => $this->last_name,
        'fullName' => $this->first_name.' '.$this->last_name,
        'email' => $this->email,
        'phone' => $this->phone,
        'image' => $this->state,
        'role' => $this->roles[0]->name,
        ];
    }
}
