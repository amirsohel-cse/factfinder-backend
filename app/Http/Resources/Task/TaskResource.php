<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quick_description' => $this->description,
            'type'=> $this->type,
            'completion_date' => $this->completion_date,
            'priority'=> $this->priority,
            'assignee_type' => $this->assignee_type,
            'created_at'=> $this->created_at,
            'updated_at' => $this->updated_at,
            'creator' =>[
                'full_name' => $this->creator->first_name.' '.$this->creator->last_name,
                'user_name' => $this->creator->username,
                'email' => $this->creator->email,
                ],
        ];
    }
}
