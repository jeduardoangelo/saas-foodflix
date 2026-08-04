<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (! $this->has('quantity')) {
            $this->merge(['quantity' => 1]);
        }
    }

    public function rules()
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function messages()
    {
        return [
            'quantity.integer' => 'Informe uma quantidade válida.',
            'quantity.min' => 'A quantidade mínima é 1.',
            'quantity.max' => 'A quantidade máxima é 99.',
        ];
    }
}
