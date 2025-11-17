<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedeemPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'points' => ['required', 'integer', 'min:100', 'max:10000'],
            'type' => ['required', 'in:discount_voucher,free_shipping,gift'],
        ];
    }

    public function messages(): array
    {
        return [
            'points.min' => 'Minimum redemption is 100 points',
            'points.max' => 'Maximum redemption is 10,000 points per transaction',
            'type.in' => 'Invalid redemption type',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();

            if ($this->points > $user->loyalty_points) {
                $validator->errors()->add(
                    'points',
                    'Insufficient points. You have ' . $user->loyalty_points . ' points.'
                );
            }
        });
    }
}
