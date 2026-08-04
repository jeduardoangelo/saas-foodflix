<?php

namespace App\Http\Requests\Site;

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
            'delivery_method' => ['required', Rule::in(['entrega', 'retirada'])],
            'street' => ['nullable', 'required_if:delivery_method,entrega', 'string', 'max:255'],
            'number' => ['nullable', 'required_if:delivery_method,entrega', 'string', 'max:20'],
            'neighborhood' => ['nullable', 'required_if:delivery_method,entrega', 'string', 'max:100'],
            'city' => ['nullable', 'required_if:delivery_method,entrega', 'string', 'max:100'],
            'complement' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(['cartao_credito', 'pix'])],
        ];
    }

    public function messages()
    {
        return [
            'delivery_method.required' => 'Escolha entrega ou retirada.',
            'delivery_method.in' => 'A forma de recebimento é inválida.',
            'street.required_if' => 'Informe a rua ou avenida para entrega.',
            'number.required_if' => 'Informe o número para entrega.',
            'neighborhood.required_if' => 'Informe o bairro para entrega.',
            'city.required_if' => 'Informe a cidade para entrega.',
            'payment_method.required' => 'Escolha a forma de pagamento.',
            'payment_method.in' => 'A forma de pagamento é inválida.',
        ];
    }
}
