<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterStudentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'major' => 'required|string|max:255',
            'student_number' => 'required|string|unique:students'
        ];
    }
}