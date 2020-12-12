<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskPost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => 'required|max:255',
            'type' => 'required',
            'completion_date' => 'required',
            'completion_time' => 'required',
            'assignee_type' => 'required',
            'priority' => 'required|in:1,2,3,5,8,13'
        ];
    }
}
