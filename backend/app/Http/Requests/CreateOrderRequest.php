<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'shipping_address' => ['required', 'string', 'max:500'],
            'payment_method' => ['required', 'in:cash,edinar,konnect,d17'],
            'coupon_code' => ['sometimes', 'string', 'exists:coupons,code'],
            'notes' => ['sometimes', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.in' => 'Invalid payment method. Choose: cash, edinar, konnect, or d17',
            'coupon_code.exists' => 'Invalid coupon code',
        ];
    }
}
