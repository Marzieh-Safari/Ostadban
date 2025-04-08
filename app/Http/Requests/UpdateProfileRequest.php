<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'major' => 'sometimes|string|max:255'
        ];
    }
}