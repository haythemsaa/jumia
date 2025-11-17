<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'vendor' || $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'status' => ['sometimes', 'in:active,inactive'],
            'images' => ['sometimes', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.min' => 'Price must be greater than 0',
            'stock.min' => 'Stock cannot be negative',
            'images.max' => 'Maximum 5 images allowed',
            'images.*.max' => 'Each image must be less than 2MB',
        ];
    }
}
