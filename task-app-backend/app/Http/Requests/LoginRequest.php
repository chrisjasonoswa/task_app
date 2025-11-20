<?php

namespace App\Http\Requests;

class LoginRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'Email is required',
            'email.email'       => 'Email must be valid',
            'password.required' => 'Password is required',
            'password.min'      => 'Password must be at least 8 characters',
        ];
    }
}

