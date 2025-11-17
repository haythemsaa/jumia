<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['required', 'string', 'max:255'],
            'comment' => ['required', 'string', 'max:2000'],
            'photos' => ['sometimes', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png', 'max:5120'], // 5MB max
        ];
    }

    public function messages(): array
    {
        return [
            'rating.min' => 'Rating must be between 1 and 5',
            'rating.max' => 'Rating must be between 1 and 5',
            'photos.max' => 'Maximum 5 photos allowed',
            'photos.*.max' => 'Each photo must be less than 5MB',
        ];
    }
}
