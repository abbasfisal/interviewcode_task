<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::id() == $this->order->user_id;
    }

    public function rules(): array
    {
        return [
            'products'              => ['required', 'array'],
            'products.*.product_id' => ['exists:products,_id'],
            'products.*.count'      => ['integer'],
        ];
    }
}
