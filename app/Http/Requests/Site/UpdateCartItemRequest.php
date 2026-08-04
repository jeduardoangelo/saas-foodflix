<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ];
    }

    public function messages()
    {
        return [
            'quantity.required' => 'Informe a quantidade.',
            'quantity.integer' => 'Informe uma quantidade válida.',
            'quantity.min' => 'A quantidade não pode ser negativa.',
            'quantity.max' => 'A quantidade máxima é 99.',
        ];
    }
}
