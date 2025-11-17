<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'regex:/^\+216[0-9]{8}$/'],
            'role' => ['sometimes', 'in:client,vendor'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Phone number must be in Tunisian format (+216XXXXXXXX)',
            'email.unique' => 'This email is already registered',
        ];
    }
}
