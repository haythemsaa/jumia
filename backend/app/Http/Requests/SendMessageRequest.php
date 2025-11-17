<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['sometimes', 'file', 'mimes:jpeg,jpg,png,pdf,doc,docx', 'max:5120'], // 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Message cannot be empty',
            'message.max' => 'Message is too long (maximum 5000 characters)',
            'attachment.max' => 'Attachment must be less than 5MB',
            'attachment.mimes' => 'Allowed file types: jpeg, jpg, png, pdf, doc, docx',
        ];
    }
}
