<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyReferralCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:8', 'exists:users,referral_code'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.exists' => 'Invalid referral code',
            'code.size' => 'Referral code must be 8 characters',
        ];
    }
}
