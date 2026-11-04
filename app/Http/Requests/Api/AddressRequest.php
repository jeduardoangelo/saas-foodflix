<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'neighborhood' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'complement' => ['nullable', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
