<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class LoginRequest extends FormRequest
{
    public mixed $password;
    public mixed $email;

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')->where(function ($query) {
                    return $query->where('role', 'student');
                }),
            ],
            'password' => 'required|string',
        ];
    }
}
