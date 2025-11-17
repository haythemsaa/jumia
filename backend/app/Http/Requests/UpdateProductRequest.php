<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        // Admin can update any product
        if ($this->user()->role === 'admin') {
            return true;
        }

        // Vendor can only update own products
        return $this->user()->role === 'vendor' &&
               $product->vendor_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'status' => ['sometimes', 'in:active,inactive,out_of_stock'],
        ];
    }
}
