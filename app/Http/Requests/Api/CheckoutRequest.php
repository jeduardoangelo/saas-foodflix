<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        foreach (['street', 'number', 'neighborhood', 'city', 'complement'] as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }
    }

    public function rules()
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where(function ($query) {
                    return $query->where('active', true)->whereNull('deleted_at');
                }),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'delivery_method' => ['required', Rule::in(['entrega', 'retirada'])],
            'street' => ['nullable', 'required_if:delivery_method,entrega', 'string', 'max:255'],
            'number' => ['nullable', 'required_if:delivery_method,entrega', 'string', 'max:20'],
            'neighborhood' => ['nullable', 'required_if:delivery_method,entrega', 'string', 'max:100'],
            'city' => ['nullable', 'required_if:delivery_method,entrega', 'string', 'max:100'],
            'complement' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(['cartao_credito', 'pix'])],
        ];
    }
}
