<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'merchant_reference' => ['required', 'string', 'max:64'],
            'amount' => ['required', 'integer', 'min:100'],
            'currency' => ['required', 'string', 'size:3'],
            'description' => ['required', 'string', 'max:255'],
            'customer' => ['required', 'array'],
            'customer.email' => ['required', 'email'],
            'customer.phone' => ['nullable', 'string', 'max:30'],
            'metadata' => ['sometimes', 'array'],
            'payment_method_token' => ['nullable', 'string', 'max:191'],
            'callback_url' => ['nullable', 'url'],
            'capture' => ['sometimes', 'boolean'],
        ];
    }
}
